<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCostToSerialNumsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('serial_nums', function (Blueprint $table) {
		$table->string('cost',10,2)->after('ship_num')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('serial_nums', function (Blueprint $table) {
		$table->dropColumn('cost');
        });
    }
}
