<?php

namespace App\Admin\Controllers;

use App\Models\Order;
use App\Models\SerialNum;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Exceptions\InvalidRequestException;
use App\Http\Requests\Admin\HandlePayConfirmRequest;
// 处理空字符串

use App\Exceptions\InternalException;

class OrdersController extends Controller
{
    use HasResourceActions;

    public function index(Content $content)
    {
        return $content
            ->header('订单列表')
            ->body($this->grid());
    }

    public function show(Order $order, Content $content)
    {
        $order->load(['user', 'items.product', 'items.productSku']);

        $selectedSerialNos = $this->selectedSerialNos($order);

        return $content
            ->header('查看订单')
            // body方法可以接受Laravel视图作为参数
            ->body(view('admin.orders.show', [
                'order' => $order,
                'selectedSerialNos' => $selectedSerialNos,
            ]));
    }
    protected function grid()
    {
        $grid = new Grid(new Order);

        $grid->model()->whereNotNull('created_at')->orderBy('created_at','desc');

        $grid->id('序号');
        $grid->no('订单流水号');
	$grid->column('extra', '订单备注')->display(function ($tags) {
	$tags = is_array($tags) ? $tags : json_decode($tags, true);
        $result = implode('; ',$tags ?? []);//处理空字符串	
	return $result;
	});
        $grid->column('user.name','买家');
        $grid->total_amount('总金额')->sortable();
        $grid->total_stock_amount('总成本')->sortable();
        $grid->paid_at('支付时间')->sortable();
        $grid->remark('单位名称')->sortable();
        $grid->ship_status('物流')->display(function($value){
            return Order::$shipStatusMap[$value];
        });
        $grid->refund_status('审批状态')->sortable()->display(function($value){
            return Order::$refundStatusMap[$value];
        });
   //     $grid->column('serial_data','序列号');
        //禁用创建按钮
        $grid->disableCreateButton();
        $grid->actions(function ($actions){
            //禁用删除和编辑按钮
            $actions->disableDelete();
            $actions->disableEdit();
        });
        $grid->tools(function ($tools){
            //禁用批量删除按钮
            $tools->batch(function ($batch) {
                $batch->disableDelete();
            });
        });

        $grid->filter(function($filter){
            $filter->disableIdFilter();
            $filter->like('no','单订流水号');
            $filter->like('user.name','买家');
            $filter->like('remark','单位名称');
            $filter->like('seller','出货公司名称');
            $filter->like('serial_data','序列号');
            $filter->between('paid_at','支付时间')->datetime();
        });
        return $grid;
    }

    public function ship(Order $order,Request $request)
    {
        //判断当前订单发货状态是否为未发货
        if ($order->ship_status !== Order::SHIP_STATUS_PENDING) {
            throw new InvalidRequestException('订单已发货');
        }

        $data = $this->validate($request, [
            'express_company' => ['required'],
            'express_no' => ['required'],
        ],[], [
            'express_company' => '物流公司',
            'express_no' => '物流单号',
        ]);
        //将订单发货状态改为已发货，并存入物流信息
        $order->update([
            'ship_status' => Order::SHIP_STATUS_DELIVERED,
            //在Order模型的$casts属性里指明了ship_data是一个数组
            //因此这里可以直接把数组传过去
            'ship_data' => $data,
        ]);

        return redirect()->back();
    }

    public function serial(Order $order,Request $request)
    {
        $data = $this->validate($request, [
            'serial_no' => ['required'],
        ],[], [
            'serial_no' => '发货序列号',
        ]);

        $serialNos = $this->normalizeSerialNos($data['serial_no']);

        if ($serialNos->isEmpty()) {
            throw new InvalidRequestException('请填写有效序列号');
        }

        $order->load('items.productSku');
        $orderItems = $order->items->filter(function ($item) {
            return $item->productSku;
        });
        if ($orderItems->isEmpty()) {
            throw new InvalidRequestException('订单没有可绑定序列号的商品');
        }

        $orderItemsByRoot = $orderItems->groupBy(function ($item) {
            return (string) $this->skuRootId($item->productSku);
        });
        $remainingByItem = [];
        foreach ($orderItems as $item) {
            $remainingByItem[$item->id] = (int) $item->amount;
        }

        $serialsByNo = SerialNum::withTrashed()
            ->with(['productSku', 'orderItem.order'])
            ->whereIn('serial_num', $serialNos->all())
            ->get()
            ->groupBy('serial_num');

        $assignments = [];
        foreach ($serialNos as $serialNo) {
            $serial = $serialsByNo->get($serialNo, collect())->first(function ($serial) use ($order, $orderItemsByRoot) {
                if (!$serial->productSku) {
                    return false;
                }
                if ($serial->order_id && (int) $serial->order_id !== (int) $order->id) {
                    return false;
                }
                if ($serial->order_item_id && (!$serial->orderItem || (int) $serial->orderItem->order_id !== (int) $order->id)) {
                    return false;
                }

                return $orderItemsByRoot->has((string) $this->skuRootId($serial->productSku));
            });

            if (!$serial) {
                throw new InvalidRequestException("序列号 {$serialNo} 不存在、已绑定其它订单或不属于该订单商品");
            }

            $rootId = (string) $this->skuRootId($serial->productSku);
            $item = $orderItemsByRoot->get($rootId)->first(function ($item) use (&$remainingByItem) {
                return $remainingByItem[$item->id] > 0;
            });
            if (!$item) {
                throw new InvalidRequestException("序列号数量超过订单商品数量：{$serialNo}");
            }

            $assignments[] = compact('serial', 'item');
            $remainingByItem[$item->id]--;
        }

        DB::transaction(function () use ($order, $serialNos, $assignments) {
            SerialNum::withTrashed()
                ->where('order_id', $order->id)
                ->update(['order_id' => null, 'order_item_id' => null]);

            foreach ($assignments as $assignment) {
                $serial = $assignment['serial'];
                $serial->order_id = $order->id;
                $serial->order_item_id = $assignment['item']->id;
                if (!$serial->deleted_at) {
                    $serial->deleted_at = $order->paid_at ?: now();
                }
                $serial->save();
            }

            $order->update([
                'serial_data' => ['serial_no' => $serialNos->implode(', ')],
            ]);
        });

        return redirect()->back();
    }

    public function serialOptions(Order $order, Request $request)
    {
        $term = trim((string) $request->input('q', $request->input('term', '')));

        if ($term === '') {
            return response()->json(['results' => []]);
        }

        $order->load('items.productSku');
        $rootIds = $order->items
            ->filter(function ($item) {
                return $item->productSku;
            })
            ->map(function ($item) {
                return (int) $this->skuRootId($item->productSku);
            })
            ->filter()
            ->unique()
            ->values()
            ->all();

        if (empty($rootIds)) {
            return response()->json(['results' => []]);
        }

        $rootExpr = 'COALESCE(product_skus.root_sku_id, product_skus.master_sku_id, product_skus.id)';
        $searchRegex = '^[[:alpha:]]*' . preg_quote($term, '/');

        $serials = DB::table('serial_nums as sn')
            ->leftJoin('product_skus as product_skus', 'sn.productSku_id', '=', 'product_skus.id')
            ->select([
                'sn.id',
                'sn.serial_num',
                'sn.cost',
                'product_skus.title as sku_title',
                'product_skus.root_sku_id',
            ])
            ->whereIn(DB::raw($rootExpr), $rootIds)
            ->whereNull('sn.deleted_at')
            ->whereNull('sn.order_id')
            ->whereNull('sn.order_item_id')
            ->where(function ($query) use ($term, $searchRegex) {
                $query->where('sn.serial_num', 'like', $term . '%')
                    ->orWhereRaw('sn.serial_num REGEXP ?', [$searchRegex]);
            })
            ->orderBy('sn.serial_num')
            ->orderBy('sn.id')
            ->get();

        $results = $serials
            ->sort(function ($left, $right) {
                return $this->compareSerialAge($left, $right);
            })
            ->unique('serial_num')
            ->take(20)
            ->map(function ($serial) {
            $textParts = [
                $serial->serial_num,
                $serial->sku_title ?: ('SKU ' . $serial->root_sku_id),
            ];

            if ($serial->cost !== null && $serial->cost !== '') {
                $textParts[] = '成本' . $serial->cost;
            }

            return [
                'id' => $serial->serial_num,
                'text' => implode(' - ', $textParts),
            ];
        })->values();

        return response()->json(['results' => $results]);
    }

    protected function skuRootId($sku)
    {
        return $sku->root_sku_id ?: ($sku->master_sku_id ?: $sku->id);
    }

    protected function normalizeSerialNos($serialInput)
    {
        $values = is_array($serialInput) ? $serialInput : [$serialInput];

        return collect($values)
            ->flatMap(function ($serialNo) {
                return preg_split('/[\s,，、;；\/]+/u', trim((string) $serialNo));
            })
            ->map(function ($serialNo) {
                return trim($serialNo);
            })
            ->filter()
            ->unique()
            ->values();
    }

    protected function selectedSerialNos(Order $order)
    {
        $linkedSerialNos = SerialNum::withTrashed()
            ->where('order_id', $order->id)
            ->whereNotNull('order_item_id')
            ->orderBy('id')
            ->pluck('serial_num')
            ->all();

        $selectedSerialNos = $this->normalizeSerialNos($order->serial_data)
            ->merge($linkedSerialNos)
            ->filter()
            ->unique()
            ->values();

        if ($selectedSerialNos->count() > 0) {
            return $selectedSerialNos;
        }

        if ($order->ship_status !== Order::SHIP_STATUS_PENDING) {
            return collect();
        }

        return $this->defaultOldestSerialNos($order);
    }

    protected function defaultOldestSerialNos(Order $order)
    {
        $requirements = $order->items
            ->filter(function ($item) {
                return $item->productSku && (int) $item->amount > 0;
            })
            ->groupBy(function ($item) {
                return (string) $this->skuRootId($item->productSku);
            })
            ->map(function ($items) {
                return $items->sum(function ($item) {
                    return (int) $item->amount;
                });
            })
            ->filter(function ($amount, $rootId) {
                return (int) $rootId > 0 && (int) $amount > 0;
            });

        if ($requirements->isEmpty()) {
            return collect();
        }

        $rootExpr = 'COALESCE(product_skus.root_sku_id, product_skus.master_sku_id, product_skus.id)';
        $serialsByRoot = DB::table('serial_nums as sn')
            ->leftJoin('product_skus as product_skus', 'sn.productSku_id', '=', 'product_skus.id')
            ->select([
                'sn.id',
                'sn.serial_num',
                DB::raw($rootExpr . ' as root_id'),
            ])
            ->whereIn(DB::raw($rootExpr), $requirements->keys()->all())
            ->whereNull('sn.deleted_at')
            ->whereNull('sn.order_id')
            ->whereNull('sn.order_item_id')
            ->orderBy('sn.serial_num')
            ->orderBy('sn.id')
            ->get()
            ->groupBy(function ($serial) {
                return (string) $serial->root_id;
            });

        $serialNos = collect();
        foreach ($requirements as $rootId => $amount) {
            $serialNos = $serialNos->merge(
                $serialsByRoot->get((string) $rootId, collect())
                    ->sort(function ($left, $right) {
                        return $this->compareSerialAge($left, $right);
                    })
                    ->unique('serial_num')
                    ->take((int) $amount)
                    ->pluck('serial_num')
            );
        }

        return $serialNos->values();
    }

    protected function compareSerialAge($left, $right)
    {
        $leftKey = $this->serialAgeKey($left->serial_num);
        $rightKey = $this->serialAgeKey($right->serial_num);

        if ($leftKey !== $rightKey) {
            return $leftKey < $rightKey ? -1 : 1;
        }

        $serialCompare = strcmp($left->serial_num, $right->serial_num);
        if ($serialCompare !== 0) {
            return $serialCompare;
        }

        $leftId = isset($left->id) ? (int) $left->id : 0;
        $rightId = isset($right->id) ? (int) $right->id : 0;

        if ($leftId === $rightId) {
            return 0;
        }

        return $leftId < $rightId ? -1 : 1;
    }

    protected function serialAgeKey($serialNo)
    {
        $serialNo = trim((string) $serialNo);

        if (preg_match('/^[A-Za-z]*(20[0-9]{2})([0-9]{2})([0-9]{2})/', $serialNo, $matches)) {
            $year = (int) $matches[1];
            $month = (int) $matches[2];
            $day = (int) $matches[3];

            if (checkdate($month, $day, $year)) {
                return (int) date('oW', strtotime(sprintf('%04d-%02d-%02d', $year, $month, $day)));
            }
        }

        // Some suppliers encode the month as A-L after a two-digit year,
        // for example 26A... means January 2026 and 24D... means April 2024.
        if (preg_match('/^[A-Za-z]*([0-9]{2})([A-L])/', $serialNo, $matches)) {
            $year = 2000 + (int) $matches[1];
            $month = ord($matches[2]) - ord('A') + 1;

            return ($year * 100) + $month;
        }

        if (preg_match('/^[A-Za-z]*([0-9]{2})([0-9]{2})/', $serialNo, $matches)) {
            $year = 2000 + (int) $matches[1];
            $week = (int) $matches[2];

            if ($week >= 1 && $week <= 53) {
                return ($year * 100) + $week;
            }
        }

        return PHP_INT_MAX;
    }
    public function memo(Order $order,Request $request)
    {
        //将订单发货状态改为已发货，并存入物流信息
        $order->update([
            'payment_no' => $request->input('memo'),
        ]);

        return redirect()->back();
    }
	
    public function back(Order $order,Request $request)
    {
        $order->update([
		'refund_status' => Order::REFUND_STATUS_APPLIED,
	    ]);
        return redirect()->back();
    }
    public function plus(Order $order,Request $request)
    {
        $data = $this->validate($request, [
            'total_amount' => ['required'],
        ],[], [
            'total_amount' => '修改订单总金额',
        ]);
        //将订单发货状态改为已发货，并存入物流信息
        $order->update([
            'total_amount' => $request->input('total_amount'),
        ]);

        return redirect()->back();
    }
    public function handlePayConfirm(Order $order,HandlePayConfirmRequest $request)
    {
        if($order->refund_status !== Order::REFUND_STATUS_APPLIED){
            throw new InvalidRequestException('订单状态不正确');
        }
    
        if($request->input('aggree')){
            //清空拒绝审批理由
            $extra = $order->extra ?: [];
            unset($extra['refund_disagree_reason']);
            $order->update([
                'extra' => $extra,
            ]);
            //调用审批逻辑 func: _payconfirmOrder()
            $this->_payconfirmOrder($order);
        } else {
            //将拒绝的理由放到订单的extra字段中 
            $extra = $order->extra ?: [];
            $extra['refund_disagree_reason'] = $request->input('reason');
            $order->update([
                'refund_status' => Order::REFUND_STATUS_SUCCESS,
                'extra' => $extra,
            ]);
        }
        return $order;
    }

    protected function _payconfirmOrder(Order $order)
    {
        dd("11111");
    }
}
