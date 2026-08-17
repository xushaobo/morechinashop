<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;

class Balance92099210921111701171Serials extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('serial_nums', 'order_id') || !Schema::hasColumn('serial_nums', 'order_item_id')) {
            return;
        }

        $targets = [
            [
                'order_id' => 9209,
                'paid_at' => '2026-03-31 09:58:40',
                'items' => [
                    ['product_sku_id' => 1170, 'item_id' => 16077, 'serial_id' => 33666, 'serial_num' => '26A103994'],
                    ['product_sku_id' => 1171, 'item_id' => 16078, 'serial_id' => 32772, 'serial_num' => '26A104031'],
                ],
            ],
            [
                'order_id' => 9210,
                'paid_at' => '2026-03-31 10:00:47',
                'items' => [
                    ['product_sku_id' => 1170, 'item_id' => 16079, 'serial_id' => 33691, 'serial_num' => '26A103960'],
                    ['product_sku_id' => 1171, 'item_id' => 16080, 'serial_id' => 32773, 'serial_num' => '26A104028'],
                ],
            ],
            [
                'order_id' => 9211,
                'paid_at' => '2026-03-31 10:02:43',
                'items' => [
                    ['product_sku_id' => 1170, 'item_id' => 16081, 'serial_id' => 33921, 'serial_num' => '24D103992'],
                    ['product_sku_id' => 1171, 'item_id' => 16082, 'serial_id' => 32774, 'serial_num' => '26A104024'],
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
