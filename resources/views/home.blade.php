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
@if($information != '' || Auth::user()->hasRole(\App\Models\Role::STUDENT_COUNCIL))
<div class="row">
    <div class="col s12">
        <div class="card">
            <div class="card-content">
                <span class="card-title">@lang('general.information')</span>
                <form id="info_form" method="POST" action="{{ route('home.edit') }}">
                    @csrf
                    <p id="info_text">@markdown($information == "" ? "Adj hozzá valamit lenn." : $information)</p>
                    <div id="info_input"></div>
                </form>
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
                <h5>@lang('role.student-council')</h5>
                <i><a href="mailto:{{ env('MAIL_VALASZTMANY') }}">{{env('MAIL_VALASZTMANY')}}</a></i><br>
                @foreach($contacts[\App\Models\Role::STUDENT_COUNCIL] as $roleuser)
                <b>@lang('role.'.$roleuser->object->name)</b>: 
                    <i>{{$roleuser->user->name}}</i>
                    @if($roleuser->object->name == \App\Models\Role::PRESIDENT)
                    <a href="mailto:{{ env('MAIL_ELNOK') }}">{{env('MAIL_ELNOK')}}</a>
                    <a href="mailto:{{ $roleuser->user->email }}">{{ $roleuser->user->email }}</a>
                    {{ $roleuser->user->personalInformation->phone_number }}
                    @endif
                    @if($roleuser->user->room)
                    ({{$roleuser->user->room}}. szoba)
                    @endif
                <br>
                @endforeach
                <br>
                <!-- Student Council Secretary -->
                <b>@lang('role.'.\App\Models\Role::STUDENT_COUNCIL_SECRETARY)</b>:
                <i>{{$contacts[\App\Models\Role::STUDENT_COUNCIL_SECRETARY]->name}}</i>
                @if($contacts[\App\Models\Role::STUDENT_COUNCIL_SECRETARY]->room)
                ({{$contacts[\App\Models\Role::STUDENT_COUNCIL_SECRETARY]->room}}. szoba)
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
                    <a href="mailto:{{$user->educationalInformation->email}}"> {{$user->educationalInformation->email}}</a>
                    @endif
                    @if($user->room)
                    ({{$user->room}}. szoba)
                    @endif
                @endforeach


                @endif
                <!-- Admins -->
                @if(isset($contacts['admins']))
                <h5>@lang('role.sys-admins')</h5>
                <i><a href="mailto:{{ env('DEVELOPER_EMAIL') }}">{{env('DEVELOPER_EMAIL')}}</a></i><br>
                @foreach($contacts['admins'] as $admin)
                    @if(!$loop->first)|@endif
                    <i>{{$admin->name}}</i>
                    @if($admin->room)
                        ({{$roleuser->user->room}}. szoba)
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

@if(Auth::user()->hasRole(\App\Models\Role::STUDENT_COUNCIL))
<div class="fixed-action-btn">
    <a class="btn-floating btn-large">
        <i id="edit_btn" class="large material-icons">mode_edit</i>
    </a>
</div>
@endif

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
    if($("#edit_btn").text() == "mode_edit"){
        $("#edit_btn").text("send")
        $("#info_text").text("");
        $("#info_input").html(`<textarea name="text" class="materialize-textarea">{{ $information ?? ""}}</textarea>`);
    }
    else{
        $("#info_form").submit();
    }
});
function standby(id) {
    document.getElementById(id).src = "{{ url('/img/committee-logos/kommbiz.jpg') }}"
}
$(document).ready(function(){
    $('.materialboxed').materialbox();
  });
</script>
@endpush
