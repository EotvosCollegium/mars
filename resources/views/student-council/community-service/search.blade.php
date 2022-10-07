@extends('layouts.app')

@section('title')
<a href="#!" class="breadcrumb">@lang('role.student-council')</a>
<a href="{{route('community_service')}}" style="cursor: pointer" class="breadcrumb">@lang('community-service.community-service')</a>
<a href="#!" class="breadcrumb">{{$selectedUser->name??__('community-service.search-user')}}</a>
@endsection

@section('student_council_module') active @endsection

@section('content')
<div class="card">
    <div class="card-content">
        <span class="card-title">@lang('community-service.search-user')</span>
        <blockquote>@lang('community-service.search-user-descr')</blockquote>
        <form action={{ route('community_service.search')}} method="GET">
            <div class="row center">
                <x-input.select m=12 l=12 id="requester" :elements="\App\Models\User::active()->get()" :text="__('community-service.requester')" :default="$selectedUser->id??null"/>
                <x-input.button text="general.search"/>
            </div>
        </form>
    </div>
</div>

@include('student-council.community-service.table', ['showApprove' => false])

@endsection