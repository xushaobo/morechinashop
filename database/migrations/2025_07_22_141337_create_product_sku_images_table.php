<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductSkuImagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_sku_images', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('product_sku_id');
	    $table->foreign('product_sku_id')->references('id')->on('product_skus')->onDelete('cascade');
            $table->string('path'); //图片路径
	    $table->boolean('is_main')->default(true);
	    $table->unsignedInteger('order')->default(0); //排序字段
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('product_sku_images');
    }
}
