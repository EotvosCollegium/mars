@extends('layouts.app')

@section('title')
<a href="#!" class="breadcrumb">@lang('general.admin')</a>
<a href="{{ route('routers') }}" class="breadcrumb" style="cursor: pointer">@lang('router.router_monitor')</a>
<a href="#!" class="breadcrumb">@lang('general.add_new')</a>
@endsection
@section('admin_module') active @endsection

@section('content')

<div class="row">
    <div class="col s12">
        <div class="card">
            <form action="{{ route('routers.store') }}" method="POST">
                @csrf
                <div class="card-content">
                    <span class="card-title">Új router</span>
                    <div class="row">
                        <x-input.text s="6" type="text" text="router.ip" id="ip" maxlength="15" required/>
                        <x-input.text s="6" type="number" text="router.room" id="room" min="1" max="500" required/>
                    </div>
                    <div class="row">
                        <x-input.text s="4" type="text" text="router.port" id="port"/>
                        <x-input.text s="4" type="text" text="router.type" id="type"/>
                        <x-input.text s="4" type="text" text="router.serial_number" id="serial_number"/>
                    </div>
                    <div><p>MAC cím</p></div>
                    <div class="row">
                        <x-input.text s="4" type="text" text="WAN" id="mac_wan"/>
                        <x-input.text s="4" type="text" text="2G/LAN" id="mac_2g_lan"/>
                        <x-input.text s="4" type="text" text="5G" id="mac_5g"/>
                    </div>
                    <div class="row">
                        <x-input.text type="text" id="comment" text="general.comment" maxlength="255"/>
                    </div>
                    <div class="row">
                        <x-input.text s="6" type="date" id="date_of_acquisition" text="router.date_of_acquisition"/>
                        <x-input.text s="6" type="date" id="date_of_deployment" text="router.date_of_deployment"/>
                    </div>
                </div>
                <div class="card-action right-align">
                    <button type="submit" class="waves-effect btn">Mentés</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

