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
        $table->string('product_id');
	    $table->string('serialNum_id');
        $table->string('orderNum_id')->default('not_save');
        $table->timestamp('created_at')->nullable();
        $table->timestamp('updated_at')->nullable();
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
