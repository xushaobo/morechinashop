<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;

class Balance9702970438223823Serials extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('serial_nums', 'order_id') || !Schema::hasColumn('serial_nums', 'order_item_id')) {
            return;
        }

        $windowStart = '2026-07-30 12:33:57';
        $windowEnd = '2026-07-30 14:45:15';

        $this->assignRootSerials(1843, $windowStart, $windowEnd, 17205, 17209, 5, 10);
        $this->assignRootSerials(2008, $windowStart, $windowEnd, 17206, 17210, 5, 20);
    }

    public function down()
    {
        //
    }

    protected function assignRootSerials($rootSkuId, $windowStart, $windowEnd, $order9702ItemId, $order9704ItemId, $order9702Count, $order9704Count)
    {
        $serialIds = DB::table('serial_nums as sn')
            ->join('product_skus as ps', 'sn.productSku_id', '=', 'ps.id')
            ->whereNull('sn.order_item_id')
            ->where('ps.root_sku_id', $rootSkuId)
            ->whereNotNull('sn.deleted_at')
            ->whereBetween('sn.deleted_at', [$windowStart, $windowEnd])
            ->orderBy('sn.deleted_at')
            ->orderBy('sn.id')
            ->pluck('sn.id')
            ->all();

        $expected = $order9702Count + $order9704Count;
        if (count($serialIds) !== $expected) {
            return;
        }

        DB::table('serial_nums')
            ->whereIn('id', array_slice($serialIds, 0, $order9702Count))
            ->update([
                'order_id' => 9702,
                'order_item_id' => $order9702ItemId,
            ]);

        DB::table('serial_nums')
            ->whereIn('id', array_slice($serialIds, $order9702Count))
            ->update([
                'order_id' => 9704,
                'order_item_id' => $order9704ItemId,
            ]);
    }
}
