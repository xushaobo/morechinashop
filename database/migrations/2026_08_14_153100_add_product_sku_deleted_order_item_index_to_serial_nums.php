<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddProductSkuDeletedOrderItemIndexToSerialNums extends Migration
{
    public function up()
    {
        Schema::table('serial_nums', function (Blueprint $table) {
            $table->index(['productSku_id', 'deleted_at', 'order_item_id'], 'idx_serial_nums_sku_deleted_order_item');
        });
    }

    public function down()
    {
        Schema::table('serial_nums', function (Blueprint $table) {
            $table->dropIndex('idx_serial_nums_sku_deleted_order_item');
        });
    }
}
