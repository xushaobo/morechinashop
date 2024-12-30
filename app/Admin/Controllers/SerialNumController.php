<?php

namespace App\Admin\Controllers;

use App\Models\Category;
use App\Models\ProductSku;
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
		 ->header('WTW到货记录列表')
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
		 ->header('录入到货产品序列号')
		 ->body($this->form());
	}

		protected function grid()
		{
			$grid = new Grid(new SerialNum);
                        //$grid = model()->with(['productsku']);
			$grid->id('ID')->sortable();
			$grid->productSku_id('sku_id')->sortable();
			$grid->serial_num('序列号')->sortable();
			$grid->created_at('创建日期')->sortable();
			$grid->updated_at('最近更新日期')->sortable();

	
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
	
				$filter->like('pi_num','到货批号');
				$filter->like('sku_num','产品货号');
				$filter->like('serial_num1','主机序列号(后四位)');
				$filter->like('serial_num2','电极附件序列号(后五位)');
				$filter->like('if_sold','是否售完');
				$filter->like('memo','备注');
				$filter->scope('trashed', '已出库')->onlyTrashed();
			});
			return $grid;
		}
	
		protected function form()
		{
			$form = new Form(new ArriveCheck);
	
			// 在表单中添加一个名为 type，值为 Product::TYPE_CROWDFUNDING 的隐藏字段
			$form->date('arrive_date', '到货日期')->rules('required')->default(date('Y-m-d',strtotime("-0 day")));
			$form->text('pi_num', '到货批次号')->rules('required')->default(date('Y-m-d',strtotime("-0 day")).',xxxxx');
			$form->text('sku_num', '产品货号')->rules('required')->default('');
			$form->text('serial_num1', '主机序列号(后四位)')->rules('required')->default('');
			$form->text('serial_num2', '电极附件序列号(后五位)')->rules('required');

	                $form->radio('if_sold', '是否售完')->options(['1' => '是', '0'=> '否'])->default('0');
			$form->text('memo', '备注')->rules('required')->default('无');
	
			return $form;
		}
	}
