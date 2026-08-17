<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;

class BalanceSerialDetailMismatchesBatch extends Migration
{
    public function up()
    {
        if (
            !Schema::hasColumn('serial_nums', 'order_id') ||
            !Schema::hasColumn('serial_nums', 'order_item_id')
        ) {
            return;
        }

        $clearLinks = [
            1624,
            3154,
            3849,
            4623,
        ];

        $relinks = [
            ['id' => 8267, 'order_id' => 8821, 'order_item_id' => 15227, 'deleted_at' => '2025-11-27 15:16:15'],
            ['id' => 31046, 'order_id' => 9046, 'order_item_id' => 15723, 'deleted_at' => '2026-02-04 17:16:48'],
            ['id' => 32208, 'order_id' => 9072, 'order_item_id' => 15772, 'deleted_at' => '2026-02-27 17:24:08'],
            ['id' => 5781, 'order_id' => 9092, 'order_item_id' => 15806, 'deleted_at' => '2026-03-04 14:31:47'],
            ['id' => 30434, 'order_id' => 9092, 'order_item_id' => 15807, 'deleted_at' => '2026-03-04 14:31:47'],
            ['id' => 37364, 'order_id' => 8976, 'order_item_id' => 15581, 'deleted_at' => '2026-01-16 14:15:26'],
            ['id' => 35770, 'order_id' => 9059, 'order_item_id' => 15746, 'deleted_at' => '2026-02-14 10:43:48'],
        ];

        $assign9160 = [
            ['id' => 5807, 'order_item_id' => 15952],
            ['id' => 5808, 'order_item_id' => 15952],
            ['id' => 32165, 'order_item_id' => 15952],
            ['id' => 32166, 'order_item_id' => 15952],
            ['id' => 5810, 'order_item_id' => 15952],
            ['id' => 5811, 'order_item_id' => 15961],
            ['id' => 32164, 'order_item_id' => 15961],
            ['id' => 5846, 'order_item_id' => 15953],
            ['id' => 5847, 'order_item_id' => 15953],
            ['id' => 32170, 'order_item_id' => 15953],
            ['id' => 32171, 'order_item_id' => 15953],
            ['id' => 32172, 'order_item_id' => 15953],
            ['id' => 31374, 'order_item_id' => 15962],
            ['id' => 31375, 'order_item_id' => 15962],
            ['id' => 5915, 'order_item_id' => 15954],
            ['id' => 5917, 'order_item_id' => 15954],
            ['id' => 32183, 'order_item_id' => 15954],
            ['id' => 32184, 'order_item_id' => 15954],
            ['id' => 32350, 'order_item_id' => 15954],
            ['id' => 31727, 'order_item_id' => 15963],
            ['id' => 32182, 'order_item_id' => 15963],
        ];

        DB::transaction(function () use ($clearLinks, $relinks, $assign9160) {
            foreach ($clearLinks as $id) {
                DB::table('serial_nums')
                    ->where('id', $id)
                    ->update([
                        'order_id' => null,
                        'order_item_id' => null,
                    ]);
            }

            foreach ($relinks as $relink) {
                DB::table('serial_nums')
                    ->where('id', $relink['id'])
                    ->update([
                        'order_id' => $relink['order_id'],
                        'order_item_id' => $relink['order_item_id'],
                        'deleted_at' => $relink['deleted_at'],
                    ]);
            }

            foreach ($assign9160 as $assignment) {
                DB::table('serial_nums')
                    ->where('id', $assignment['id'])
                    ->update([
                        'order_id' => 9160,
                        'order_item_id' => $assignment['order_item_id'],
                        'deleted_at' => '2026-03-19 10:39:43',
                    ]);
            }
        });
    }

    public function down()
    {
        //
    }
}
