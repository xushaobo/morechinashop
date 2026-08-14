<?php

namespace App\Admin\Extensions\Tools;

use Encore\Admin\Grid\Tools\BatchAction;

class BatchUpdateDeletedAt extends BatchAction
{
    protected $action = 'batch_update_deleted_at';

    public function script()
    {
        $url = route('admin.api.batch.update.deleted_at'); // 新路由
        $class = $this->getElementClass();

        return <<<EOT
$(document).on('click', '{$class}', function() {
    var ids = $.admin.grid.selected();
    if (ids.length === 0) {
        $.admin.toastr.warning('请至少选择一条记录');
        return;
    }

    // 弹出选项：设置时间 或 清空
    var action = prompt('输入 1 设置为当前时间，输入 2 清空，直接输入时间格式如 2026-03-20 15:30:00', '');
    if (action === null) return;

    var datetime = '';
    if (action === '1') {
        // 当前时间（格式：YYYY-MM-DD HH:MM:SS）
        var now = new Date();
        datetime = now.getFullYear() + '-' + 
                   String(now.getMonth() + 1).padStart(2, '0') + '-' + 
                   String(now.getDate()).padStart(2, '0') + ' ' + 
                   String(now.getHours()).padStart(2, '0') + ':' + 
                   String(now.getMinutes()).padStart(2, '0') + ':' + 
                   String(now.getSeconds()).padStart(2, '0');
    } else if (action === '2') {
        datetime = 'NULL'; // 特殊标识，表示清空
    } else {
        datetime = action; // 直接使用用户输入的时间字符串
    }

    $.ajax({
        method: 'post',
        url: '{$url}',
        data: {
            _token: $.admin.token,
            ids: ids,
            deleted_at: datetime
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
