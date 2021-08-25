<?php

namespace App\Admin\Controllers;

use App\Models\OrderItem;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;

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
	//展示关联关系的字段时，使用column方法
//	$grid->number('序号');
//	$grid->rows(function ($row, $number) {
//	  $row->column('number', $number);
//	});
	$grid->column('order.id','序号')->sortable();
	$grid->column('order.paid_at','下单日期')->sortable();
	$grid->column('order.remark','单位名称')->sortable();
	$grid->column('productSku.title','型号')->sortable();
	$grid->column('price','售出单价')->sortable();
	$grid->column('stock_price','成本单价')->sortable();
	$grid->column('amount','数量');
        
        

        $grid->filter(function($filter){
            $filter->disableIdFilter();

	    $filter->like('order.paid_at','下单日期');
            $filter->like('productSku.title','型号');
            $filter->like('order.remark','单位名称');
        });
	return $grid;
    }

}

