<!-- Admin site showing all applicants -->
@extends('layouts.app')
@section('title')
    <a href="#!" class="breadcrumb">Felvételi jelentkezők</a>
@endsection

@section('content')
    @if($workshops->count() > 1)
    <div class="card">
        <div class="card-content">
            <div class="row center" style="margin-bottom: 0">
                <form id="workshop-filter" method="GET" route="{{route('applications')}}">
                    <x-input.select id="workshop" :elements="$workshops" allow-empty :default="$workshop"
                                    text="Műhely"/>
                    @can('viewUnfinishedApplications', \App\models\User::class)
                        @foreach (\App\Models\ApplicationForm::STATUSES as $st)
                            <label>
                                <input type="radio" name="status" value="{{$st}}" @if($status == $st) checked @endif>
                                <span style="padding-left: 25px; margin: 5px">@include('auth.application.status', ['status' => $st])</span>
                            </label>
                        @endforeach
                    @endif
                    <x-input.button type="submit" text="Szűrés"/>
                </form>
                <form id="empty-filter" method="GET" route="{{route('application')}}" style="{{($status=='' && $workshop=='')?'display: none':''}}">
                    <x-input.button id="delete-filter" text="Szűrő törlése"/>
                </form>
            </div>
            <blockquote>
                @can('editApplicationStatus', \App\models\User::class)
                <p>A jelentkezők aktuális státusza a jelentkezők számára nem nyilvános.</p>
                @endcan
                @can('finalizeApplicationProcess', \App\Models\User::class)
                <p>{{$applicationDeadline->addWeeks(2)->format('Y. m. d.')}} után lehet a lap alján felvenni a kiválasztott jelentkezőket, ezzel véglegesíteni a felvételit.</p>
                @endcan
            </blockquote>

            @push('scripts')
                {{-- Show the empty filter button on change of the workshop selector --}}
                <script>
                    $(document).ready(function () {
                        $('#workshop').change(function () {
                            $('#empty-filter').show();
                        });
                    });
                </script>
            @endpush
        </div>
    </div>
    @endif
    @foreach($applications as $application)
        <a href="{{route('applications', ['id' => $application->user_id])}}">
            @include('auth.application.application', ['user' => $application->user, 'expanded' => false])
        </a>
    @endforeach
    <hr>
    <h6>Összesen: <b class="right">{{$applications->count()}} jelentkező</b></h6>
    @can('finalizeApplicationProcess', \App\Models\User::class)
    @if($applicationDeadline->addWeeks(2) < now())
    <div class="card" style="margin-top:20px">
        <div class="card-content">
            <div class="row" style="margin:0">
                <form id="finalize-application-process" method="POST" action="{{route('application.finalize')}}">
                    @csrf
                    <div class="col">
                        Hogyha a felvételi eljárás befejeződött, akkor a felvett jelentkezőket itt lehet jóváhagyni.
                        Ezzel együtt minden más felvételiző elutasításra, anyagai törlésre, valamint az összes
                        felvételihez kapcsolódó (felvételiztető) jog elvételre kerül.
                        A "Véglegesítve" és "Behívva" státuszú jelentkezőket előbb el kell utasítani, vagy fel kell venni.
                    </div>
                    <x-input.button class="red right" text="Felvételi lezárása"/>
                </form>
            </div>
        </div>
    </div>
    @endif
    @endcan
    @can('viewAllApplications', \App\Models\User::class)
    <div class="fixed-action-btn">
        <a href="{{ route('applications.export') }}" class="btn-floating btn-large">
            <i class="large material-icons">file_download</i>
        </a>
    </div>
    @endcan

@endsection
