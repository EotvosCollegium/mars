@extends('layouts.app')
@section('title')
    <a href="#!" class="breadcrumb">@lang('user.update_tenant_status')</a>
@endsection

@section('content')
    <div class="row">
        <div class="col s12">
            <div class="card">
                <div class="card-content">
                    <span class="card-title">@lang('user.update_tenant_status')</span>
                    <blockquote>@lang('user.set_tenant_until')</blockquote>
                    <form method="POST" action="{{ route('users.update.tenant_until', ['user' => user()]) }}">
                        @csrf
                        <div class="row">
                            <x-input.datepicker
                                id='tenant_until'
                                required
                                text='user.tenant_until'
                                :value="user()->personalInformation?->tenant_until"/>
                            <x-input.button class="right" text="general.save"/>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @if (
        app(App\Http\Controllers\Auth\ApplicationController::class)->isActive()
    )
        <div class="row">
            <div class="col s12">
                <div class="card">
                    <div class="card-content">
                        <span class="card-title">Jelentkezés collegistának</span>
                        <blockquote>
                            Ha már korábban laktál a Collegiumban és emiatt regisztráltál az Uránba,
                            akkor ide kattintva tudod elkezdeni a jelentkezési folyamatot. Ezzel megszűnik a vendég státuszod.
                        </blockquote>
                        <div class="row center">
                            <form method="post" action="{{route('users.tenant-update.to-applicant')}}">
                                @csrf
                                <x-input.button text="general.show"/>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

@endsection
