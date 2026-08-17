<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;

class ClearHistoricalRootSerialNumLinks extends Migration
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
            INNER JOIN order_items oi ON sn.order_item_id = oi.id
            INNER JOIN orders o ON oi.order_id = o.id
            SET sn.order_id = NULL,
                sn.order_item_id = NULL
            WHERE o.serial_data IS NULL
              AND sn.productSku_id <> oi.product_sku_id
        ');
    }

    public function down()
    {
        //
    }
}
