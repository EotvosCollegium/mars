@foreach ($errors->all() as $error)
    <blockquote class="error">{{ $error }}</blockquote>
@endforeach
@can('view', $user)
    {{-- Profile picture --}}
    @include('utils.user.profile-picture', ['user' => $user])

    {{-- Personal information --}}
    <ul class="collapsible">
        <li @if(session()->get('section') == "personal_information") class="active" @endif>
            <div class="collapsible-header"><b>@lang('user.personal_information')</b></div>
            <div class="collapsible-body">
                @include('user.personal-information', ['user' => $user])
            </div>
        </li>
    </ul>
    @if($user->hasEducationalInformation())
        {{-- Educational information --}}
        <ul class="collapsible">
            <li @if(session()->get('section') == "educational_information") class="active" @endif>
                <div class="collapsible-header"><b>@lang('user.educational_information')</b></div>
                <div class="collapsible-body">
                    @include('user.educational-information', ['user' => $user])
                </div>
            </li>
        </ul>
        {{-- Alfonso --}}
        <ul class="collapsible">
            <li @if(session()->get('section') == "alfonso") class="active" @endif>
                <div class="collapsible-header"><b>ALFONSÓ</b></div>
                <div class="collapsible-body">
                    @include('user.alfonso', ['user' => $user])
                    <div class="divider"></div>
                    @include('user.alfonso-language-exams', ['user' => $user])
                    <div class="divider"></div>
                    @include('user.alfonso-requirements', ['user' => $user])
                </div>
            </li>
        </ul>
    @endif
    {{-- Roles --}}
    <ul class="collapsible">
        <li @if(session()->get('section') == "roles") class="active" @endif>
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
@can('handle', $user->internetAccess)
    <ul class="collapsible">
        <li>
            <div class="collapsible-header"><b>@lang('internet.internet')</b></div>
            <div class="collapsible-body">
                @include('user.internet', ['user' => $user])
            </div>
        </li>
    </ul>
@endcan
{{-- Printing --}}
<ul class="collapsible">
    <li>
        <div class="collapsible-header"><b>@lang('print.print')</b></div>
        <div class="collapsible-body">
            @include('user.printing', ['user' => $user])
        </div>
    </li>
</ul>
@if(user()->id == $user->id)
    {{-- Change Password--}}
    <ul class="collapsible">
        <li @if(session()->get('section') == "change_password") class="active" @endif>
            <div class="collapsible-header"><b>@lang('general.change_password')</b></div>
            <div class="collapsible-body">
                <form method="POST" action="{{ route('users.update.password', ['user' => $user]) }}">
                    @csrf
                    <div class="row">
                        <x-input.text id='old_password' text="registration.old_password" type='password' required
                                      autocomplete="password"/>
                        <x-input.text s=6 id='new_password' text="registration.new_password" type='password' required
                                      autocomplete="new-password"/>
                        <x-input.text s=6 id='confirmpwd' text="registration.confirmpwd"
                                      name="new_password_confirmation"
                                      type='password' required autocomplete="new-password"/>
                        <x-input.button class="right" text="general.change_password"/>
                    </div>
                </form>
            </div>
        </li>
    </ul>
    @can('fill', App\Models\SemesterEvaluation::class)
        <a href="{{ route('secretariat.evaluation.show') }}" class="btn left coli blue">Szemeszter értékelés</a>
    @endif

    <form action="{{ route('logout') }}" method="POST">
        @csrf
        <x-input.button only_input class="right" text="general.logout"/>
    </form>

@endif
