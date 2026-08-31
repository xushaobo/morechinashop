<div class="box box-info">
  <div class="box-header with-border">
    <h3 class="box-title">批量录入到货序列号</h3>
    <div class="box-tools">
      <a href="{{ admin_url('serial_num') }}" class="btn btn-sm btn-default"><i class="fa fa-list"></i> 返回列表</a>
    </div>
  </div>
  <form action="{{ route('admin.serial_num.batch_store') }}" method="post" class="form-horizontal">
    {{ csrf_field() }}
    <div class="box-body">
      @if($errors->any())
        <div class="alert alert-danger">
          @foreach($errors->all() as $error)
            <div>{{ $error }}</div>
          @endforeach
        </div>
      @endif

      <div class="form-group {{ $errors->has('productSku_id') ? 'has-error' : '' }}">
        <label for="productSku_id" class="col-sm-2 control-label">商品</label>
        <div class="col-sm-6">
          <select id="productSku_id" name="productSku_id" class="form-control" required>
            <option value="">请选择商品</option>
            @foreach($skuOptions as $skuId => $skuLabel)
              <option value="{{ $skuId }}" {{ (string) old('productSku_id') === (string) $skuId ? 'selected' : '' }}>{{ $skuLabel }}</option>
            @endforeach
          </select>
        </div>
      </div>

      <div class="form-group {{ $errors->has('created_at') ? 'has-error' : '' }}">
        <label for="created_at" class="col-sm-2 control-label">到货日期</label>
        <div class="col-sm-6">
          <input type="date" id="created_at" name="created_at" value="{{ old('created_at', date('Y-m-d')) }}" class="form-control" required>
        </div>
      </div>

      <div class="form-group {{ $errors->has('ship_num') ? 'has-error' : '' }}">
        <label for="ship_num" class="col-sm-2 control-label">到货批次号</label>
        <div class="col-sm-6">
          <input type="text" id="ship_num" name="ship_num" value="{{ old('ship_num') }}" class="form-control" required>
        </div>
      </div>

      <div class="form-group {{ $errors->has('cost') ? 'has-error' : '' }}">
        <label for="cost" class="col-sm-2 control-label">成本</label>
        <div class="col-sm-6">
          <input type="text" id="cost" name="cost" value="{{ old('cost', '0') }}" class="form-control" required>
        </div>
      </div>

      <div class="form-group {{ $errors->has('quantity') ? 'has-error' : '' }}">
        <label for="quantity" class="col-sm-2 control-label">数量</label>
        <div class="col-sm-6">
          <input type="number" id="quantity" name="quantity" value="{{ old('quantity', 1) }}" min="1" class="form-control" required>
        </div>
      </div>

      <div class="form-group {{ $errors->has('serial_nums') ? 'has-error' : '' }}">
        <label for="serial_nums" class="col-sm-2 control-label">序列号</label>
        <div class="col-sm-6">
          <textarea id="serial_nums" name="serial_nums" rows="12" class="form-control" required placeholder="只填一个序列号时，会按数量重复录入；多个序列号可每行一个，也可以用空格、逗号分隔">{{ old('serial_nums') }}</textarea>
        </div>
      </div>
    </div>
    <div class="box-footer">
      <div class="col-sm-offset-2 col-sm-6">
        <button type="submit" class="btn btn-success"><i class="fa fa-save"></i> 提交</button>
      </div>
    </div>
  </form>
</div>

<script>
$(document).ready(function() {
  if ($.fn.select2) {
    $('#productSku_id').select2({
      width: '100%',
      placeholder: '搜索ID、货号或描述'
    });
  }
});
</script>
