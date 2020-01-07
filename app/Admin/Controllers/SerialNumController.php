<?php

namespace App\Admin\Controllers;

use App\Models\Category;
use App\Models\SerialNum;
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
			$grid = new Grid(new SerialNum);
			$grid->id('ID')->sortable();
			$grid->product_id('产品货号')->sortable();
			$grid->serialNum_id('序列号')->sortable();
			$grid->orderNum_id('所属订单号')->sortable()->editable();

	
			$grid->actions(function ($actions) {
				$actions->disableView();
				$actions->disableDelete();
			});
			$grid->tools(function ($tools) {
				$tools->batch(function ($batch) {
					$batch->disableDelete();
				});
			});

			$grid->filter(function($filter){
				$filter->disableIdFilter();
	
				$filter->like('product_id','产品货号');
				$filter->like('serialNum_id','序列号');
				$filter->like('orderNum_id','所属订单号');
			});
			return $grid;
		}
	
		protected function form()
		{
			$form = new Form(new SerialNum);
	
			// 在表单中添加一个名为 type，值为 Product::TYPE_CROWDFUNDING 的隐藏字段
			$form->text('product_id', '产品货号')->rules('required')->default('201301');
			$form->text('serialNum_id', '序列号')->rules('required')->default('1932');
			$form->text('orderNum_id','所属订货号')->default('not save');
	
			return $form;
		}
	}
