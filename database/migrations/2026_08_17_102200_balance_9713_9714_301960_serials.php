<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;

class Balance97139714301960Serials extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('serial_nums', 'order_id') || !Schema::hasColumn('serial_nums', 'order_item_id')) {
            return;
        }

        $serialIds = DB::table('serial_nums as sn')
            ->join('product_skus as ps', 'sn.productSku_id', '=', 'ps.id')
            ->whereNull('sn.order_item_id')
            ->where('ps.root_sku_id', 1141)
            ->where('sn.deleted_at', '2026-07-31 11:52:25')
            ->orderBy('sn.id')
            ->pluck('sn.id')
            ->all();

        if (count($serialIds) !== 12) {
            return;
        }

        $order9713ItemId = DB::table('order_items')
            ->where('order_id', 9713)
            ->where('product_sku_id', 1141)
            ->value('id');

        $order9714ItemId = DB::table('order_items')
            ->where('order_id', 9714)
            ->where('product_sku_id', 1141)
            ->value('id');

        if (!$order9713ItemId || !$order9714ItemId) {
            return;
        }

        DB::table('serial_nums')
            ->whereIn('id', array_slice($serialIds, 0, 10))
            ->update([
                'order_id' => 9713,
                'order_item_id' => $order9713ItemId,
            ]);

        DB::table('serial_nums')
            ->whereIn('id', array_slice($serialIds, 10))
            ->update([
                'order_id' => 9714,
                'order_item_id' => $order9714ItemId,
            ]);
    }

    public function down()
    {
        //
    }
}
