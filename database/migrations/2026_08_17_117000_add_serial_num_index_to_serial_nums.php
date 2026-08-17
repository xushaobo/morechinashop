<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSerialNumIndexToSerialNums extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('serial_nums', 'serial_num') || $this->hasIndex('idx_serial_nums_serial_num')) {
            return;
        }

        Schema::table('serial_nums', function (Blueprint $table) {
            $table->index('serial_num', 'idx_serial_nums_serial_num');
        });
    }

    public function down()
    {
        if (!$this->hasIndex('idx_serial_nums_serial_num')) {
            return;
        }

        Schema::table('serial_nums', function (Blueprint $table) {
            $table->dropIndex('idx_serial_nums_serial_num');
        });
    }

    protected function hasIndex($indexName)
    {
        return DB::table('information_schema.statistics')
            ->where('table_schema', DB::connection()->getDatabaseName())
            ->where('table_name', 'serial_nums')
            ->where('index_name', $indexName)
            ->exists();
    }
}
