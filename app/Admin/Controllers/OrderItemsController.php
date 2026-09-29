<?php

namespace App\Admin\Controllers;

use App\Models\OrderItem;
use App\Models\SerialNum;
use Encore\Admin\Controllers\AdminController;
use App\Admin\Actions\EditOutboundTimeAction; // 导入修改删除时间类
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Grid;
use Encore\Admin\Form;
use Encore\Admin\Layout\Content;

use Illuminate\Support\Facades\DB; // 记得引入 DB Facade
use Illuminate\Http\Request;
use App\Exceptions\InvalidRequestException;


class OrderItemsController extends AdminController
{
    use HasResourceActions; 
	
    public function index(Content $content)
    {
	return $content
	  ->header('成本列表')
	  ->body($this->grid());
    }

    public function serialForm(OrderItem $orderItem, Content $content)
    {
        $orderItem->load(['order', 'product', 'productSku']);

        $selectedSerials = SerialNum::withTrashed()
            ->where('productSku_id', $orderItem->product_sku_id)
            ->where(function ($query) use ($orderItem) {
                $query->where('order_item_id', $orderItem->id)
                    ->orWhere(function ($query) use ($orderItem) {
                        $query->where('order_id', $orderItem->order_id)
                            ->whereNull('order_item_id');
                    });
            })
            ->orderBy('id')
            ->get(['id', 'serial_num']);

        return $content
            ->header('填写序列号')
            ->body(view('admin.order_items.serial', [
                'orderItem' => $orderItem,
                'selectedSerialOptions' => $selectedSerials->map(function ($serial) {
                    return [
                        'value' => 'sn:' . $serial->id,
                        'text' => $serial->serial_num,
                    ];
                })->values(),
            ]));
    }

    public function serialOptions(OrderItem $orderItem, Request $request)
    {
        $term = trim((string) $request->input('q', $request->input('term', '')));

        $serials = DB::table('serial_nums as sn')
            ->leftJoin('product_skus as product_skus', 'sn.productSku_id', '=', 'product_skus.id')
            ->select([
                'sn.id',
                'sn.serial_num',
                'sn.cost',
                'sn.order_item_id',
                'product_skus.title as sku_title',
            ])
            ->where('sn.productSku_id', $orderItem->product_sku_id)
            ->where(function ($query) use ($orderItem) {
                $query->where(function ($query) {
                    $query->whereNull('sn.deleted_at')
                        ->whereNull('sn.order_id')
                        ->whereNull('sn.order_item_id');
                })->orWhere('sn.order_item_id', $orderItem->id)
                    ->orWhere(function ($query) use ($orderItem) {
                        $query->where('sn.order_id', $orderItem->order_id)
                            ->whereNull('sn.order_item_id');
                    });
            })
            ->when($term !== '', function ($query) use ($term) {
                $query->where('sn.serial_num', 'like', $term . '%');
            })
            ->orderBy('sn.serial_num')
            ->orderBy('sn.id')
            ->limit(50)
            ->get();

        return response()->json([
            'results' => $serials->map(function ($serial) {
                $text = $serial->serial_num;
                if ($serial->cost !== null && $serial->cost !== '') {
                    $text .= ' - 成本' . $serial->cost;
                }

                return [
                    'id' => 'sn:' . $serial->id,
                    'text' => $text,
                ];
            })->values(),
        ]);
    }

    public function updateSerial(OrderItem $orderItem, Request $request)
    {
        $orderItem->load(['order', 'productSku']);
        if (!$orderItem->productSku) {
            throw new InvalidRequestException('该订单明细没有关联产品 SKU');
        }

        $serialNos = $this->normalizeSerialNos($request->input('serial_no', []));
        if ($serialNos->count() > (int) $orderItem->amount) {
            throw new InvalidRequestException('序列号数量不能超过订单明细数量');
        }

        $serialIds = $serialNos
            ->filter(function ($serialNo) {
                return preg_match('/^sn:[0-9]+$/', (string) $serialNo);
            })
            ->map(function ($serialNo) {
                return (int) substr((string) $serialNo, 3);
            })
            ->values();
        $plainSerialNos = $serialNos
            ->reject(function ($serialNo) {
                return preg_match('/^sn:[0-9]+$/', (string) $serialNo);
            })
            ->values();

        $serials = $serialNos->isEmpty()
            ? collect()
            : SerialNum::withTrashed()
                ->with(['orderItem.order'])
                ->where('productSku_id', $orderItem->product_sku_id)
                ->where(function ($query) use ($serialIds, $plainSerialNos) {
                    if ($serialIds->isNotEmpty()) {
                        $query->whereIn('id', $serialIds->all());
                    }
                    if ($plainSerialNos->isNotEmpty()) {
                        $method = $serialIds->isNotEmpty() ? 'orWhereIn' : 'whereIn';
                        $query->{$method}('serial_num', $plainSerialNos->all());
                    }
                })
                ->get();

        $serialsByNo = $serials->groupBy('serial_num');
        $serialsById = $serials->keyBy('id');
        $selected = collect();
        $usedIds = [];

        foreach ($serialNos as $serialNo) {
            $requestedId = preg_match('/^sn:([0-9]+)$/', (string) $serialNo, $matches)
                ? (int) $matches[1]
                : null;
            $candidates = $requestedId
                ? collect([$serialsById->get($requestedId)])->filter()
                : $serialsByNo->get($serialNo, collect());
            $serial = $candidates->first(function ($serial) use ($orderItem, &$usedIds) {
                if (in_array((int) $serial->id, $usedIds, true)) {
                    return false;
                }
                if ($serial->order_id && (int) $serial->order_id !== (int) $orderItem->order_id) {
                    return false;
                }
                if ($serial->order_item_id && (int) $serial->order_item_id !== (int) $orderItem->id) {
                    return false;
                }

                return true;
            });

            if (!$serial) {
                throw new InvalidRequestException("序列号 {$serialNo} 不存在、已绑定其它订单或不属于当前 product_sku_id");
            }

            $usedIds[] = (int) $serial->id;
            $selected->push($serial);
        }

        DB::transaction(function () use ($orderItem, $selected) {
            SerialNum::withTrashed()
                ->where('productSku_id', $orderItem->product_sku_id)
                ->where('order_id', $orderItem->order_id)
                ->where(function ($query) use ($orderItem) {
                    $query->where('order_item_id', $orderItem->id)
                        ->orWhereNull('order_item_id');
                })
                ->update([
                    'order_id' => null,
                    'order_item_id' => null,
                    'deleted_at' => null,
                ]);

            foreach ($selected as $serial) {
                SerialNum::withTrashed()
                    ->where('id', $serial->id)
                    ->update([
                        'order_id' => $orderItem->order_id,
                        'order_item_id' => $orderItem->id,
                        'deleted_at' => $serial->deleted_at ?: ($orderItem->order->paid_at ?: now()),
                    ]);
            }

            $serialData = SerialNum::withTrashed()
                ->where(function ($query) use ($orderItem) {
                    $query->where('order_id', $orderItem->order_id)
                        ->orWhereHas('orderItem', function ($query) use ($orderItem) {
                            $query->where('order_id', $orderItem->order_id);
                        });
                })
                ->orderBy('id')
                ->pluck('serial_num')
                ->implode(', ');

            $orderItem->order->update([
                'serial_data' => $serialData !== '' ? ['serial_no' => $serialData] : null,
            ]);
        });

        admin_toastr('序列号已保存，并已同步到订单详情', 'success');

        return redirect(admin_url('stocks'));
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
    protected function grid()
    {
        $grid = new Grid(new OrderItem);
	// 全部关闭
//	$grid->disableActions();
	 // 使用LEFT JOIN查询
$linkedSerials = DB::raw('(
    SELECT order_item_id,
           GROUP_CONCAT(serial_num ORDER BY id SEPARATOR ", ") as serial_nums,
           GROUP_CONCAT(deleted_at ORDER BY id SEPARATOR ", ") as outbound_times,
           COUNT(id) as serial_count,
           AVG(cost) as avg_cost
    FROM serial_nums
    WHERE order_item_id IS NOT NULL
      AND deleted_at IS NOT NULL
    GROUP BY order_item_id
) as linked_serial_nums');

$fallbackSerials = DB::raw('(
    SELECT productSku_id,
           deleted_at,
           GROUP_CONCAT(serial_num ORDER BY id SEPARATOR ", ") as serial_nums,
           GROUP_CONCAT(deleted_at ORDER BY id SEPARATOR ", ") as outbound_times,
           COUNT(id) as serial_count,
           AVG(cost) as avg_cost
    FROM serial_nums
    WHERE order_item_id IS NULL
      AND deleted_at IS NOT NULL
    GROUP BY productSku_id, deleted_at
) as fallback_serial_nums');

$grid->model()
    ->leftJoin('orders', 'order_items.order_id', '=', 'orders.id')
    ->leftJoin('product_skus', 'order_items.product_sku_id', '=', 'product_skus.id')
    ->leftJoin($linkedSerials, 'linked_serial_nums.order_item_id', '=', 'order_items.id')
    ->leftJoin($fallbackSerials, function ($join) {
        $join->on('fallback_serial_nums.productSku_id', '=', 'order_items.product_sku_id')
            ->on('fallback_serial_nums.deleted_at', '=', 'orders.paid_at');
    })
    ->select(
        'order_items.id',
        'order_items.order_id as 序号',
        'orders.paid_at as 日期',
        'orders.remark as 客户名称',
        'product_skus.title as 货号/型号',
        'order_items.price as 售价',
        'order_items.amount as 数量',
	DB::raw('COALESCE(linked_serial_nums.serial_nums, fallback_serial_nums.serial_nums) as 序列号列表'),
	DB::raw('COALESCE(linked_serial_nums.outbound_times, fallback_serial_nums.outbound_times) as 出库时间'),
	DB::raw('COALESCE(linked_serial_nums.serial_count, fallback_serial_nums.serial_count, 0) as 序列号数量'),
	DB::raw('COALESCE(linked_serial_nums.avg_cost, fallback_serial_nums.avg_cost) as 成本价'),

    )
    ->orderBy('orders.id', 'desc');
    $grid->column('序号')->display(function ($id) { return "<a href='/admin/stocks/$id'>$id</a>"; });
    $grid->column('日期')->sortable();
    $grid->column('客户名称')->sortable();
    $grid->column('货号/型号')->sortable();
    $grid->column('售价');
    $grid->column('成本价')->sortable();
    $grid->column('数量');
    // 然后在Grid的显示回调中计算利润
    $grid->column('利润')->display(function () {
    $cost = $this->成本价 ?? 0;
    $sales = $this->售价 * $this->数量;
    $totalCost = $cost * $this->数量;
    $profit = $sales - $totalCost;
    
    return number_format($profit, 2);
    });
    $grid->column('操作')->display(function () {
        $url = route('admin.orderItems.serial', ['orderItem' => $this->id]);
        return '<a href="' . e($url) . '" class="btn btn-xs btn-primary">填写序列号</a>';
    });
    // 显示序列号
    $grid->column('序列号')->display(function () {
       return $this->序列号列表 ?: '无';
    });
    
    $grid->column('数量一致性')->display(function () {
    $serialCount = $this->序列号数量 ?: 0;
    $orderAmount = $this->数量;
    $difference = $serialCount - $orderAmount;
    
    if ($serialCount == $orderAmount) {
        return "<span style='color: green; font-weight: bold;'>✓ 一致</span>";
    } elseif ($serialCount > $orderAmount) {
        return "<span style='color: orange; font-weight: bold;'>! 序列号多{$difference}个</span>";
    } else {
        return "<span style='color: red; font-weight: bold;'>✗ 序列号少" . abs($difference) . "个</span>";
    }
});

// 在 Grid 中这样定义
$grid->column('出库时间')->display(function () {
    // 显示逻辑
    return $this->出库时间 ?: '无出库记录';
});

    
        

        $grid->filter(function($filter){
            $filter->disableIdFilter();
	    $filter->between('order.paid_at','下单日期')->datetime();
            $filter->like('productSku.title','型号');
            $filter->like('order.remark','单位名称');
		
	    $serialCountSql = 'CASE
            WHEN COALESCE((SELECT COUNT(id) FROM serial_nums WHERE order_item_id = order_items.id AND deleted_at IS NOT NULL), 0) > 0
            THEN (SELECT COUNT(id) FROM serial_nums WHERE order_item_id = order_items.id AND deleted_at IS NOT NULL)
            ELSE COALESCE((SELECT COUNT(id) FROM serial_nums WHERE order_item_id IS NULL AND productSku_id = order_items.product_sku_id AND deleted_at = orders.paid_at), 0)
        END';

	$filter->where(function ($query) {
	        $serialNum = $this->input;

	        $query->whereRaw('(EXISTS (
            SELECT 1 FROM serial_nums sn
            WHERE sn.order_item_id = order_items.id
            AND sn.serial_num LIKE ?
            AND sn.deleted_at IS NOT NULL
	        ) OR EXISTS (
            SELECT 1 FROM serial_nums sn
            WHERE sn.order_item_id IS NULL
            AND sn.productSku_id = order_items.product_sku_id
            AND sn.deleted_at = orders.paid_at
            AND sn.serial_num LIKE ?
	        ))', ["%{$serialNum}%", "%{$serialNum}%"]);
	    }, '序列号');



	$filter->where(function ($query) use ($serialCountSql) {
        $value = $this->input;

        if ($value == 1) {
            // 一致的情况：优先精确订单明细关联，其次兼容旧的精确SKU出库时间。
	   $query->whereRaw("{$serialCountSql} = order_items.amount");
        } elseif ($value == 2) {
            // 不一致的情况：优先精确订单明细关联，其次兼容旧的精确SKU出库时间。
          $query->whereRaw("{$serialCountSql} != order_items.amount");
        }
    }, '数量一致性', 'consistent')->radio([
	'all' => '显示所有',
        1 => '一致',
        2 => '不一致',
    ]);
        });
	return $grid;
    }


     protected function detail($id)
    {
        $show = new Show(OrderItem::findOrFail($id));

        $show->field('id', __('ID'));
        $show->field('order_id', __('订单ID'));
        $show->field('product_name', __('产品名称'));
        $show->field('price', __('价格'));
        $show->field('quantity', __('数量'));
        $show->field('created_at', __('创建时间'));
        $show->field('updated_at', __('更新时间'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new OrderItem());

        $form->display('order_id', __('订单ID'));
        $form->text('product_sku_id', __('产品ID'));
        $form->decimal('price', __('价格'));
        $form->number('quantity', __('数量'));
        $form->display('created_at', __('创建时间'));
        $form->display('updated_at', __('更新时间'));

        return $form;
    }
}

