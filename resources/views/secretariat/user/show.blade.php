@extends('layouts.app')

@section('title')
<a href="#!" class="breadcrumb">@lang('admin.admin')</a>
<a href="{{ route('users.index') }}" class="breadcrumb" style="cursor: pointer">@lang('admin.user_management')</a>
<a href="#!" class="breadcrumb">{{ $user->name }}</a>
@endsection
@section('secretariat_module') active @endsection

@section('content')
<div class="row">
    <div class="col s12">
        @include('user.profile', ['user' => $user])
    </div>
</div>
@endsection
