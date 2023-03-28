<?php

namespace App\Admin\Controllers;

use App\Models\ProductSku;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;


class ProductSkuController extends Controller
{
    use HasResourceActions;
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = "商品库存和在途";

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new ProductSku());

        $grid->column('id', __('Id'));
        $grid->column('title', __('品名'));
        $grid->column('description', __('分类描述'))->sortable();
        $grid->column('stock', __('库存数量'))->sortable();
        $grid->column('ontheway', __('在途数量'))->sortable();

        $grid->filter(function($filter){
                                $filter->disableIdFilter();

                                $filter->like('title','货号');
                                $filter->like('description','分类描述');
                        });
        return $grid;
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new ProductSku());

        $form->text('title', __('品名货号'));
        $form->text('description', __('分类描述'));
        $form->number('ontheway', __('在途数量'));
        $form->number('stock', __('库存数量'));

/**
        $form->text('description', __('Description'));
        $form->decimal('price', __('Price'));
        $form->decimal('stock_price', __('Stock price'))->default(0.00);
        $form->number('product_id', __('Product id'));
**/
	$form->hasMany('serialnum','点击"新增"添加序列号', function(Form\NestedForm $form) {
		$form->text('serial_num','序列号')->rules('required');
		$form->text('ship_num','到货批次号')->rules('required');
		$form->date('created_at', '创建时间')->rules('required')->default(date('Y-m-d',strtotime("-0 day")));
	});
        return $form;
    }


	  public function create(Content $content)
    {
        return $content
            ->header('新增SKU')
            ->body($this->form());
    }

    public function index(Content $content)
    {
        return $content
            ->header('Index')
            ->description('description')
            ->body($this->grid());
    }

    public function edit($id, Content $content)
    {
	return $content	
	   ->header('编辑库存和序列号')
           ->body($this->form()->edit($id));
    }

    
}
