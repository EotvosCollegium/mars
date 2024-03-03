@extends('layouts.app')

@section('title')
<a href="#!" class="breadcrumb">@lang('general.admin')</a>
<a href="{{ route('routers') }}" class="breadcrumb" style="cursor: pointer">@lang('router.router_monitor')</a>
<a href="#!" class="breadcrumb">{{ $router->ip }}</a>
@endsection
@section('admin_module') active @endsection

@section('content')

<div class="row">
    <div class="col s12">
        <div class="card">
            <form action="{{ route('routers.update', $router->ip) }}" method="POST">
                @csrf
                <div class="card-content">
                    <span class="card-title">Módosítás</span>
                    <div class="row">
                        <x-input.textarea s="6" type="text" text="router.ip" id="ip" maxlength="15" required>{{ $router->ip }}</x-input.textarea>
                        <x-input.text s="6" type="number" text="router.room" id="room" min="1" max="500" :value="$router->room" required>
                        </x-input.text>
                    </div>
                    <div class="row">
                        <x-input.textarea s="4" type="text" text="router.port" id="port">{{ $router->port }}</x-input.textarea>
                        <x-input.textarea s="4" type="text" text="router.type" id="type">{{ $router->type }}</x-input.textarea>
                        <x-input.textarea s="4" type="text" text="router.serial_number" id="serial_number">{{ $router->serial_number }}</x-input.textarea>
                    </div>
                    <div><p>MAC cím</p></div>
                    <div class="row">
                        <x-input.textarea s="4" type="text" text="WAN" id="mac_WAN">{{ $router->mac_WAN }}</x-input.textarea>
                        <x-input.textarea s="4" type="text" text="2G/LAN" id="mac_2G_LAN">{{ $router->mac_2G_LAN }}</x-input.textarea>
                        <x-input.textarea s="4" type="text" text="5G" id="mac_5G">{{ $router->mac_5G }}</x-input.textarea>
                    </div>
                    <div class="row">
                        <x-input.textarea type="text" id="comment" text="general.comment" maxlength="255">{{ $router->comment }}</x-input.textarea>
                    </div>
                    <div class="row">
                        <x-input.datepicker s="6" id="date_of_acquisition" text="router.date_of_acquisition" value="{{ $router->date_of_acquisition }}"/>
                        <x-input.datepicker s="6" id="date_of_deployment" text="router.date_of_deployment" value="{{ $router->date_of_deployment }}"/>
                    </div>
                </div>
                <div class="card-action">
                    <div class="row" style="margin:0">
                        <x-input.button text="general.save" class="right"/>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

