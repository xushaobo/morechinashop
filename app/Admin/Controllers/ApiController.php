<?php

namespace App\Admin\Controllers;

use Encore\Admin\Controllers\AdminController;
use Illuminate\Http\Request;
use App\Models\SerialNum;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ApiController extends AdminController
{
    public function batchUpdateSerialNum(Request $request)
    {
        $ids = $request->input('ids');
        $serialNum = $request->input('serial_num');

        if (empty($ids) || empty($serialNum)) {
            return response()->json(['status' => false, 'message' => '参数错误']);
        }

        try {
            DB::transaction(function () use ($ids, $serialNum) {
                SerialNum::whereIn('id', $ids)->update(['serial_num' => $serialNum]);
            });
            return response()->json(['status' => true, 'message' => '批量更新成功']);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => '更新失败：' . $e->getMessage()]);
        }
    }



	public function batchUpdateCost(Request $request)
{
    Log::info('Batch update cost request', $request->all());

    try {
        $ids = $request->input('ids');
        $cost = $request->input('cost');

        if (empty($ids) || $cost === null || trim($cost) === '') {
            return response()->json(['status' => false, 'message' => '参数错误']);
        }

        if (!is_array($ids)) {
            $ids = explode(',', $ids);
        }

        DB::beginTransaction();

        foreach ($ids as $id) {
            $record = SerialNum::withTrashed()->find($id);
            if ($record) {
                $record->cost = $cost; // 直接赋值为字符串
                $record->save();
            }
        }

        DB::commit();

        return response()->json(['status' => true, 'message' => '成本更新成功']);
    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Batch update cost failed: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
        return response()->json(['status' => false, 'message' => '更新失败：' . $e->getMessage()]);
    }
}

public function batchUpdateDeletedAt(Request $request)
{
    Log::info('Batch update deleted_at request', $request->all());

    try {
        $ids = $request->input('ids');
        $deletedAt = $request->input('deleted_at');

        if (empty($ids)) {
            return response()->json(['status' => false, 'message' => '参数错误：未选择记录']);
        }

        if (!is_array($ids)) {
            $ids = explode(',', $ids);
        }

        // 处理时间值
        if ($deletedAt === 'NULL') {
            $value = null; // 清空
        } else {
            // 验证时间格式（简单验证）
            if (!strtotime($deletedAt)) {
                return response()->json(['status' => false, 'message' => '时间格式无效']);
            }
            $value = $deletedAt;
        }

        DB::beginTransaction();

        foreach ($ids as $id) {
            $record = SerialNum::withTrashed()->find($id);
            if ($record) {
                $record->deleted_at = $value;
                $record->save();
            }
        }

        DB::commit();

        return response()->json(['status' => true, 'message' => '删除时间更新成功']);
    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Batch update deleted_at failed: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
        return response()->json(['status' => false, 'message' => '更新失败：' . $e->getMessage()]);
    }
}
    
}
