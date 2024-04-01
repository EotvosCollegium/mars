@extends('layouts.app')
@section('title')
<i class="material-icons left">build</i>@lang('faults.faults')
@endsection
@section('content')
<div class="row">
    <div class="col s12">
        <div class="card">
            <div class="card-content">
                <span class="card-title">@lang('faults.add-fault')</span>
                <blockquote>@lang('faults.fault_description')</blockquote>
                <blockquote><a href="{{route('internet.index')}}">@lang('faults.fault_internet_description')</a></blockquote>
                <form id="send-fault" class="form-horizontal" method="POST" action=" {{ route('faults.add') }} ">
                    @csrf
                    <x-input.text id="location" text="faults.location" autofocus required/>
                    <x-input.textarea id="description" text="faults.description" required/>
                    <x-input.button class="right" text="faults.submit"/>
                </form>
                @include('dormitory.faults.list')
            </div>
        </div>
    </div>
</div>
@endsection
