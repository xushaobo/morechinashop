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


class OrderItemsController extends AdminController
{
    use HasResourceActions; 
	
    public function index(Content $content)
    {
	return $content
	  ->header('成本列表')
	  ->body($this->grid());
    }
    protected function grid()
    {
        $grid = new Grid(new OrderItem);
	// 全部关闭
//	$grid->disableActions();
	 // 使用LEFT JOIN查询
$grid->model()
    ->leftJoin('orders', 'order_items.order_id', '=', 'orders.id')
    ->leftJoin('product_skus', 'order_items.product_sku_id', '=', 'product_skus.id')
    ->select(
        'order_items.id',
        'order_items.order_id as 序号',
        'orders.paid_at as 日期',
        'orders.remark as 客户名称',
        'product_skus.title as 货号/型号',
        'order_items.price as 售价',
        'order_items.amount as 数量',
	DB::raw('(
            SELECT GROUP_CONCAT(serial_num SEPARATOR ", ")
            FROM serial_nums 
            WHERE productSku_id = order_items.product_sku_id
            AND deleted_at IS NOT NULL
            AND deleted_at BETWEEN orders.paid_at - INTERVAL 1 HOUR AND orders.paid_at + INTERVAL 1 HOUR 
        ) as 序列号列表'),
	DB::raw('(
            SELECT GROUP_CONCAT(deleted_at SEPARATOR ", ")
            FROM serial_nums 
            WHERE productSku_id = order_items.product_sku_id
            AND deleted_at IS NOT NULL
            AND deleted_at BETWEEN orders.paid_at - INTERVAL 1 HOUR AND orders.paid_at + INTERVAL 1 HOUR 
        ) as 出库时间'),
	 DB::raw('(
            SELECT COUNT(serial_num)  -- 添加序列号数量统计
            FROM serial_nums 
            WHERE productSku_id = order_items.product_sku_id
            AND deleted_at IS NOT NULL
            AND deleted_at BETWEEN orders.paid_at - INTERVAL 1 HOUR AND orders.paid_at + INTERVAL 1 HOUR
        ) as 序列号数量'),
        DB::raw('(
            SELECT AVG(cost)
            FROM serial_nums 
            WHERE productSku_id = order_items.product_sku_id
            AND deleted_at IS NOT NULL
            AND deleted_at BETWEEN orders.paid_at - INTERVAL 1 DAY AND orders.paid_at + INTERVAL 1 DAY
        ) as 成本价'),
	
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





	$filter->where(function ($query) {
        $value = request()->input('consistent'); // 从请求中获取值
        
        if ($value == 1) {
            // 一致的情况
            $query->whereRaw('COALESCE((
                SELECT COUNT(serial_num) 
                FROM serial_nums 
                WHERE productSku_id = order_items.product_sku_id
                AND deleted_at IS NOT NULL
                AND deleted_at BETWEEN orders.paid_at - INTERVAL 1 HOUR AND orders.paid_at + INTERVAL 1 HOUR
            ), 0) = order_items.amount');
        } elseif ($value == 2) {
            // 不一致的情况
            $query->whereRaw('COALESCE((
                SELECT COUNT(serial_num) 
                FROM serial_nums 
                WHERE productSku_id = order_items.product_sku_id
                AND deleted_at IS NOT NULL
                AND deleted_at BETWEEN orders.paid_at - INTERVAL 1 HOUR AND orders.paid_at + INTERVAL 1 HOUR
            ), 0) != order_items.amount');
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

