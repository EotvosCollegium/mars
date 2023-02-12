@can('view', $user)
    {{-- Personal information --}}
    <ul class="collapsible">
        <li>
            <div class="collapsible-header"><b>@lang('user.personal_information')</b></div>
            <div class="collapsible-body">
                @include('user.personal-information', ['user' => $user])
            </div>
        </li>
    </ul>
    {{-- Educational information --}}
    <ul class="collapsible">
        <li>
            <div class="collapsible-header"><b>@lang('user.educational_information')</b></div>
            <div class="collapsible-body">
                @include('user.educational-information', ['user' => $user])
            </div>
        </li>
    </ul>
    {{-- Roles --}}
    <ul class="collapsible">
        <li>
            <div class="collapsible-header"><b>@lang('role.roles')</b></div>
            <div class="collapsible-body">
                @include('user.roles', ['user' => $user])
            </div>
        </li>
    </ul>
    {{-- Statuses --}}
    @if($user->isCollegist())
        <ul class="collapsible">
            <li>
                <div class="collapsible-header"><b>Státuszok</b></div>
                <div class="collapsible-body">
                    @include('user.statuses', ['user' => $user])
                </div>
            </li>
        </ul>
    @endif
@endcan
{{-- Internet --}}
<ul class="collapsible">
    <li>
        <div class="collapsible-header"><b>@lang('internet.internet')</b></div>
        <div class="collapsible-body">
            @include('user.internet', ['user' => $user])
        </div>
    </li>
</ul>
{{-- Printing --}}
<ul class="collapsible">
    <li>
        <div class="collapsible-header"><b>@lang('print.print')</b></div>
        <div class="collapsible-body">
            @include('user.printing', ['user' => $user])
        </div>
    </li>
</ul>
@if(auth()->user()->id == $user->id)
    {{-- Change Password--}}
    <ul class="collapsible">
        <li>
            <div class="collapsible-header"><b>@lang('general.change_password')</b></div>
            <div class="collapsible-body">
                <form method="POST" action="{{ route('users.update.password', ['user' => $user]) }}">
                    @csrf
                    <div class="row">
                        <x-input.text id='old_password' text="registration.old_password" type='password' required
                                      autocomplete="password"/>
                        <x-input.text s=6 id='new_password' text="registration.new_password" type='password' required
                                      autocomplete="new-password"/>
                        <x-input.text s=6 id='confirmpwd' text="registration.confirmpwd" name="new_password_confirmation"
                                      type='password' required autocomplete="new-password"/>
                        <x-input.button class="right" text="general.change_password"/>
                    </div>
                </form>
            </div>
        </li>
    </ul>
    <form action="{{ route('logout') }}" method="POST">
        @csrf
        <x-input.button class="right" text="general.logout"/>
    </form>
@endif
