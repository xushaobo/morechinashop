<?php

namespace App\Admin\Extensions\Tools;

use Encore\Admin\Grid\Tools\BatchAction;

class BatchUpdateCost extends BatchAction
{
    protected $action = 'batch_update_cost';

    public function script()
    {
        $url = route('admin.api.batch.update.cost');
        $class = $this->getElementClass();

        return <<<EOT
$('{$class}').off('click').on('click', function() {
    var ids = $.admin.grid.selected();
    if (ids.length === 0) {
        $.admin.toastr.warning('请至少选择一条记录');
        return;
    }
    var newCost = prompt('请输入新的成本价', '');
    if (newCost === null || newCost.trim() === '') {
        $.admin.toastr.warning('成本价不能为空');
        return;
    }
    // cost 字段是 varchar，无需数字验证
    $.ajax({
        method: 'post',
        url: '{$url}',
        data: {
            _token: $.admin.token,
            ids: ids,
            cost: newCost
        },
        success: function (data) {
            if (data.status) {
                $.admin.toastr.success(data.message);
                $.admin.reload();
            } else {
                $.admin.toastr.error(data.message);
            }
        }
    });
});
EOT;
    }
}
