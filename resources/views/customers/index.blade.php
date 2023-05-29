@extends('layouts.app')
@section('title', '待联系客户列表')

@section('content')
  <div class="row">
    <div class="col-md-10 offset-md-1">
      <div class="card panel-default">
        <div class="card-header">待联系客户列表
	<a href="{{ route('customers.create') }}" class="float-right">新增待联系客户</a>
       </div>
        <div class="card-body">
          <table class="table table-bordered table-striped">
            <thead>
            <tr>
              <th>客户公司名称</th>
              <th>客户联系人</th>
              <th>联系人电话</th>
              <th>备注</th>
              <th>下一次提醒时间</th>
            </tr>
            </thead>
            <tbody>
            @foreach($customers as $customer)
              <tr>
                <td>{{ $customer->customer_name }}</td>
                <td>{{ $customer->contact_name }}</td>
                <td>{{ $customer->contact_phone }}</td>
                <td>{{ $customer->memo }}</td>
                <td>{{ $customer->last_used_at }}</td>
                <td>
		  <a href="{{ route('customers.edit', ['customer' => $customer->id]) }}" class="btn btn-primary">修改</a>

		<!-- 把之前删除按钮的表单替换成这个按钮，data-id 属性保存了这个地址的 id，在 js 里会用到 -->
<button class="btn btn-danger btn-del-address" type="button" data-id="{{ $customer->id }}">删除</button>

                </td>
              </tr>
            @endforeach
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
@endsection


@section('scriptsAfterJs')
<script>
$(document).ready(function() {
  // 删除按钮点击事件
  $('.btn-del-address').click(function() {
    // 获取按钮上 data-id 属性的值，也就是地址 ID
    var id = $(this).data('id');
    // 调用 sweetalert
    swal({
        title: "确认要删除该待联系客户？",
        icon: "warning",
        buttons: ['取消', '确定'],
        dangerMode: true,
      })
    .then(function(willDelete) { // 用户点击按钮后会触发这个回调函数
      // 用户点击确定 willDelete 值为 true， 否则为 false
      // 用户点了取消，啥也不做
      if (!willDelete) {
        return;
      }
      // 调用删除接口，用 id 来拼接出请求的 url
      axios.delete('/customers/' + id)
        .then(function () {
          // 请求成功之后重新加载页面
          location.reload();
        })
    });
  });
});
</script>
@endsection
