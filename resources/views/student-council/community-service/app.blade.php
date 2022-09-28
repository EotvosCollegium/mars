@extends('layouts.app')

@section('title')
<a href="#!" class="breadcrumb">@lang('role.student-council')</a>
<a href="#!" class="breadcrumb">@lang('community-service.community-service')</a>
@endsection

@section('student_council_module') active @endsection

@section('content')

<div class="card">
    <div class="card-content">
        <span class="card-title">@lang('community-service.add-new-service')</span>
        <blockquote>@lang('community-service.add-new-service-descr')</blockquote>
        <form method="POST" action="{{ route('community_service.create') }}">
            @csrf
            <div class="row">
                <x-input.text m=6 l=6 id="description" required :text="__('community-service.description')" />
                @php
                    $elements=collect([]);
                    $studentCouncil=\App\Models\Role::StudentsCouncil();
                    foreach (\App\Models\Role::STUDENT_COUNCIL_LEADERS as $leader){
                        $elements=$elements->concat([$studentCouncil->getUsers($studentCouncil->getObject($leader))]);
                    }
                    foreach (\App\Models\Role::COMMITTEE_LEADERS as $leader){
                        $elements=$elements->concat([$studentCouncil->getUsers($studentCouncil->getObject($leader))]);
                    }
                    $elements=$elements->flatten()->unique();
                @endphp
                <x-input.select m=6 l=6 id="approver" :elements="$elements" :text="__('community-service.approver')"/>
            </div>
            <x-input.button floating class="btn=large right" icon="send" />
        </form>
    </div>
</div>


@can('approveAny', \App\Models\CommunityService::class)
<div class="card">
    <div class="card-content">
        <span class="card-title">@lang('community-service.search-user')</span>
        <blockquote>@lang('community-service.search-user-descr')</blockquote>
        <div class="row center">
        <x-input.button text="general.search" />
        </div>
    </div>
</div>
@endcan

@php
    $user=\Illuminate\Support\Facades\Auth::user();
@endphp

@foreach($semesters as $semester)
    @if($semester->communityServices->count() != 0)
    @php
        $communityServices=$semester->communityServices;
    @endphp
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
                                @foreach ($communityServices as $communityService)
                                    <tr>
                                        <td style="word-break: break-all">{{ $communityService->description }}</td>
                                        <td>{{ $communityService->created_at->format('Y. m. d.') }}</td>
                                        <td>{{ $communityService->requester->name }}</td>
                                        <td>{{ $communityService->approver->name }}</td>
                                        <td>
                                            @if($communityService->approved)
                                                <span class="new badge green" data-badge-caption="Elfogadva"></span>
                                            @else
                                                <span class="new badge red" data-badge-caption="Elutasítva"></span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($user->can('approve', $communityService) && !$communityService->approved && $semester->isCurrent())
                                                <form action={{ route('community_service.approve', ['community_service' => $communityService->id])}} method="POST">
                                                    @csrf
                                                    <button type="submit" class="btn-floating btn-small waves-effect waves-light green">
                                                        <i class="material-icons">check</i>
                                                    </button>
                                                </form>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
@endforeach

@endsection