@extends('layouts.app')
@section('title')
    <i class="material-icons left">wifi</i>@lang('internet.internet')
@endsection

@section('content')
    <div class="row">
        <div class="col s12">
            @include('network.internet.internet_access')
        </div>
        @if($internet_access->has_internet_until > \Carbon\Carbon::now())
            <div class="col s12">
                @include('network.internet.wifi_password')
            </div>
            <div class="col s12">
                @include('network.internet.report_fault')
            </div>
            <div class="col s12">
                @include('network.internet.mac_addresses')
            </div>
        @endif
    </div>
@endsection
