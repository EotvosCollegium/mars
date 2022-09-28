@extends('layouts.app')

@section('title')
<a href="#!" class="breadcrumb">@lang('role.student-council')</a>
<a href="{{route('community_service')}}" style="cursor: pointer" class="breadcrumb">@lang('community-service.community-service')</a>
<a href="#!" class="breadcrumb">@lang('community-service.community-service')</a>
@endsection

@section('student_council_module') active @endsection

@section('content')
<div class="card">
    <div class="card-content">
        <span class="card-title">@lang('community-service.search-user')</span>
        <blockquote>@lang('community-service.search-user-descr')</blockquote>
        <form action={{ route('community_service.search')}} method="GET">
            <div class="row center">
                <x-input.select m=12 l=12 id="requester" :elements="\App\Models\User::active()->get()" :text="__('community-service.requester')"/>
                <x-input.button text="general.search"/>
            </div>
        </form>
    </div>
</div>
@foreach($semesters as $semester)
<div class="col s12">
    <div class="card">
        <div class="card-content">
            <span class="card-title">{{ $semester->tag }}</span>
            <div class="row">
                <div class="col s12">
                    <table style="width:100%">
                        <thead>
                            <tr>
                                <th>@lang('community-service.description')</th>
                                <th>@lang('checkout.date')</th>
                                <th>@lang('community-service.requester')</th>
                                <th>@lang('community-service.approver')</th>
                                <th>@lang('community-service.approved')</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($semester->communityServices as $communityService)
                                <tr>
                                    <td style="word-break: break-all">{{ $communityService->description }}</td>
                                    <td>{{ $communityService->created_at->format('Y. m. d.') }}</td>
                                    <td>{{ $communityService->requester->name }}</td>
                                    <td>{{ $communityService->approver->name }}</td>
                                    <td>{{ $communityService->status }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endforeach

@endsection