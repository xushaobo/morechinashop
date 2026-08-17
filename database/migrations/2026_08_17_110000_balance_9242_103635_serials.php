<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;

class Balance9242103635Serials extends Migration
{
    public function up()
    {
        if (
            !Schema::hasColumn('serial_nums', 'order_id') ||
            !Schema::hasColumn('serial_nums', 'order_item_id')
        ) {
            return;
        }

        $paidAt = DB::table('orders')
            ->where('id', 9242)
            ->value('paid_at');

        if (!$paidAt) {
            return;
        }

        $windowStart = date('Y-m-d H:i:s', strtotime($paidAt) - 3600);
        $windowEnd = date('Y-m-d H:i:s', strtotime($paidAt) + 3600);

        $mainItemId = DB::table('order_items')
            ->where('order_id', 9242)
            ->where('product_sku_id', 1127)
            ->where('amount', 25)
            ->value('id');

        $bundleItemId = DB::table('order_items')
            ->where('order_id', 9242)
            ->where('product_sku_id', 1364)
            ->where('amount', 5)
            ->value('id');

        if (!$mainItemId || !$bundleItemId) {
            return;
        }

        $serials = DB::table('serial_nums as sn')
            ->join('product_skus as ps', 'sn.productSku_id', '=', 'ps.id')
            ->whereNull('sn.order_item_id')
            ->whereNotNull('sn.deleted_at')
            ->where('ps.root_sku_id', 1127)
            ->whereBetween('sn.deleted_at', [$windowStart, $windowEnd])
            ->orderBy('sn.deleted_at')
            ->orderBy('sn.id')
            ->select('sn.id', 'sn.serial_num')
            ->get();

        if ($serials->count() !== 30) {
            return;
        }

        $mainSerialIds = $serials->slice(0, 25)->pluck('id')->all();
        $bundleSerialIds = $serials->slice(25)->pluck('id')->all();

        if (count($mainSerialIds) !== 25 || count($bundleSerialIds) !== 5) {
            return;
        }

        DB::transaction(function () use ($mainSerialIds, $bundleSerialIds, $mainItemId, $bundleItemId, $serials) {
            DB::table('serial_nums')
                ->whereIn('id', $mainSerialIds)
                ->update([
                    'order_id' => 9242,
                    'order_item_id' => $mainItemId,
                ]);

            DB::table('serial_nums')
                ->whereIn('id', $bundleSerialIds)
                ->update([
                    'order_id' => 9242,
                    'order_item_id' => $bundleItemId,
                ]);

            DB::table('orders')
                ->where('id', 9242)
                ->update([
                    'serial_data' => json_encode([
                        'serial_no' => $serials->pluck('serial_num')->implode(', '),
                    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                ]);
        });
    }

    public function down()
    {
        //
    }
}
