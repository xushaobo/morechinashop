<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableCustomers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('customers', function (Blueprint $table) {
		$table->string('contact_require')->nullable(); //潜在需求
		$table->string('require_type')->nullable(); //产品类别
		$table->string('require_brand')->nullable(); //产品品牌
		$table->string('follow_up_stage')->nullable(); //跟进阶段
		$table->string('recent_contact')->nullable(); //最近联系日期和结果
		$table->string('next_action')->nullable(); //下一次联系，行动内容
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
