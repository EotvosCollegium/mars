@extends('layouts.app')

@section('title')
<a href="#!" class="breadcrumb">@lang('general.admin')</a>
<a href="#!" class="breadcrumb">@lang("general.users")</a>
@endsection
@section('secretariat_module') active @endsection

@section('content')

@livewire('list-users')

@endsection
