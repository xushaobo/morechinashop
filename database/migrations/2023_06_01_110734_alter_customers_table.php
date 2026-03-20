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
		$table->string('region')->default(''); //地区
		$table->string('city')->default('');  //城市
		$table->string('customer_type')->default('');  //客户性质
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
		$table->dropColumn('region'); //地区
		$table->dropColumn('city'); //地区
		$table->dropColumn('customer_type'); //地区
        });
    }
}
