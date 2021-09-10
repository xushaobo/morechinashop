<?php

namespace App\Admin\Controllers;

use App\Models\Repair;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class RepairsController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '上海牧晨返修品';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Repair());

        $grid->column('id', __('序号'))->sortable();
        $grid->column('repair_date', __('返修日期'))->sortable();
        $grid->column('remark', __('客户名称'))->width(200);
        $grid->column('customer_name', __('联系人'))->width(80);
        $grid->column('phone', __('联系电话'));
        $grid->column('seller', __('销售员'))->width(80);
        $grid->column('brand', __('品牌'));
        $grid->column('type', __('型号'));
        $grid->column('serial_num', __('序列号'))->width(150);
        $grid->column('bad_description', __('故障描述'))->width(150);
        $grid->column('ifunderwarry',('是否在保'))->replace([1=>'是',0=>'否'])->sortable()->width(50);
        $grid->column('ifreturntoBJ',('是否返厂'))->replace([1=>'是',0=>'否'])->sortable()->width(50);
        $grid->column('howtodo', __('处理办法'))->width(150);

        $grid->column('main_info2',('已邮寄客户'))->width(70);

	$grid->column('des_add',('返修状态'))->using([
    0 => '进行中',
    1 => '已修复',
    2 => '申保中',
], '未知')->dot([
    0 => 'danger',
    1 => 'success',
    2 => 'primary',
], 'warning')->sortable()->width(100);

$grid->filter(function($filter){
                                $filter->disableIdFilter();

                                $filter->like('remark','客户名称');
                                $filter->like('serial_num','序列号');
                                $filter->like('type','型号');
				$filter->like('des_add','返修状态');
                        });

$grid->actions(function ($actions) {
                                $actions->disableView();
                                $actions->disableDelete();
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
        $form = new Form(new Repair());

        $form->date('repair_date', __('返修日期'))->default(date('Y-m-d'));
        $form->text('remark', __('客户名称'));
        $form->text('customer_name', __('联系人'));
        $form->mobile('phone', __('联系电话'));
        $form->text('seller', __('销售员'));
        $form->text('brand', __('品牌'));
        $form->text('type', __('型号'));
        $form->text('serial_num', __('序列号'));
        $form->text('bad_description', __('故障描述'));
	$form->radio('ifunderwarry', '是否在保')->options(['1' => '是', '0'=> '否'])->default('0');
        $form->textarea('howtodo', __('处理办法'));
	$form->radio('des_add', '维修状态')->options(['1' => '已修复', '0'=> '进行中','2'=> '申保中'])->default('0');
	$form->radio('ifreturntoBJ', '是否返厂')->options(['1' => '是', '0'=> '否'])->default('0');
        $form->text('mail_info', __('邮寄状态'));
        $form->textarea('howtodo_BJ', __('厂家处理办法'));
        $form->date('finfish_date', __('申保返回时间'))->default(date('Y-m-d'));
        $form->date('returnBJ_date', __('寄回客户时间'))->default(date('Y-m-d'));
        $form->text('main_info2', __('寄回客户状态/单号'))->default('0');
        $form->text('pay', __('收费'))->default('无');
        return $form;
    }
}
