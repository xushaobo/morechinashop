<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddRootSkuIdToProductSkusTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('product_skus', function (Blueprint $table) {
        	// 添加 root_sku_id 字段，类型与 id 一致（unsigned int）
            $table->unsignedInteger('root_sku_id')->nullable()->after('master_sku_id');
            // 创建索引
            $table->index('root_sku_id', 'idx_product_skus_root_sku_id');
        });

	// 初始化现有数据：root_sku_id = COALESCE(master_sku_id, id)
        DB::statement('UPDATE product_skus SET root_sku_id = COALESCE(master_sku_id, id)');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('product_skus', function (Blueprint $table) {
         	 $table->dropIndex('idx_product_skus_root_sku_id');
      	         $table->dropColumn('root_sku_id');
        });
    }
}
