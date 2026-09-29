<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;

class Reassign37456ToOrder9848 extends Migration
{
    public function up()
    {
        if (
            !Schema::hasColumn('serial_nums', 'order_id') ||
            !Schema::hasColumn('serial_nums', 'order_item_id')
        ) {
            return;
        }

        $serial = DB::table('serial_nums')
            ->where('id', 37456)
            ->where('serial_num', '26271083')
            ->where('order_id', 9852)
            ->where('order_item_id', 17527)
            ->first();

        if (!$serial) {
            return;
        }

        $targetItemExists = DB::table('order_items')
            ->where('id', 17517)
            ->where('order_id', 9848)
            ->where('product_sku_id', 1340)
            ->exists();

        if (!$targetItemExists) {
            return;
        }

        DB::table('serial_nums')
            ->where('id', 37456)
            ->update([
                'order_id' => 9848,
                'order_item_id' => 17517,
            ]);
    }

    public function down()
    {
        if (
            !Schema::hasColumn('serial_nums', 'order_id') ||
            !Schema::hasColumn('serial_nums', 'order_item_id')
        ) {
            return;
        }

        DB::table('serial_nums')
            ->where('id', 37456)
            ->where('serial_num', '26271083')
            ->where('order_id', 9848)
            ->where('order_item_id', 17517)
            ->update([
                'order_id' => 9852,
                'order_item_id' => 17527,
            ]);
    }
}
