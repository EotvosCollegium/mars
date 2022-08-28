@extends('layouts.app')

@section('title')
    <a href="#!" class="breadcrumb">@lang('user.user_data')</a>
@endsection

@section('content')
    @include('user.profile', ['user' => $user])
@endsection
