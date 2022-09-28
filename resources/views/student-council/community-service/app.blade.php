@extends('layouts.app')

@section('title')
<a href="#!" class="breadcrumb">@lang('role.student-council')</a>
<a href="#!" class="breadcrumb">@lang('community-service.community-service')</a>
@endsection

@section('student_council_module') active @endsection

@section('content')

@can('create', \App\Models\CommunityService::class)
<div class="card">
    <div class="card-content">
        <span class="card-title">@lang('community-service.add-new-service')</span>
        <blockquote>@lang('community-service.add-new-service-descr')</blockquote>
        <form method="POST" action="{{ route('community_service.create') }}">
            @csrf
            <div class="row">
                <x-input.text m=6 l=6 id="description" required :text="__('community-service.description')" />
                <x-input.select m=6 l=6 id="approver" :elements="$possible_approvers" :text="__('community-service.approver')"/>
            </div>
            <x-input.button floating class="btn=large right" icon="send" />
        </form>
    </div>
</div>
@endcan


@can('approveAny', \App\Models\CommunityService::class)
<div class="card">
    <div class="card-content">
        <span class="card-title">@lang('community-service.search-user')</span>
        <blockquote>@lang('community-service.search-user-descr')</blockquote>
        <div class="row center">
        <x-input.button text="general.search" :href="route('community_service.search')"></x-input.button>
        </div>
    </div>
</div>
@endcan


@foreach($semesters as $semester)
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
                                        <td>
                                            <span class="new badge @if($communityService->approved) green @endif" data-badge-caption="">
                                                {{ $communityService->status }}
                                            </span>
                                        </td>
                                        <td>
                                            @can('approve', $communityService)
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
@endforeach

@endsection