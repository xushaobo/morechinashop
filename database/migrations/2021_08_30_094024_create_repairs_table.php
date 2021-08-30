<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRepairsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('repairs', function (Blueprint $table) {
            $table->increments('id');
            $table->date('repair_date')->nullable();
            $table->string('customer_name')->nullable();
            $table->string('phone')->nullable();
            $table->string('seller')->nullable();
            $table->string('brand')->nullable();
            $table->string('type')->nullable();
            $table->string('serial_num')->nullable();
            $table->string('bad_description')->nullable();
            $table->boolean('ifunderwarry')->nullable();
            $table->text('howtodo')->nullable();
	    $table->text('des_add')->nullable();
            $table->date('finfish_date')->nullable();
            $table->string('mail_info')->nullable();
            $table->boolean('ifreturntoBJ')->nullable();
    	    $table->text('howtodo_BJ')->nullable();
            $table->date('returnBJ_date')->nullable();
	    $table->text('remark')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('repairs');
    }
}
