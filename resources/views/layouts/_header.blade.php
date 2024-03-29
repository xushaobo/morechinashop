<nav class="navbar navbar-expand-lg navbar-light bg-light navbar-static-top">
  <div class="container">
	  <!-- Branding Image -->
	  <a class="navbar-brand" href="{{ url('/') }}">
		上海牧晨电子管理后台
	  </a>
	  <a class="navbar-brand" href="{{ url('/admin/repairs') }}">
		WTW返修表	
	  </a>
	  <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarSupportedContent">
      <!-- Left Side Of Navbar -->
      <ul class="navbar-nav mr-auto">
 <!-- 顶部类目菜单开始 -->
      <!-- 判断模板是否有 $categoryTree 变量 -->
      @if(isset($categoryTree))
        <li class="nav-item dropdown">
          <a href="#" class="nav-link dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" id="categoryTree">所有类目 <b class="caret"></b></a>
          <ul class="dropdown-menu" aria-labelledby="categoryTree">
            <!-- 遍历 $categoryTree 集合，将集合中的每一项以 $category 变量注入 layouts._category_item 模板中并渲染 -->
            @each('layouts._category_item', $categoryTree, 'category')
          </ul>
        </li>
      @endif
      <!-- 顶部类目菜单结束 -->
      </ul>

      <!-- Right Side Of Navbar -->
      <ul class="navbar-nav navbar-right">
      <ul class="navbar-nav navbar-right">
        <!-- 登录注册链接开始 -->
        @guest
        <li class="nav-item"><a class="nav-link" href="{{ route('login') }}">登录</a></li>
        <li class="nav-item"><a class="nav-link" href="{{ route('register') }}">注册</a></li>
        @else
		<li class="nav-item">
		<a class="nav-link mt-1" href="{{ route('cart.index') }}"><i class="fa fa-shopping-cart"></i></a>
	  </li>


        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                         <img src="https://manbofish.morechina.com/WechatIMG71.jpeg?imageView2/1/w/60/h/60" class="img-responsive img-circle" width="30px" height="30px">
            {{ Auth::user()->name }}
          </a>
          <div class="dropdown-menu" aria-labelledby="navbarDropdown">
            <a href="{{ route('products.favorites')}}" class="dropdown-item">我的收藏</a>
<a href="{{ route('user_addresses.index') }}" class="dropdown-item">收货地址</a>
<a href="{{ route('customers.index') }}" class="dropdown-item">联系客户</a>
<a href="{{ route('orders.index') }}" class="dropdown-item">我的订单</a>
            <a class="dropdown-item" id="logout" href="#"
               onclick="event.preventDefault();document.getElementById('logout-form').submit();">退出登录</a>
            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
              {{ csrf_field() }}
            </form>
          </div>
        </li>

        <!-- 消息提醒开始 -->
	  <li class="nav-item notification-badge">
            <a class="nav-link mr-3 badge badge-pill badge-{{ Auth::user()->notification_count > 0 ? 'hint' : 'secondary' }} text-white" href="{{ route('notifications.index') }}">
              {{ Auth::user()->notification_count }}
            </a>
          </li>
        <!-- 消息提醒结束 -->

        @endguest
        <!-- 登录注册链接结束 -->
      </ul>
    </div>
  </div>
</nav>
