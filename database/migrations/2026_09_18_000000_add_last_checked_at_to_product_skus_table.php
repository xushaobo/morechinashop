<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLastCheckedAtToProductSkusTable extends Migration
{
    public function up()
    {
        Schema::table('product_skus', function (Blueprint $table) {
            $table->date('last_checked_at')->nullable()->default('2025-01-01')->after('stock');
        });
    }

    public function down()
    {
        Schema::table('product_skus', function (Blueprint $table) {
            $table->dropColumn('last_checked_at');
        });
    }
}
