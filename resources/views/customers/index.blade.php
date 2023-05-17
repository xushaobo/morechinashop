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
            </tr>
            </thead>
            <tbody>
            @foreach($customers as $customer)
              <tr>
                <td>{{ $customer->customer_name }}</td>
                <td>{{ $customer->contact_name }}</td>
                <td>{{ $customer->contact_phone }}</td>
                <td>{{ $customer->memo }}</td>
                <td>
		  <a href="{{ route('customers.edit', ['customer' => $customer->id]) }}" class="btn btn-primary">修改</a>
                  <button class="btn btn-danger">删除</button>
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

