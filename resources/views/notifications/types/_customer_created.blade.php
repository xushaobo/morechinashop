<li class="media @if ( ! $loop->last) border-bottom @endif">
  <div class="media-left">
    <a href="{{ route('customers.index', $notification->data['customer_message']) }}">
      <img class="media-object img-thumbnail mr-3" alt="{{ $notification->data['customer_message'] }}" src="{{ $notification->data['customer_message'] }}" style="width:48px;height:48px;" />
    </a>
  </div>

  <div class="media-body">
    <div class="media-heading mt-0 mb-1 text-secondary">
      <a href="{{ route('customers.index', $notification->data['customer_message']) }}">{{ $notification->data['customer_message'] }}</a>
      评论了
      <a href="{{ $notification->data['customer_message'] }}">{{ $notification->data['customer_message'] }}</a>

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
