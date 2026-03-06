<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMasterSkuIdToSkusTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('product_skus', function (Blueprint $table) {
            // 添加 master_sku_id 字段,为空说明是主SKU
	    $table->unsignedInteger('master_sku_id')->nullable()->after('id');

	    $table->foreign('master_sku_id')
		  ->references('id')
		  ->on('product_skus')
		  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('product_skus', function (Blueprint $table) {
		$table->dropForeign(['master_sku_id']);
		$table->dropColumn('master_sku_id');
        });
    }
}
