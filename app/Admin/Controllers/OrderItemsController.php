<?php

namespace App\Admin\Controllers;

use App\Models\OrderItem;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;

use Illuminate\Support\Facades\DB; // 记得引入 DB Facade


class OrderItemsController extends Controller
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
            SELECT AVG(cost) 
            FROM serial_nums 
            WHERE productSku_id = order_items.product_sku_id
            AND deleted_at IS NOT NULL
            AND deleted_at BETWEEN orders.paid_at - INTERVAL 1 DAY AND orders.paid_at + INTERVAL 1 DAY
        ) as 成本价')
    )
    ->orderBy('orders.id', 'desc');
    $grid->column('序号')->display(function ($id) { return "<a href='/admin/stocks/$id'>$id</a>"; });
    $grid->column('日期')->sortable();
    $grid->column('客户名称');
    $grid->column('货号/型号');
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
    
        

        $grid->filter(function($filter){
            $filter->disableIdFilter();

	    $filter->between('order.paid_at','下单日期')->datetime();
            $filter->like('productSku.title','型号');
            $filter->like('order.remark','单位名称');
        });
	return $grid;
    }

    public function show($id, Content $content)
    {

    }
    public function edit($id, Content $content)
    {
	return $content	
	   ->header('编辑订单项目')
           ->body($this->form(true)->edit($id));
    }
    public function create(Content $content)
    {
	return $content	
	   ->header('创建订单项目')
           ->body($this->form(false));
    }
    public function form($isEditing = false)
    {
	$form = new Form(new OrderItem);
    }
}

