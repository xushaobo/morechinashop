<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;

class Assign9679103740Serials extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('serial_nums', 'order_id') || !Schema::hasColumn('serial_nums', 'order_item_id')) {
            return;
        }

        $singleItemId = DB::table('order_items')
            ->where('order_id', 9679)
            ->where('product_sku_id', 1097)
            ->value('id');

        $bundleItemId = DB::table('order_items')
            ->where('order_id', 9679)
            ->where('product_sku_id', 1391)
            ->value('id');

        if (!$singleItemId || !$bundleItemId) {
            return;
        }

        DB::table('serial_nums')
            ->where('serial_num', 'H260802005')
            ->update([
                'order_id' => 9679,
                'order_item_id' => $singleItemId,
            ]);

        DB::table('serial_nums')
            ->where('serial_num', 'H260804003')
            ->update([
                'order_id' => 9679,
                'order_item_id' => $bundleItemId,
            ]);
    }

    public function down()
    {
        //
    }
}
