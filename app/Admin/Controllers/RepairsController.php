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
        $grid->column('repair_date', __('返修日期'));
        $grid->column('remark', __('单位'));
        $grid->column('customer_name', __('客户名称'));
        $grid->column('phone', __('联系电话'));
        $grid->column('seller', __('销售员'));
        $grid->column('brand', __('品牌'));
        $grid->column('type', __('型号'));
        $grid->column('serial_num', __('序列号'));
        $grid->column('bad_description', __('故障描述'));
        $grid->ifunderwarry('是否在保');
        $grid->column('howtodo', __('处理办法'));
        $grid->column('des_add', __('返修状态'));
        $grid->column('mail_info', __('邮寄状态'));
        $grid->column('ifreturntoBJ', __('是否返厂'));

        return $grid;
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(Repair::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('repair_date', __('Repair date'));
        $show->field('customer_name', __('Customer name'));
        $show->field('phone', __('Phone'));
        $show->field('seller', __('Seller'));
        $show->field('brand', __('Brand'));
        $show->field('type', __('Type'));
        $show->field('serial_num', __('Serial num'));
        $show->field('bad_description', __('Bad description'));
        $show->field('ifunderwarry', __('Ifunderwarry'));
        $show->field('howtodo', __('Howtodo'));
        $show->field('des_add', __('Des add'));
        $show->field('finfish_date', __('Finfish date'));
        $show->field('mail_info', __('Mail info'));
        $show->field('ifreturntoBJ', __('IfreturntoBJ'));
        $show->field('howtodo_BJ', __('Howtodo BJ'));
        $show->field('returnBJ_date', __('ReturnBJ date'));
        $show->field('remark', __('Remark'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Repair());

        $form->date('repair_date', __('Repair date'))->default(date('Y-m-d'));
        $form->text('customer_name', __('Customer name'));
        $form->mobile('phone', __('Phone'));
        $form->text('seller', __('Seller'));
        $form->text('brand', __('Brand'));
        $form->text('type', __('Type'));
        $form->text('serial_num', __('Serial num'));
        $form->text('bad_description', __('Bad description'));
        $form->switch('ifunderwarry', __('Ifunderwarry'));
        $form->textarea('howtodo', __('Howtodo'));
        $form->textarea('des_add', __('Des add'));
        $form->date('finfish_date', __('Finfish date'))->default(date('Y-m-d'));
        $form->text('mail_info', __('Mail info'));
        $form->switch('ifreturntoBJ', __('IfreturntoBJ'));
        $form->textarea('howtodo_BJ', __('Howtodo BJ'));
        $form->date('returnBJ_date', __('ReturnBJ date'))->default(date('Y-m-d'));
        $form->textarea('remark', __('Remark'));

        return $form;
    }
}
