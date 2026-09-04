<?php

namespace App\Http\Controllers;

use App\Http\Requests\OrderRequest;
use App\Models\UserAddress;
use App\Models\Order;
use App\Models\SerialNum;
use Illuminate\Http\Request;
use App\Services\OrderService;

use App\Events\OrderCreated;

use App\Http\Requests\PayConfirmRequest;
use App\Http\Requests\PriceUpdateRequest;
use App\Http\Requests\Admin\HandlePayConfirmRequest;
use App\Exceptions\InvalidRequestException;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class OrdersController extends Controller
{
    public function store(OrderRequest $request, OrderService $orderService)
    {
	$user    = $request->user();
        $address = UserAddress::find($request->input('address_id'));

        return $orderService->store($user, $address, $request->input('remark'),$request->input('seller'), $request->input('items'));
    }

    protected function afterCreated(Order $order)
    {
        event(new OrderCreated($order));
    }

	 public function index(Request $request)
    {
        $orders = Order::query()
            // 使用 with 方法预加载，避免N + 1问题
            ->with(['items.product', 'items.productSku'])
            ->where('user_id', $request->user()->id)
            ->orderBy('id', 'desc')
            ->paginate(10);

        return view('orders.index', ['orders' => $orders]);
    }

	public function show(Order $order, Request $request)
    {
        $this->afterCreated($order);
        $this->authorize('own',$order);
        return view('orders.show', ['order' => $order->load(['items.productSku', 'items.product'])]);
    }

    public function payConfirm(Order $order, PayConfirmRequest $request)
    {
        $this->authorize('own',$order);
        // 校验订单是否属于当前用户
        // 判断订单是否已付款
        //if (!$order->paid_at) {
        //    throw new InvalidRequestException('该订单未支付，不可退款');
        //}
        // 判断订单申请审批状态是否正确
        if ($order->refund_status !== Order::REFUND_STATUS_PENDING) {
            throw new InvalidRequestException('该订单已提交领导申批，请勿重复申请');
        }
        // 将用户输入的审批理由放到订单的 extra 字段中
        $extra                  = $order->extra ?: [];
        $extra['refund_reason'] = $request->input('data');
        // 将订单申请审批状态改为已申请退款
        $order->update([
            'paid_at' => Carbon::now(),
            'refund_status' => Order::REFUND_STATUS_APPLIED,
            'extra'         => $extra,
        ]);

        return $order;
    }

	      public function priceUpdate(Order $order, PriceUpdateRequest $request)
	    {
		DB::transaction(function () use ($order) {
		    $order->load(['items.productSku']);
		    $orderItemIds = $order->items->pluck('id')->all();

		    SerialNum::withTrashed()
			->where(function ($query) use ($order, $orderItemIds) {
			    $query->where('order_id', $order->id);

			    if (!empty($orderItemIds)) {
				$query->orWhereIn('order_item_id', $orderItemIds);
			    }
			})
			->update([
			    'order_id' => null,
			    'order_item_id' => null,
			    'deleted_at' => null,
			]);

		    $order->update([
			'closed' => true
		    ]);

		    foreach ($order->items as $item) {
			$item->productSku->addStock($item->amount);
		    }

		    $order->delete();
		});
	
		return $order;
		
	    }

}
