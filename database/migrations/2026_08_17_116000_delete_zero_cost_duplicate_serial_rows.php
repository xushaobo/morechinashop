<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;

class DeleteZeroCostDuplicateSerialRows extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('serial_nums', 'cost')) {
            return;
        }

        DB::table('serial_nums')
            ->whereIn('id', [4595, 4604, 4611, 4802, 4610])
            ->whereRaw('CAST(cost AS DECIMAL(10,2)) = 0')
            ->delete();
    }

    public function down()
    {
        //
    }
}
