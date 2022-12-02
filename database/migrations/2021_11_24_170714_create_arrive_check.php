<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateArriveCheck extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('arrive_check', function (Blueprint $table) {
            $table->increments('id');
            $table->date('arrive_date')->nullable();
	    $table->string('pi_num')->nullable();
	    $table->string('sku_num')->nullable();
	    $table->string('serial_num1')->nullable();
	    $table->string('serial_num2')->nullable();
            $table->boolean('if_sold');
            $table->text('memo')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('arrive_check');
    }
}
