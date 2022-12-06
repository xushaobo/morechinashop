<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSerialNumsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('serial_nums', function (Blueprint $table) {
            $table->increments('id');
	    $table->unsignedInteger('productSku_id');
            $table->foreign('productSku_id')->references('id')->on('product_skus')->onDelete('cascade');
            $table->string('serial_num');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('serial_nums');
    }
}
