<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;

class BackfillSerialNumOrderIdFromOrderItem extends Migration
{
    public function up()
    {
        if (
            !Schema::hasColumn('serial_nums', 'order_id') ||
            !Schema::hasColumn('serial_nums', 'order_item_id')
        ) {
            return;
        }

        DB::statement('
            UPDATE serial_nums sn
            INNER JOIN order_items oi ON oi.id = sn.order_item_id
            SET sn.order_id = oi.order_id
            WHERE sn.order_id IS NULL
              AND sn.order_item_id IS NOT NULL
        ');
    }

    public function down()
    {
        //
    }
}
