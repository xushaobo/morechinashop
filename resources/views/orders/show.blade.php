@extends('layouts.app')
@section('title', '查看订单')

@section('content')
<div class="row">
<div class="col-lg-10 offset-lg-1">
<div class="card">
  <div class="card-header">
    <h4>订单详情</h4>
  </div>
  <div class="card-body">
    <table class="table">
      <thead>
      <tr>
        <th>商品信息</th>
        <th class="text-center">单价</th>
        <th class="text-center">数量</th>
        <th class="text-right item-amount">小计</th>
      </tr>
      </thead>
      @foreach($order->items as $index => $item)
        <tr>
          <td class="product-info">
            <div class="preview">
              <a target="_blank" href="{{ route('products.show', [$item->product_id]) }}">
                <img src="{{ $item->product->image_url }}">
              </a>
            </div>
            <div>
              <span class="product-title">
                 <a target="_blank" href="{{ route('products.show', [$item->product_id]) }}">{{ $item->product->title }}</a>
              </span>
              <span class="sku-title">{{ $item->productSku->title }}</span>
            </div>
          </td>
          <td class="sku-price text-center vertical-middle">￥{{ $item->price }}</td>
          <td class="sku-amount text-center vertical-middle">{{ $item->amount }}</td>
          <td class="item-amount text-right vertical-middle">￥{{ number_format($item->price * $item->amount, 2, '.', '') }}</td>
        </tr>
      @endforeach
      <tr><td colspan="4"></td></tr>
    </table>
    <div class="order-bottom">
      <div class="order-info">
        <div class="line"><div class="line-label">收货地址：</div><div class="line-value">{{ join(' ', $order->address) }}</div></div>
        <div class="line"><div class="line-label">订单备注：</div><div class="line-value">{{ $order->remark ?: '-' }}</div></div>
        <div class="line"><div class="line-label">订单编号：</div><div class="line-value">{{ $order->no }}</div></div>
        <!-- 如果有物流信息则展示 -->
        @if($order->ship_data)
        @endif
        <!-- 审核状态不是未退款时展示信息 -->
        @if($order->refund_status !== \App\Models\Order::REFUND_STATUS_PENDING)
          <div class="line">
            <div class="line-label">审批状态： </div>
            <div class="line-value">{{ \App\Models\Order::$refundStatusMap[$order->refund_status] }}</div>
          </div>
          <div class="line">
            <div class="line-label">审批理由： </div>
            <div class="line-value">{{ $order->extra['refund_reason'] }}</div>
          </div>
        @endif
        <!-- 审核状态是未退款时展示申请审批按钮-->
        @if($order->refund_status === \App\Models\Order::REFUND_STATUS_PENDING)
        <div class="refund-button">
          <button class="btn btn-sm btn-danger" id="btn-apply-refund">申请审批</button>
        </div>
        @endif
      </div>
      <div class="order-summary text-right">
        <div class="total-amount">
          <span>订单总价：</span>
          <div class="value">￥{{ $order->total_amount }}</div>
        </div>
        <div>
          <span>订单状态：</span>
          <div class="value">
            @if($order->paid_at)
              @if($order->refund_status === \App\Models\Order::REFUND_STATUS_PENDING)
                已支付
              @else
                {{ \App\Models\Order::$refundStatusMap[$order->refund_status] }}
              @endif
            @elseif($order->closed)
              已关闭
            @else
              未支付
            @endif
          </div>
          @if(isset($order->extra['refund_disagree_reason']))
          <div>
            <span>同意申批理由：</span>
            <div class="value">{{ $order->extra['refund_disagree_reason'] }}</div>
          </div>
          @endif
        </div>
      </div>
    </div>
  </div>
</div>
</div>
</div>
@endsection

@section('scriptsAfterJs')
<script>
  $(document).ready(function () {
    $('#btn-apply-refund').click(function (){
      swal({
        text: '请输入审批理由,是否已付款',
        content: "input",
      }).then(function (input) {
        if(!input) {
          swal('审批理由不可为空','','error');
          return;
        }
        //
        axios.post('{{ route('orders.pay_confirm', [$order->id]) }}', {data: input})
          .then(function (){
            swal('申请审批成功','','success').then(function () {
              location.reload();
            });
          });
      });
    });
  });
</script>
@endsection
