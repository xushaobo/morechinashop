<?php

namespace App\Admin\Controllers;

use App\Models\ProductSku;
use App\Models\SerialNum;
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
	$grid->column('master_sku_id', ('关联主商品ID'))->sortable();
        $grid->column('title', __('品名'));
        $grid->column('description', __('分类描述'))->sortable();
        $grid->column('total_stock', __('总库存数量'))->display(function () {
		return $this->total_stock; //调用访问控制器
	});
        $grid->column('stock', __('自身库存数量'))->sortable()
	->totalRow(function ($amount) {
           return "<span class='text-danger text-bold'>总数量： {$amount} </span>";
	});
	  $grid->column('serial_count', '序列号数量')
        ->display(function () {
            return SerialNum::where('productSku_id', $this->id)
                ->whereNull('deleted_at')
                ->count();
        });
	$grid->column('diff', '库存与序列号差值')->display(function () {
    $stock = $this->stock;
    $serialCount = SerialNum::where('productSku_id', $this->id)
        ->whereNull('deleted_at')
        ->count();
    $diff = $stock - $serialCount;
    if ($diff > 0) {
        return "<span style='color: orange;'>库存多 {$diff}</span>";
    } elseif ($diff < 0) {
        return "<span style='color: red;'>序列号多 " . abs($diff) . "</span>";
    } else {
        return "<span style='color: green;'>一致</span>";
    }
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
					$filter->where(function ($query) {
						$serialCountSql = '(SELECT COUNT(*) FROM serial_nums WHERE serial_nums.productSku_id = product_skus.id AND serial_nums.deleted_at IS NULL)';

						if ($this->input === 'same') {
							$query->whereRaw('product_skus.stock = '.$serialCountSql);
						}

						if ($this->input === 'different') {
							$query->whereRaw('product_skus.stock <> '.$serialCountSql);
						}
					}, '库存与序列号')->select([
						'same' => '一致',
						'different' => '不一致',
					]);
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

        $form->text('master_sku_id', __('关联主商品ID'));
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
		$form->text('deleted_at', '出库时间');
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
