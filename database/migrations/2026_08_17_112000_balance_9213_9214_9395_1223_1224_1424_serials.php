<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;

class Balance921392149395122312241424Serials extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('serial_nums', 'order_id') || !Schema::hasColumn('serial_nums', 'order_item_id')) {
            return;
        }

        $targets = [
            [
                'order_id' => 9213,
                'paid_at' => '2026-03-31 16:04:07',
                'items' => [
                    ['item_id' => 16087, 'serial_id' => 30884, 'serial_num' => '20251117'],
                    ['item_id' => 16088, 'serial_id' => 30328, 'serial_num' => '250929'],
                ],
            ],
            [
                'order_id' => 9214,
                'paid_at' => '2026-03-31 17:09:23',
                'items' => [
                    ['item_id' => 16090, 'serial_id' => 30896, 'serial_num' => '20251117'],
                    ['item_id' => 16091, 'serial_id' => 30918, 'serial_num' => '20251117'],
                ],
            ],
            [
                'order_id' => 9395,
                'paid_at' => '2026-05-15 17:13:57',
                'items' => [
                    ['item_id' => 16533, 'serial_id' => 36143, 'serial_num' => '20260622'],
                ],
            ],
        ];

        DB::transaction(function () use ($targets) {
            foreach ($targets as $target) {
                foreach ($target['items'] as $item) {
                    DB::table('serial_nums')
                        ->where('id', $item['serial_id'])
                        ->update([
                            'order_id' => $target['order_id'],
                            'order_item_id' => $item['item_id'],
                            'deleted_at' => $target['paid_at'],
                        ]);
                }

                DB::table('orders')
                    ->where('id', $target['order_id'])
                    ->update([
                        'serial_data' => json_encode([
                            'serial_no' => implode(', ', array_column($target['items'], 'serial_num')),
                        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                    ]);
            }
        });
    }

    public function down()
    {
        //
    }
}
