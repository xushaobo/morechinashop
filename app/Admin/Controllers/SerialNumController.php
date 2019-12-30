<?php

namespace App\Admin\Controllers;

use App\Models\Category;
use App\Models\serialNum;
use App\Models\Product;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;

class SerialNumController extends Controller
{
	use HasResourceActions;

	public function index(Content $content)
	{
		return $content
		 ->header('产品序列号列表')
		 ->body($this->grid());
	}

	public function edit($id, Content $content)
	{
		return $content
		  ->header('编辑序列号')
		  ->body($this->form()->edit($id));
	}

	public function create(Content $content)
	{
		return $content
		 ->header('录入添加产品序列号')
		 ->body($this->form());
	}

		protected function grid()
		{
			$grid = new Grid(new Product);
			//只展示type为 serialNum的商品
			$grid->model()->where('type',Product::TYPE_SERIALNUM);
			$grid->id('ID')->sortable();
			$grid->title('商品名称');
			$grid->on_sale('已上架')->display(function ($value) {
				return $value ? '是' : '否';
			});
			$grid->price('价格');
			// 展示众筹相关字段
			$grid->column('serialnum.serialNum_id', '产品序列号');
			$grid->column('serialnum.orderNum_id', '所属订单号');
	
			$grid->actions(function ($actions) {
				$actions->disableView();
				$actions->disableDelete();
			});
			$grid->tools(function ($tools) {
				$tools->batch(function ($batch) {
					$batch->disableDelete();
				});
			});
	
			return $grid;
		}
	
		protected function form()
		{
			$form = new Form(new Product);
	
			// 在表单中添加一个名为 type，值为 Product::TYPE_CROWDFUNDING 的隐藏字段
			$form->hidden('type')->value(Product::TYPE_SERIALNUM);
			$form->text('title', '商品名称')->default('产品序列号管理');
			$form->select('category_id', '类目')->options(function ($id) {
				$category = Category::find($id);
				if ($category) {
					return [$category->id => $category->full_name];
				}
			})->ajax('/admin/api/categories?is_directory=0');
			$form->image('image', '封面图片')->rules('required|image');
			$form->editor('description', '商品描述')->rules('required');
			$form->radio('on_sale', '上架')->options(['1' => '是', '0' => '否'])->default('0');
			// 添加众筹相关字段
			$form->hasMany('skus', '商品 SKU', function (Form\NestedForm $form) {
				$form->text('title', 'SKU 名称')->rules('required');
				$form->text('description', 'SKU 描述')->rules('required');
				$form->text('price', '单价')->rules('required|numeric|min:0.01');
				$form->text('stock', '剩余库存')->rules('required|integer|min:0');
			});
			$form->saving(function (Form $form) {
				$form->model()->price = collect($form->input('skus'))->where(Form::REMOVE_FLAG_NAME, 0)->min('price');
			});
	
			return $form;
		}
	}
