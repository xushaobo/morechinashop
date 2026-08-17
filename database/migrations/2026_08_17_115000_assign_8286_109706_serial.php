<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;

class Assign8286109706Serial extends Migration
{
    public function up()
    {
        if (
            !Schema::hasColumn('serial_nums', 'order_id') ||
            !Schema::hasColumn('serial_nums', 'order_item_id')
        ) {
            return;
        }

        DB::table('serial_nums')
            ->where('id', 7789)
            ->update([
                'order_id' => 8286,
                'order_item_id' => 14093,
                'deleted_at' => '2025-07-14 13:42:26',
            ]);
    }

    public function down()
    {
        //
    }
}
