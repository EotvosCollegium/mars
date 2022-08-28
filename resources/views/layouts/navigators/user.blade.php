@guest
    <li><a href="{{ route('login') }}">@lang('general.login')</a></li>
    <li><a href="{{ route('register') }}">@lang('general.register_collegist')</a></li>
    <li><a href="{{ route('register.guest') }}">@lang('general.register_guest')</a></li>
@else
    <li><a class="waves-effect" href="{{ route('profile') }}"><i class="material-icons left">account_circle</i>{{ Auth::user()->name }}</a></li>
@endguest
