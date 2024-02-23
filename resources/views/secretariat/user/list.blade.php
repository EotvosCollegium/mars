@extends('layouts.app')

@section('title')
<a href="#!" class="breadcrumb">@lang('general.admin')</a>
<a href="#!" class="breadcrumb">@lang("general.users")</a>
@endsection
@section('secretariat_module') active @endsection

@section('content')

@livewire('list-users')

{{-- Export --}}
<div class="fixed-action-btn tooltipped" data-position="left" data-tooltip="Exportálás">
    <a href="{{ route('users.export') }}" class="btn-floating btn-large">
        <i class="large material-icons">file_download</i>
    </a>
</div>
@endsection
