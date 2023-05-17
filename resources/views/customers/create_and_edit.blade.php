@extends('layouts.app')
@section('title', '新增待联系客户')

@section('content')
<div class="row">
<div class="col-md-10 offset-lg-1">
<div class="card">
  <div class="card-header">
    <h2 class="text-center">
      {{ $customer->id ? '修改':'新增'}}待联系客户
    </h2>
  </div>
  <div class="card-body">
    <!-- 输出后端报错开始 -->
    @if (count($errors) > 0)
      <div class="alert alert-danger">
        <h4>有错误发生：</h4>
        <ul>
          @foreach ($errors->all() as $error)
            <li><i class="glyphicon glyphicon-remove"></i> {{ $error }}</li>
          @endforeach
        </ul>
      </div>
    @endif
    <!-- 输出后端报错结束 -->
      @if($customer->id)
    <form class="form-horizontal" role="form" action="{{ route('customers.update', ['customer' => $customer->id]) }}" method="post">
      {{ method_field('PUT') }}
      @else
	<form class="form-horizontal" role="form" action="{{ route('customers.store') }}" method="post">
      @endif
        <!-- 引入 csrf token 字段 -->
      {{ csrf_field() }}
      <!-- 注意这里多了 @change -->

        <div class="form-group row">
          <label class="col-form-label text-md-right col-sm-2">公司名称</label>
          <div class="col-sm-9">
            <input type="text" class="form-control" name="customer_name" value="{{ old('customer_name', $customer->customer_name) }}">
          </div>
        </div>
        <div class="form-group row">
          <label class="col-form-label text-md-right col-sm-2">联系人姓名</label>
          <div class="col-sm-9">
            <input type="text" class="form-control" name="contact_name" value="{{ old('contact_name', $customer->contact_name) }}">
          </div>
        </div>
        <div class="form-group row">
          <label class="col-form-label text-md-right col-sm-2">电话</label>
          <div class="col-sm-9">
            <input type="text" class="form-control" name="contact_phone" value="{{ old('contact_phone', $customer->contact_phone) }}">
          </div>
        </div>
        <div class="form-group row">
          <label class="col-form-label text-md-right col-sm-2">备注</label>
          <div class="col-sm-9">
            <input type="text" class="form-control" name="memo" value="{{ old('memo', $customer->memo) }}">
          </div>
        </div>
        <div class="form-group row text-center">
          <div class="col-12">
            <button type="submit" class="btn btn-primary">提交</button>
          </div>
        </div>
      </form>
  </div>
</div>
</div>
</div>
@endsection
