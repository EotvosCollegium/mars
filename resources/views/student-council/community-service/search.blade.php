@extends('layouts.app')

@section('title')
<a href="#!" class="breadcrumb">@lang('role.student-council')</a>
<a href="{{route('community_service')}}" class="breadcrumb">@lang('community-service.community-service')</a>
<a href="#!" class="breadcrumb">@lang('community-service.community-service')</a>
@endsection