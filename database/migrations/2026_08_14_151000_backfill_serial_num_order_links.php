<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;

class BackfillSerialNumOrderLinks extends Migration
{
    public function up()
    {
        if (
            !Schema::hasColumn('serial_nums', 'order_id') ||
            !Schema::hasColumn('serial_nums', 'order_item_id') ||
            !Schema::hasColumn('product_skus', 'root_sku_id')
        ) {
            return;
        }

        $this->backfillFromOrderSerialData();
    }

    public function down()
    {
        //
    }

    protected function backfillFromOrderSerialData()
    {
        DB::table('orders')
            ->select('id', 'serial_data', 'paid_at')
            ->whereNotNull('serial_data')
            ->orderBy('id')
            ->chunk(100, function ($orders) {
                foreach ($orders as $order) {
                    $serialNos = $this->parseSerialNos($order->serial_data);

                    if (empty($serialNos)) {
                        continue;
                    }

                    $this->backfillOrderSerials($order, $serialNos);
                }
            });
    }

    protected function backfillOrderSerials($order, array $serialNos)
    {
        $items = DB::table('order_items')
            ->join('product_skus', 'order_items.product_sku_id', '=', 'product_skus.id')
            ->select(
                'order_items.id',
                'order_items.amount',
                DB::raw('product_skus.root_sku_id as root_sku_id')
            )
            ->where('order_items.order_id', $order->id)
            ->orderBy('order_items.id')
            ->get();

        if ($items->isEmpty()) {
            return;
        }

        $itemsByRoot = [];
        $remainingByItem = [];
        foreach ($items as $item) {
            if (!$item->root_sku_id) {
                continue;
            }

            $rootId = (string) $item->root_sku_id;
            $itemsByRoot[$rootId][] = $item;
            $remainingByItem[$item->id] = (int) $item->amount;
        }

        if (empty($itemsByRoot)) {
            return;
        }

        $serialRows = DB::table('serial_nums')
            ->join('product_skus', 'serial_nums.productSku_id', '=', 'product_skus.id')
            ->select(
                'serial_nums.id',
                'serial_nums.serial_num',
                DB::raw('product_skus.root_sku_id as root_sku_id')
            )
            ->whereIn('serial_nums.serial_num', $serialNos)
            ->where(function ($query) use ($order) {
                $query->whereNull('serial_nums.order_id')
                    ->orWhere('serial_nums.order_id', $order->id);
            })
            ->where(function ($query) use ($order) {
                $query->whereNull('serial_nums.deleted_at');

                if ($order->paid_at) {
                    $paidAt = strtotime($order->paid_at);
                    $query->orWhereBetween('serial_nums.deleted_at', [
                        date('Y-m-d H:i:s', $paidAt - 3600),
                        date('Y-m-d H:i:s', $paidAt + 3600),
                    ]);
                }
            })
            ->orderBy('serial_nums.id')
            ->get();

        if ($serialRows->isEmpty()) {
            return;
        }

        $serialsByNo = [];
        foreach ($serialRows as $serial) {
            $serialsByNo[$serial->serial_num][] = $serial;
        }

        $updates = [];
        $usedSerialIds = [];
        foreach ($serialNos as $serialNo) {
            if (empty($serialsByNo[$serialNo])) {
                continue;
            }

            foreach ($serialsByNo[$serialNo] as $serial) {
                if (isset($usedSerialIds[$serial->id]) || !$serial->root_sku_id) {
                    continue;
                }

                $rootId = (string) $serial->root_sku_id;
                if (empty($itemsByRoot[$rootId])) {
                    continue;
                }

                $itemId = $this->firstAvailableItemId($itemsByRoot[$rootId], $remainingByItem);
                if (!$itemId) {
                    continue;
                }

                $updates[$itemId][] = $serial->id;
                $usedSerialIds[$serial->id] = true;
                $remainingByItem[$itemId]--;
                break;
            }
        }

        foreach ($updates as $itemId => $serialIds) {
            DB::table('serial_nums')
                ->whereIn('id', $serialIds)
                ->update([
                    'order_id' => $order->id,
                    'order_item_id' => $itemId,
                ]);

            if ($order->paid_at) {
                DB::table('serial_nums')
                    ->whereIn('id', $serialIds)
                    ->whereNull('deleted_at')
                    ->update(['deleted_at' => $order->paid_at]);
            }
        }
    }

    protected function firstAvailableItemId(array $items, array $remainingByItem)
    {
        foreach ($items as $item) {
            if (!empty($remainingByItem[$item->id])) {
                return $item->id;
            }
        }

        return null;
    }

    protected function parseSerialNos($serialData)
    {
        $value = $serialData;
        $decoded = json_decode($serialData, true);

        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            $value = isset($decoded['serial_no']) ? $decoded['serial_no'] : '';
        }

        if (is_array($value)) {
            $value = implode(' ', $value);
        }

        $value = trim((string) $value);
        if ($value === '') {
            return [];
        }

        $serialNos = [];
        foreach (preg_split('/[\s,，;；]+/u', $value) as $serialNo) {
            $serialNo = trim($serialNo);
            if ($serialNo !== '' && !isset($serialNos[$serialNo])) {
                $serialNos[$serialNo] = $serialNo;
            }
        }

        return array_values($serialNos);
    }

}
