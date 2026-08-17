<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;

class Balance9463946492849285Serials extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('serial_nums', 'order_id') || !Schema::hasColumn('serial_nums', 'order_item_id')) {
            return;
        }

        $this->assignRootSerials(1152, '2026-04-16 16:24:06', [
            ['order_id' => 9284, 'product_sku_id' => 1152, 'amount' => 3],
            ['order_id' => 9285, 'product_sku_id' => 1152, 'amount' => 3],
        ]);

        $this->assignRootSerials(1880, '2026-06-02 13:34:02', [
            ['order_id' => 9463, 'product_sku_id' => 1880, 'amount' => 5],
            ['order_id' => 9464, 'product_sku_id' => 1880, 'amount' => 3],
        ]);
    }

    public function down()
    {
        //
    }

    protected function assignRootSerials($rootSkuId, $deletedAt, array $targets)
    {
        $serialIds = DB::table('serial_nums as sn')
            ->join('product_skus as ps', 'sn.productSku_id', '=', 'ps.id')
            ->whereNull('sn.order_item_id')
            ->whereNotNull('sn.deleted_at')
            ->where('ps.root_sku_id', $rootSkuId)
            ->where('sn.deleted_at', $deletedAt)
            ->orderBy('sn.id')
            ->pluck('sn.id')
            ->all();

        $expected = array_sum(array_column($targets, 'amount'));
        if (count($serialIds) !== $expected) {
            return;
        }

        $assignments = [];
        $offset = 0;

        foreach ($targets as $target) {
            $itemId = DB::table('order_items')
                ->where('order_id', $target['order_id'])
                ->where('product_sku_id', $target['product_sku_id'])
                ->where('amount', $target['amount'])
                ->value('id');

            if (!$itemId) {
                return;
            }

            $ids = array_slice($serialIds, $offset, $target['amount']);
            if (count($ids) !== $target['amount']) {
                return;
            }

            $assignments[] = [
                'ids' => $ids,
                'order_id' => $target['order_id'],
                'order_item_id' => $itemId,
            ];
            $offset += $target['amount'];
        }

        if ($offset !== count($serialIds)) {
            return;
        }

        DB::transaction(function () use ($assignments) {
            foreach ($assignments as $assignment) {
                DB::table('serial_nums')
                    ->whereIn('id', $assignment['ids'])
                    ->update([
                        'order_id' => $assignment['order_id'],
                        'order_item_id' => $assignment['order_item_id'],
                    ]);
            }
        });
    }
}
