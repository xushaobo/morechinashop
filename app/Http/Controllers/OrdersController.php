<?php

namespace App\Http\Controllers;

use App\Http\Requests\OrderRequest;
use App\Models\UserAddress;
use App\Models\Order;
use Illuminate\Http\Request;
use App\Services\OrderService;

use App\Events\OrderCreated;

use App\Http\Requests\PayConfirmRequest;
use App\Http\Requests\PriceUpdateRequest;
use App\Http\Requests\Admin\HandlePayConfirmRequest;
use App\Exceptions\InvalidRequestException;

use Carbon\Carbon;

class OrdersController extends Controller
{
    public function store(OrderRequest $request, OrderService $orderService)
    {
		$user    = $request->user();
        $address = UserAddress::find($request->input('address_id'));

        return $orderService->store($user, $address, $request->input('remark'), $request->input('items'));
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
            ->orderBy('created_at', 'desc')
            ->paginate();

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
        $this->authorize('own',$order);
        if ($order->refund_status !== Order::REFUND_STATUS_PENDING) {
            throw new InvalidRequestException('该订单已提交领导申批，请勿重复申请');
        }
        // 将用户输入的审批理由放到订单的 extra 字段中
        $total_price = $order->total_price ?: [];
        $total_price['total_price'] = $request->input('price');
        // 将订单申请审批状态改为已申请退款
        $order->update([
            'total_amount'         => $total_price['total_price'],
        ]);

        return $order;
    }

}
