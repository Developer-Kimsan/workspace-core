<li class="nav-item dropdown pr-4 d-flex">
  <a href="nav-link">
    <p class="text-danger position-absolute pl-3">20</p>
    <i class="mr-3 mt-2 lar la-envelope h4"></i>
  </a>
  <a class="nav-link" href="">
    <p class="text-danger pl-3 position-absolute">20</p></div>
    <i class="mr-1 mt-2 la la-bell-o h4"></i>
  </a>
  <a class="nav-link" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">
    @if (backpack_auth()->user()->image == null)
    <img class="img-avatar" src="{{ backpack_avatar_url(backpack_auth()->user()) }}" alt="{{ backpack_auth()->user()->name }}">
    @else
    <img class="img-avatar" src="{{ asset(backpack_auth()->user()->image) }}" alt="{{ backpack_auth()->user()->name }}">
    @endif
  </a>
  <div class="dropdown-menu {{ config('backpack.base.html_direction') == 'rtl' ? 'dropdown-menu-left' : 'dropdown-menu-right' }} mr-4 pb-1 pt-1">
    <a class="dropdown-item" href="{{ route('backpack.account.info') }}"><i class="la la-user h5"></i> {{ trans('backpack::base.my_account') }}</a>
    @if (backpack_auth()->user()->role == '1')
    <a class='dropdown-item' href='{{ backpack_url('user') }}'><i class="las la-user-friends h5"></i> Users</a>
    <a class='dropdown-item' href='{{ backpack_url('customer') }}'><i class="las la-people-carry h5"></i> Customers</a>
    @endif
    <div class="dropdown-divider"></div>
    <a class="dropdown-item" href="{{ backpack_url('logout') }}"><i class="la la-lock h5"></i> {{ trans('backpack::base.logout') }}</a>
  </div>
</li>
