<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;

class Assign928526070121Serial extends Migration
{
    public function up()
    {
        if (
            !Schema::hasColumn('serial_nums', 'order_id') ||
            !Schema::hasColumn('serial_nums', 'order_item_id')
        ) {
            return;
        }

        $itemId = DB::table('order_items')
            ->where('order_id', 9285)
            ->where('product_sku_id', 1152)
            ->where('amount', 3)
            ->value('id');

        if (!$itemId) {
            return;
        }

        DB::table('serial_nums')
            ->where('serial_num', '26070121')
            ->update([
                'order_id' => 9285,
                'order_item_id' => $itemId,
            ]);
    }

    public function down()
    {
        //
    }
}
