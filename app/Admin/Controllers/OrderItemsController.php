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
        
	 // 使用LEFT JOIN查询
	 $grid->model()
         ->leftJoin('orders', 'order_items.order_id', '=', 'orders.id')
	 ->leftJoin('product_skus', 'order_items.product_sku_id', '=', 'product_skus.id')
         ->leftJoin('serial_nums', function($join) {
             $join->on('order_items.product_sku_id', '=', 'serial_nums.productSku_id')
		  ->whereBetween('serial_nums.deleted_at', [
                 DB::raw('orders.paid_at - INTERVAL 2 DAY'),
                 DB::raw('orders.paid_at + INTERVAL 2 DAY')
             ])
                  ->whereNotNull('serial_nums.deleted_at');
         })
         ->select('order_items.order_id as 序号','orders.paid_at as 日期','orders.remark as 客户名称','product_skus.title as 货号/型号','order_items.price as 售价',DB::raw('COALESCE(serial_nums.cost, 0) as 成本价'),'order_items.amount as 数量')
	->groupBy('order_items.order_id','order_items.amount','order_items.price', 'product_skus.title', 'orders.paid_at', 'orders.remark',  DB::raw('COALESCE(serial_nums.cost, 0)'))
	 ->orderBy('orders.id', 'desc');
    
    $grid->column('序号')->display(function ($id) { return "<a href='/admin/stocks/$id'>$id</a>"; });
    $grid->column('日期')->sortable();
    $grid->column('客户名称');
    $grid->column('货号/型号');
    $grid->column('售价');
    $grid->column('成本价');
    $grid->column('数量');
    
        

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
           ->body($this->form()->edit($id));
    }
}

