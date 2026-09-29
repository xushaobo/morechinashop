<div class="box box-info">
  <div class="box-header with-border">
    <h3 class="box-title">填写订单明细序列号</h3>
    <div class="box-tools">
      <a href="{{ admin_url('stocks') }}" class="btn btn-sm btn-default"><i class="fa fa-list"></i> 返回库存列表</a>
      <a href="{{ route('admin.orders.show', ['order' => $orderItem->order_id]) }}" class="btn btn-sm btn-default"><i class="fa fa-file-text-o"></i> 查看订单详情</a>
    </div>
  </div>
  <div class="box-body">
    <table class="table table-bordered">
      <tr>
        <th>订单号</th>
        <td>{{ $orderItem->order->no }}</td>
        <th>订单明细 ID</th>
        <td>{{ $orderItem->id }}</td>
      </tr>
      <tr>
        <th>产品</th>
        <td>{{ $orderItem->product->title }}</td>
        <th>product_sku_id</th>
        <td>{{ $orderItem->product_sku_id }}（{{ $orderItem->productSku->title }}）</td>
      </tr>
      <tr>
        <th>数量</th>
        <td>{{ $orderItem->amount }}</td>
        <th>单价</th>
        <td>￥{{ $orderItem->price }}</td>
      </tr>
    </table>

    <form action="{{ route('admin.orderItems.update_serial', ['orderItem' => $orderItem->id]) }}" method="post">
      {{ csrf_field() }}
      <div class="form-group {{ $errors->has('serial_no') ? 'has-error' : '' }}">
        <label for="serial_no">序列号</label>
        <select id="serial_no" name="serial_no[]" class="form-control" multiple="multiple" style="width: 100%" data-placeholder="按 product_sku_id 搜索并选择序列号">
          @foreach($selectedSerialOptions as $serialOption)
            <option value="{{ $serialOption['value'] }}" selected>{{ $serialOption['text'] }}</option>
          @endforeach
        </select>
        @if($errors->has('serial_no'))
          @foreach($errors->get('serial_no') as $message)
            <span class="help-block">{{ $message }}</span>
          @endforeach
        @endif
        <p class="help-block">当前明细最多选择 {{ $orderItem->amount }} 个序列号，保存后订单详情会自动汇总。</p>
      </div>
      <button type="submit" class="btn btn-success"><i class="fa fa-save"></i> 保存序列号</button>
      <a href="{{ admin_url('stocks') }}" class="btn btn-default">取消</a>
    </form>
  </div>
</div>

<script>
$(function () {
  var $serialNo = $('#serial_no');

  $serialNo.select2({
    width: '100%',
    placeholder: '按 product_sku_id 搜索并选择序列号',
    minimumInputLength: 0,
    ajax: {
      url: '{{ route('admin.orderItems.serial_options', ['orderItem' => $orderItem->id]) }}',
      dataType: 'json',
      delay: 200,
      data: function (params) {
        return { q: params.term || '' };
      },
      processResults: function (data) {
        return data;
      },
      cache: true
    }
  });
});
</script>
