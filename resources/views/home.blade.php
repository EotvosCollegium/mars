@extends('layouts.app')

@section('title')
<i class="material-icons left">chevron_right</i>@lang('general.home')
@endsection

@section('content')
@if (session('status'))
<div class="row">
    <div class="col s12">
        <div class="card">
            <div class="card-content">
                <p>{{ session('status') }}</p>
            </div>
        </div>
    </div>
</div>
@endif
<!-- Information -->
@if($information_general.$information_collegist != '' ||
    user()->hasRole([
        \App\Models\Role::STUDENT_COUNCIL => \App\Models\Role::STUDENT_COUNCIL_LEADERS,
        \App\Models\Role::SYS_ADMIN,
        \App\Models\Role::STUDENT_COUNCIL_SECRETARY]))
<div class="row">
    <div class="col s12">
        <div class="card">
            <div class="card-content">
                <span class="card-title">Információk</span>
                <div id="info_text">
                    @markdown($information_general)
                    @markdown($information_collegist)
                    @if(user()->hasRole([
                        \App\Models\Role::STUDENT_COUNCIL => \App\Models\Role::STUDENT_COUNCIL_LEADERS,
                        \App\Models\Role::SYS_ADMIN,
                        \App\Models\Role::STUDENT_COUNCIL_SECRETARY]))
                        <x-input.button floating class="right" id="edit_btn" icon="mode_edit"/>
                    @endif
                </div>
                <div id="info_input" class="hidden">
                    <form id="info_form" method="POST" action="{{ route('home.edit') }}">
                        @csrf
                        Általános:
                        <textarea class="materialize-textarea" name="info_general">{{ $information_general }}</textarea>
                        Csak Collegistáknak:
                        <textarea class="materialize-textarea" name="info_collegist">{{ $information_collegist }}</textarea>
                        <small>
                            Formázásra a
                            <a href='https://www.markdownguide.org/cheat-sheet/' target='__blank'>Markdown jelölései</a>
                            használhatóak.
                        </small>
                        <x-input.button floating class="right" icon="save"/>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endif
<!-- Contacts -->
<div class="row">
    <div class="col s12">
        <div class="card">
            <div class="card-content">
                <span class="card-title">@lang('general.contacts')</span>
                <!-- Student Council -->
                @if(isset($contacts[\App\Models\Role::STUDENT_COUNCIL]))
                <h5>Választmány</h5>
                <i><a href="mailto:{{ config('contacts.mail_valasztmany') }}">{{config('contacts.mail_valasztmany')}}</a></i><br>
                @foreach($contacts[\App\Models\Role::STUDENT_COUNCIL] as $roleuser)
                <b>@lang('role.'.$roleuser->object->name)</b>:
                    <i>{{$roleuser->user?->name}}</i>
                    @if($roleuser->object->name == \App\Models\Role::PRESIDENT)
                    <a href="mailto:{{ config('contacts.mail_elnok') }}">{{config('contacts.mail_elnok')}}</a>
                    <a href="mailto:{{ $roleuser->user?->email }}">{{ $roleuser->user?->email }}</a>
                    {{ $roleuser->user?->personalInformation?->phone_number }}
                    @endif
                    @if($roleuser->user?->room)
                    ({{$roleuser->user?->room}}. szoba)
                    @endif
                <br>
                @endforeach
                <br>
                <!-- Student Council Secretary -->
                <b>@lang('role.'.\App\Models\Role::STUDENT_COUNCIL_SECRETARY)</b>:
                <i>{{$contacts[\App\Models\Role::STUDENT_COUNCIL_SECRETARY]?->name}}</i>
                @if($contacts[\App\Models\Role::STUDENT_COUNCIL_SECRETARY]?->room)
                ({{$contacts[\App\Models\Role::STUDENT_COUNCIL_SECRETARY]?->room}}. szoba)
                @endif
                <br>
                <!-- Board of trustees members -->
                <b>@lang('role.'.\App\Models\Role::BOARD_OF_TRUSTEES_MEMBER)</b>:
                @foreach($contacts[\App\Models\Role::BOARD_OF_TRUSTEES_MEMBER] as $user)
                    @if(!$loop->first)|@endif
                    <i>{{$user->name}}</i>
                    @if($user->room)
                    ({{$user->room}}. szoba)
                    @endif
                @endforeach
                <br>
                <!-- Ethics commissioners -->
                <b>@lang('role.'.\App\Models\Role::ETHICS_COMMISSIONER)</b>:
                @foreach($contacts[\App\Models\Role::ETHICS_COMMISSIONER] as $user)
                    @if(!$loop->first)|@endif
                    <i>{{$user->name}}</i>
                    @if($user->hasEducationalInformation())
                    <a href="mailto:{{$user->email}}"> {{$user->email}}</a>
                    @endif
                    @if($user->room)
                    ({{$user->room}}. szoba)
                    @endif
                @endforeach

                <!-- Workshop functionaries -->
                <div class="arrow-dropdown">
                    <h5 class="arrow-dropdown-title closed"><a>
                        @lang('role.workshop-functionaries')
                    </a></h5>
                    <div class="arrow-dropdown-content">
                        <ul>
                            @foreach($contacts['workshops'] as $name => $functionaries)
                            <li>
                                <b>{{$name}}</b>
                                <ul>
                                    <li>@lang('role.'.\App\Models\Role::WORKSHOP_LEADER):
                                        <i>{{$functionaries['leaders']}}</i>
                                    </li>
                                    <li>@lang('role.'.\App\Models\Role::WORKSHOP_ADMINISTRATOR):
                                        <i>{{$functionaries['administrators']}}</i>
                                    </li>
                                </ul>
                            </li>
                            @endforeach
                        </ul>
                    </div>
                </div>

                @endif
                <!-- Admins -->
                @if(isset($contacts['admins']))
                <h5>@lang('role.sys-admins')</h5>
                <i><a href="mailto:{{ config('contacts.developer_email') }}">{{config('contacts.developer_email')}}</a></i><br>
                @foreach($contacts['admins'] as $admin)
                    @if(!$loop->first)|@endif
                    <i>{{$admin->name}}</i>
                    @if($admin->room)
                        ({{$admin->room}}. szoba)
                    @endif
                @endforeach
                @endif

                <h5>@lang('general.others')</h5>
                @foreach($contacts['other'] as $key => $other)
                    <b>@lang('role.'.$key)</b>:
                    <i>{{$other['name'] ?? ''}}</i>
                    @if($other['email'] ?? null)
                    <a href="mailto:{{ $other['email'] }}">{{$other['email']}}</a>
                    @endif
                    @if($other['link'] ?? null)
                    <a href="{{ $other['link'] }}">{{$other['link']}}</a>
                    @endif
                    {{ $other['phone_number'] ?? ''}}
                    <br>
                @endforeach
            </div>
        </div>
    </div>
</div>

@if($epistola)
<div class="cards-container">
    @foreach ($epistola as $article)
        @include('student-council.communication-committee.epistola', ['article'=> $article])
    @endforeach
</div>
@endif


@endsection

@push('scripts')
<script>
$("#edit_btn").click(function() {
    $("#info_text").toggleClass('hidden');
    $("#info_input").toggleClass('hidden');
    $('.materialboxed').materialbox();
});
function standby(id) {
    document.getElementById(id).src = "{{ url('/img/committee-logos/kommbiz.jpg') }}"
}
$(document).ready(function(){
    $('.materialboxed').materialbox();
});
</script>
@endpush
