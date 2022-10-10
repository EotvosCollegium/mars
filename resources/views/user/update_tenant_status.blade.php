@extends('layouts.app')
@section('title')
<a href="#!" class="breadcrumb">@lang('user.update_tenant_status')</a>
@endsection

@section('content')
<div class="row">
    <div class="col s12">
        <div class="card">
            <div class="card-content">
                <span class="card-title">@lang('user.update_personal_data')</span>
                <blockquote>@lang('user.update_personal_data_descr')</blockquote>
                @include('user.personal-information', ['user' => $user])
            </div>
        </div>
    </div>
</div>

@if (env('APPLICATION_DEADLINE')>\Carbon\Carbon::now() && Auth::user()->isTenant() && !Auth::user()->isCollegist())
<div class="row">
    <div class="col s12">
        <div class="card">
            <div class="card-content">
                <span class="card-title">Jelentkezés collegistának</span>
                <blockquote>
                    Ha már korábban laktál a Collegiumban és emiatt regisztráltál az Uránba,
                    akkor ide kattintva tudod elkezdeni a jelentkezési folyamatot.
                </blockquote>
                <div class="row center">
                    <x-input.button text="general.show" :href="route('users.tenant-update.to-applicant')"/>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

@endsection