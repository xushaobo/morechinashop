<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableCustomersContactInfo extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('customers', function (Blueprint $table) {
		$table->string('contact_QQ')->nullable()->change(); //地区
		$table->string('memo')->nullable()->change(); //地区
		$table->string('region')->nullable()->change(); //地区
		$table->string('city')->nullable()->change(); //地区
		$table->string('customer_type')->nullable()->change(); //地区
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
