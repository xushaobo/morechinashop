<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;

class BackfillUniqueRootWindowSerialNumOrderLinks extends Migration
{
    public function up()
    {
        if (
            !Schema::hasColumn('serial_nums', 'order_id') ||
            !Schema::hasColumn('serial_nums', 'order_item_id') ||
            !Schema::hasColumn('product_skus', 'root_sku_id')
        ) {
            return;
        }

        DB::table('serial_nums as sn')
            ->join('product_skus as ps', 'sn.productSku_id', '=', 'ps.id')
            ->whereNull('sn.order_item_id')
            ->whereNotNull('sn.deleted_at')
            ->whereNotNull('ps.root_sku_id')
            ->select(
                'ps.root_sku_id',
                'sn.deleted_at',
                DB::raw('COUNT(*) as serial_count')
            )
            ->groupBy('ps.root_sku_id', 'sn.deleted_at')
            ->orderBy('sn.deleted_at')
            ->chunk(100, function ($groups) {
                foreach ($groups as $group) {
                    $this->assignGroup($group);
                }
            });
    }

    public function down()
    {
        //
    }

    protected function assignGroup($group)
    {
        $start = date('Y-m-d H:i:s', strtotime($group->deleted_at) - 3600);
        $end = date('Y-m-d H:i:s', strtotime($group->deleted_at) + 3600);

        $candidates = DB::table('order_items as oi')
            ->join('orders as o', 'oi.order_id', '=', 'o.id')
            ->join('product_skus as ps', 'oi.product_sku_id', '=', 'ps.id')
            ->leftJoin('serial_nums as linked', function ($join) {
                $join->on('linked.order_item_id', '=', 'oi.id')
                    ->whereNotNull('linked.deleted_at');
            })
            ->select(
                'oi.id as order_item_id',
                'oi.order_id',
                'oi.amount',
                DB::raw('COUNT(linked.id) as linked_count')
            )
            ->where('ps.root_sku_id', $group->root_sku_id)
            ->whereBetween('o.paid_at', [$start, $end])
            ->groupBy('oi.id', 'oi.order_id', 'oi.amount')
            ->havingRaw('CAST(oi.amount AS SIGNED) > CAST(COUNT(linked.id) AS SIGNED)')
            ->orderBy('oi.id')
            ->get();

        if ($candidates->count() !== 1) {
            return;
        }

        $candidate = $candidates->first();
        $remaining = (int) $candidate->amount - (int) $candidate->linked_count;
        if ($remaining !== (int) $group->serial_count) {
            return;
        }

        $serialIds = DB::table('serial_nums as sn')
            ->join('product_skus as ps', 'sn.productSku_id', '=', 'ps.id')
            ->whereNull('sn.order_item_id')
            ->where('ps.root_sku_id', $group->root_sku_id)
            ->where('sn.deleted_at', $group->deleted_at)
            ->orderBy('sn.id')
            ->pluck('sn.id')
            ->all();

        if (count($serialIds) !== (int) $group->serial_count) {
            return;
        }

        DB::transaction(function () use ($serialIds, $candidate) {
            DB::table('serial_nums')
                ->whereIn('id', $serialIds)
                ->update([
                    'order_id' => $candidate->order_id,
                    'order_item_id' => $candidate->order_item_id,
                ]);
        });
    }
}
