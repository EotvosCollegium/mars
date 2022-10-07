@extends('layouts.app')
@section('title')
<a href="#!" class="breadcrumb">@lang('user.update_tenant_status')</a>
@endsection

@section('content')
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

<div class="row">
    <div class="col s12">
        <div class="card">
            <div class="card-content">
                <span class="card-title">@lang('user.update_tenant_until')</span>
                <form action="{{ route('users.tenant-update.update') }}" method="post">
                    @csrf
                    <div class="row">
                        <blockquote>
                            @lang('user.set_tenant_until')
                        </blockquote>
                        <x-input.datepicker
                            id='tenant_until'
                            required
                            locale='user'
                            :value="$user->personalInformation?->tenant_until" />
                    </div>
                    <div class="row">
                        <x-input.button class="right red" text="general.save" />
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection