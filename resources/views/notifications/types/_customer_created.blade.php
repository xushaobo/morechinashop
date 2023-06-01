<li class="media @if ( ! $loop->last) border-bottom @endif">

  <div class="media-body">
    <div class="media-heading mt-0 mb-1 text-secondary">
      <h5>
      <a href="{{ route('customers.index', $notification->data['customer_message']) }}">{{ $notification->data['customer_message'] }}</br>待联系</a> </h5>

      {{-- 回复删除按钮 --}}
      <span class="meta float-right" title="{{ $notification->created_at }}">
        <i class="far fa-clock"></i>
        {{ $notification->created_at->diffForHumans() }}
      </span>
    </div>
    <div class="reply-content">
      {!! $notification->data['customer_message'] !!}
    </div>
  </div>
</li>
