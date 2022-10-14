@forelse($semesters as $semester)
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
                                <th>@lang('community-service.status')</th>
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
                                        <span class="new badge {{ $communityService->getStatusColor() }}" data-badge-caption="">
                                            {{ $communityService->status }}
                                        </span>
                                    </td>
                                    @if($showApprove)
                                    <td>
                                        @can('approve', $communityService)
                                            <form action={{ route('community_service.approve', ['community_service' => $communityService->id])}} method="POST">
                                                @csrf
                                                <button type="submit" class="btn-floating btn-small waves-effect waves-light green">
                                                    <i class="material-icons">check</i>
                                                </button>
                                            </form>
                                            <div style="padding: 5px"></div>
                                            <form action={{ route('community_service.reject', ['community_service' => $communityService->id])}} method="POST">
                                                @csrf
                                                <button type="submit" class="btn-floating btn-small waves-effect waves-light red">
                                                    <i class="material-icons">close</i>
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                    @endif
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@empty
    <div class="card">
        <div class="card-content">
            <span class="card-title">@lang('community-service.no-services')</span>
            <blockquote>@lang('community-service.no-services-descr')</blockquote>
        </div>
    </div>
@endforelse
