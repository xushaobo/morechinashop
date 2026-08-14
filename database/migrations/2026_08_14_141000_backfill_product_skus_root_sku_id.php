<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;

class BackfillProductSkusRootSkuId extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('product_skus', 'root_sku_id')) {
            return;
        }

        DB::statement('
            UPDATE product_skus child
            LEFT JOIN product_skus master ON master.id = child.master_sku_id
            SET child.root_sku_id = COALESCE(master.root_sku_id, child.master_sku_id, child.id)
            WHERE child.root_sku_id IS NULL
        ');
    }

    public function down()
    {
        //
    }
}
