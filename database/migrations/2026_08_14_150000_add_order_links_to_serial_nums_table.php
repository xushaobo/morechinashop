<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddOrderLinksToSerialNumsTable extends Migration
{
    public function up()
    {
        Schema::table('serial_nums', function (Blueprint $table) {
            if (!Schema::hasColumn('serial_nums', 'order_id')) {
                $table->unsignedInteger('order_id')->nullable()->after('productSku_id');
                $table->index('order_id', 'idx_serial_nums_order_id');
                $table->foreign('order_id')->references('id')->on('orders')->onDelete('set null');
            }

            if (!Schema::hasColumn('serial_nums', 'order_item_id')) {
                $table->unsignedInteger('order_item_id')->nullable()->after('order_id');
                $table->index(['order_item_id', 'deleted_at'], 'idx_serial_nums_order_item_deleted_at');
                $table->foreign('order_item_id')->references('id')->on('order_items')->onDelete('set null');
            }
        });
    }

    public function down()
    {
        Schema::table('serial_nums', function (Blueprint $table) {
            if (Schema::hasColumn('serial_nums', 'order_item_id')) {
                $table->dropForeign(['order_item_id']);
                $table->dropIndex('idx_serial_nums_order_item_deleted_at');
                $table->dropColumn('order_item_id');
            }

            if (Schema::hasColumn('serial_nums', 'order_id')) {
                $table->dropForeign(['order_id']);
                $table->dropIndex('idx_serial_nums_order_id');
                $table->dropColumn('order_id');
            }
        });
    }
}
