<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterCustomersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('customers', function (Blueprint $table) {
		$table->string('contact_info')->default(''); //地区
		$table->string('contact_email')->default(''); //地区
		$table->string('contact_QQ')->default(''); //地区
		$table->string('contact_address')->default(''); //地区
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('customers', function (Blueprint $table) {
		$table->drop_column('contact_info'); //地区
		$table->drop_column('contact_email'); //地区
		$table->drop_column('contact_QQ'); //地区
		$table->drop_column('contact_address'); //地区
        });
    }
}
