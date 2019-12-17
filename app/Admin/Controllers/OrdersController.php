<?php

namespace App\Admin\Controllers;

use App\Models\Order;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Illuminate\Http\Request;
use App\Exceptions\InvalidRequestException;

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
        return $content
            ->header('查看订单')
            // body方法可以接受Laravel视图作为参数
            ->body(view('admin.orders.show',['order' => $order]));
    }
    protected function grid()
    {
        $grid = new Grid(new Order);

        $grid->model()->whereNotNull('created_at')->orderBy('created_at','desc');

        $grid->no('订单流水号');

        $grid->column('user.name','买家');
        $grid->total_amount('总金额')->sortable();
        $grid->paid_at('支付时间')->sortable();
        $grid->ship_status('物流')->display(function($value){
            return Order::$shipStatusMap[$value];
        });
        $grid->refund_status('退款状态')->display(function($value){
            return Order::$refundStatusMap[$value];
        });
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
}
