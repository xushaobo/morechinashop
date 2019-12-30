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
            $table->unsignedInteger('product_id');
	    $table->foreign('product_id')->references('product_id')->on('product_skus')->onDelete('cascade');
	    $table->string('serialNum_id');
	    $table->string('orderNum_id')->default('not_save');
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
        Schema::dropIfExists('serial_nums');
    }
}
