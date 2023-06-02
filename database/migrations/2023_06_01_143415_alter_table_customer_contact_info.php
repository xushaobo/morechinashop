<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableCustomerContactInfo extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('customers', function (Blueprint $table) {
		$table->string('contact_info')->nullable()->change(); //地区
		$table->string('contact_address')->nullable()->change(); //地区
		$table->string('contact_email')->nullable()->change(); //地区
		$table->string('first_call')->nullable()->change(); //地区
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    }
}
