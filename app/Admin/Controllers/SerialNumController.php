<?php

namespace App\Admin\Controllers;

use App\Models\Category;
use App\Models\ProductSku;
use App\Models\Product;
use App\Models\SerialNum;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use App\Admin\Actions\Post\Restore;
use App\Admin\Extensions\Tools\BatchUpdateSerialNum;
use App\Admin\Extensions\Tools\BatchUpdateCost;
use App\Admin\Extensions\Tools\BatchUpdateDeletedAt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SerialNumController extends Controller
{
	use HasResourceActions;

	public function index(Content $content)
	{
		return $content
		 ->header('WTW产品序列号列表')
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

		public function batchCreate(Content $content)
		{
			return $content
			 ->header('批量录入到货产品序列号')
			 ->body(view('admin.serial_nums.batch_create', [
				'skuOptions' => $this->skuOptions(),
			 ]));
		}

		public function batchStore(Request $request)
		{
			$data = $this->validate($request, [
				'productSku_id' => ['required', 'integer', 'exists:product_skus,id'],
				'ship_num' => ['required', 'string'],
				'cost' => ['required'],
				'created_at' => ['required', 'date'],
				'quantity' => ['required', 'integer', 'min:1'],
				'serial_nums' => ['required', 'string'],
			], [], [
				'productSku_id' => '商品ID',
				'ship_num' => '到货批次号',
				'cost' => '成本',
				'created_at' => '到货日期',
				'quantity' => '数量',
				'serial_nums' => '序列号',
			]);

			$inputSerialNums = $this->normalizeBatchSerialNums($data['serial_nums']);

			if ($inputSerialNums->isEmpty()) {
				return redirect()->back()->withInput()->withErrors(['serial_nums' => '请填写有效序列号']);
			}

			if ($inputSerialNums->count() === 1) {
				$serialNums = collect(array_fill(0, (int) $data['quantity'], $inputSerialNums->first()));
			} else {
				if ($inputSerialNums->count() !== (int) $data['quantity']) {
					return redirect()->back()->withInput()->withErrors([
						'serial_nums' => '填写多个序列号时，序列号数量必须等于数量字段',
					]);
				}

				$serialNums = $inputSerialNums;
			}

			$duplicatedInput = $inputSerialNums->countBy()
				->filter(function ($count) {
					return $count > 1;
				})
				->keys()
				->values();
			if ($duplicatedInput->isNotEmpty()) {
				return redirect()->back()->withInput()->withErrors([
					'serial_nums' => '本次输入有重复序列号：'.$duplicatedInput->implode(', '),
				]);
			}

			$existingSerialNums = SerialNum::withTrashed()
				->whereIn('serial_num', $inputSerialNums->all())
				->pluck('serial_num')
				->unique()
				->values();
			if ($existingSerialNums->isNotEmpty()) {
				return redirect()->back()->withInput()->withErrors([
					'serial_nums' => '系统中已存在序列号：'.$existingSerialNums->implode(', '),
				]);
			}

			DB::transaction(function () use ($data, $serialNums) {
				$sku = ProductSku::where('id', $data['productSku_id'])->lockForUpdate()->firstOrFail();
				$now = now();
				$createdAt = date('Y-m-d H:i:s', strtotime($data['created_at']));

				$rows = $serialNums->map(function ($serialNum) use ($data, $createdAt, $now) {
					return [
						'productSku_id' => (int) $data['productSku_id'],
						'serial_num' => $serialNum,
						'cost' => $data['cost'],
						'ship_num' => $data['ship_num'],
						'created_at' => $createdAt,
						'updated_at' => $now,
					];
				})->all();

				SerialNum::insert($rows);
				$sku->increment('stock', $serialNums->count());
			});

			admin_toastr('成功录入 '.$serialNums->count().' 个序列号，并增加对应库存', 'success');

			return redirect(admin_url('serial_num'));
		}

		protected function grid()
		{
			$grid = new Grid(new SerialNum);
			// 避免N+1问题先查出数据
                        $grid->model()->with(['productSku.product.category'])->orderBy('created_at','desc');
			$grid->id('ID')->sortable();
			$grid->column('productsku.product_id','所属产品id')->sortable();
			//$grid->column('productsku.product.title','所属产品名称');			// 关联表数据
			$grid->column('productsku.product.title','所属产品名称')->display(function () {
				return $this->productSku->product->title;
			});			// 关联表数据
			$grid->column('productsku.title','货号')->sortable();
			$grid->column('productsku.description','描述')->sortable();
			$grid->serial_num('序列号')->sortable();
			$grid->cost('成本')->sortable()->editable()->totalRow(function ($amount) {
			    return "<span class='text-danger text-bold'>总成本： <i class='fa fa-yen'></i>{$amount} 元</span>";
			});
			$grid->created_at('创建日期')->sortable();
			$grid->updated_at('最近更新日期')->sortable();
		        $grid->column('deleted_at', '出库时间')
		        ->editable('datetime')
		        ->display(function ($value) {
			// 直接返回，不进行时间解析
			return $value ?: '未出库';
		        });
			$grid->ship_num('到货批号')->sortable();

	
			$grid->actions(function ($actions) {
                               $actions->disableView();
                                $actions->disableDelete();
                        });

				$grid->tools(function ($tools) {
					$tools->append('<a href="'.admin_url('serial_num/batch-create').'" class="btn btn-sm btn-success"><i class="fa fa-plus"></i> 批量录入到货序列号</a>');
					$tools->batch(function ($batch) {
						$batch->disableDelete();
					});
				});

			$grid->filter(function($filter){
				$filter->disableIdFilter();
	
				$filter->like('productsku.id','ID号');
				$filter->like('productsku.title','货号');
				$filter->like('productsku.description','描述');
				$filter->where(function ($query) {
					$category = Category::find($this->input);
					if (!$category) {
						$query->whereRaw('0 = 1');
						return;
					}

					$categoryIds = [$category->id];
					if ($category->is_directory) {
						$categoryIds = Category::query()
							->where('id', $category->id)
							->orWhere('path', 'like', '%-'.$category->id.'-%')
							->pluck('id')
							->all();
					}

					$query->whereHas('productSku.product', function ($query) use ($categoryIds) {
						$query->whereIn('category_id', $categoryIds);
					});
				}, '类目', 'category_id')->select($this->categoryOptions());
				$filter->like('serial_num','序列号');
				$filter->like('cost','成本');
				$filter->between('created_at','创建时间')->datetime();
				$filter->between('deleted_at','出库时间')->datetime();
				$filter->like('ship_num','到货批号');
				//范围过滤器，调用模型的`onlyTrashed`方法，查询出被软删除的数据。
				$filter->scope('trashed','已出库序列号')->onlyTrashed();
			});
			
			$grid->actions(function ($actions) {
			if (\request('_scope_') == 'trashed') {
			  $actions->add(new Restore());
			}
			});

			  $grid->batchActions(function ($batch) {
			  $batch->add('批量修改序列号', new BatchUpdateSerialNum());
			  $batch->add('批量修改成本', new BatchUpdateCost());
			  $batch->add('批量修改出库时间', new BatchUpdateDeletedAt());
			 });
				return $grid;
			}

			protected function normalizeBatchSerialNums($serialInput)
			{
				return collect(preg_split('/[\s,，、;；\/]+/u', trim((string) $serialInput)))
					->map(function ($serialNum) {
						return trim($serialNum);
					})
					->filter()
					->values();
			}

			protected function skuOptions()
			{
				return ProductSku::query()
					->orderBy('title')
					->orderBy('id')
					->get(['id', 'title', 'description', 'stock'])
					->mapWithKeys(function (ProductSku $sku) {
						$label = implode(' - ', array_filter([
							$sku->id,
							$sku->title,
							$sku->description,
							'库存'.$sku->stock,
						], function ($value) {
							return $value !== null && $value !== '';
						}));

						return [$sku->id => $label];
					})
					->toArray();
			}

		protected function categoryOptions()
		{
			$categories = Category::query()
				->orderBy('path')
				->orderBy('id')
				->get(['id', 'name', 'path']);
			$categoryMap = $categories->keyBy('id');

			return $categories->mapWithKeys(function (Category $category) use ($categoryMap) {
				$name = collect($category->path_ids)
					->map(function ($id) use ($categoryMap) {
						$parent = $categoryMap->get((int) $id);

						return $parent ? $parent->name : null;
					})
					->filter()
					->push($category->name)
					->implode(' - ');

				return [$category->id => $name];
			})->toArray();
		}
	
		protected function form()
		{
			$form = new Form(new SerialNum);
	
			$form->text('productSku_id', '商品ID')->rules('required');
			$form->text('serial_num', '序列号')->rules('required');
			$form->text('created_at', '到货日期')->rules('required');
			$form->text('deleted_at', '出库日期');
			$form->text('ship_num', '到货批次号')->rules('required')->default(date('Y-m-d',strtotime("-0 day")).',xxxxx');
			$form->text('cost', '成本')->rules('required');
			return $form;
		}
	}
