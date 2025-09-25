php
<?php

namespace App\Admin\Actions;

use Encore\Admin\Actions\RowAction;
use Illuminate\Database\Eloquent\Model;

class EditOutboundTimeAction extends RowAction
{
    public $name = '编辑出库时间';

    public function handle(Model $model)
    {
        // 这里处理编辑逻辑
        $deletedAt = request('deleted_at');
        
        // 更新出库时间
        $model->update(['deleted_at' => $deletedAt]);
        
        return $this->response()->success('出库时间更新成功')->refresh();
    }

    public function form()
    {
        $this->datetime('deleted_at', '出库时间')->rules('required');
    }
    
    public function dialog()
    {
        $this->confirm('确定要编辑出库时间吗？');
    }
}
