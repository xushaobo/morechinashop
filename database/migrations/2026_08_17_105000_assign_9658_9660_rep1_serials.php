<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;

class Assign96589660Rep1Serials extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('serial_nums', 'order_id') || !Schema::hasColumn('serial_nums', 'order_item_id')) {
            return;
        }

        $item9658 = DB::table('order_items')
            ->where('order_id', 9658)
            ->where('product_sku_id', 2302)
            ->value('id');

        $item9660 = DB::table('order_items')
            ->where('order_id', 9660)
            ->where('product_sku_id', 2302)
            ->value('id');

        if (!$item9658 || !$item9660) {
            return;
        }

        $serialIds = DB::table('serial_nums as sn')
            ->join('product_skus as ps', 'sn.productSku_id', '=', 'ps.id')
            ->whereNull('sn.order_item_id')
            ->where('ps.root_sku_id', 2302)
            ->where('sn.deleted_at', '2026-07-16 17:58:10')
            ->orderBy('sn.id')
            ->pluck('sn.id')
            ->all();

        if (count($serialIds) !== 2) {
            return;
        }

        DB::table('serial_nums')
            ->where('id', $serialIds[0])
            ->update([
                'order_id' => 9658,
                'order_item_id' => $item9658,
            ]);

        DB::table('serial_nums')
            ->where('id', $serialIds[1])
            ->update([
                'order_id' => 9660,
                'order_item_id' => $item9660,
            ]);
    }

    public function down()
    {
        //
    }
}
