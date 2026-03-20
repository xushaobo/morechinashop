<?php

namespace App\Admin\Extensions\Tools;

use Encore\Admin\Grid\Tools\BatchAction;

class BatchUpdateSerialNum extends BatchAction
{
    protected $action = 'batch_update_serial_num';

    public function script()
    {
        $url = route('admin.api.batch.update.serial_num'); // 后端路由

        return <<<EOT

$('{$this->getElementClass()}').on('click', function() {
    var ids = $.admin.grid.selected();
    if (ids.length === 0) {
        $.admin.toastr.warning('请至少选择一条记录');
        return;
    }
    // 弹出对话框让用户输入新的序列号
    var newSerial = prompt('请输入新的序列号', '');
    if (newSerial === null || newSerial.trim() === '') {
        $.admin.toastr.warning('序列号不能为空');
        return;
    }
    $.ajax({
        method: 'post',
        url: '{$url}',
        data: {
            _token: $.admin.token,
            ids: ids,
            serial_num: newSerial
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
