@guest
    <li><a href="{{ route('login') }}">@lang('general.login')</a></li>
    @if(\App\Models\Feature::isFeatureEnabled("application"))
        <li><a href="{{ route('register') }}">@lang('general.register_collegist')</a></li>
    @endif
    @if(\App\Models\Feature::isFeatureEnabled("guests"))
            <li><a href="{{ route('register.guest') }}">@lang('general.register_guest')</a></li>
    @endif
@else
    <li><a class="waves-effect" href="{{ route('profile') }}"><i class="material-icons left">account_circle</i>{{ user()->name }}</a></li>
@endguest
