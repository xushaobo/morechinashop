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
        $grid->column('stock', __('库存数量'))->sortable()
	->totalRow(function ($amount) {
           return "<span class='text-danger text-bold'>总数量： {$amount} </span>";
	});
        $grid->column('ontheway', __('在途数量'))->sortable();

        // 移除新增按钮
        $grid->disableCreateButton();

        $grid->filter(function($filter){
                                $filter->disableIdFilter();

                                $filter->like('id','ID号');
                                $filter->like('title','货号');
                                $filter->like('description','分类描述');
                                $filter->notEqual('stock','剔除库存数量0');
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
	// 多图上传字段
	$form->hasMany('productSkuImage','产品SKU图片', function(Form\NestedForm $form) {
	   $form->image('path', '图片')->uniqueName()
		->disk('public')->move('images/productSkus')
		->rules('image|max:2048')
		->removable();
		
        })->useTable()->mode('table');

	$form->hasMany('serialnum','点击"新增"添加序列号', function(Form\NestedForm $form) {
		$form->text('serial_num','序列号')->rules('required');
		$form->text('ship_num','到货批次号')->rules('required')->default("0");
		$form->text('cost','成本')->rules('required')->default("0");
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
