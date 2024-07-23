<!-- Admin site showing all applicants -->
@extends('layouts.app')
@section('title')
    <a href="#!" class="breadcrumb">Felvételi jelentkezők</a>
@endsection

@section('content')
    @include('auth.admission.period')
    <div class="card">
        <div class="card-content">
            <div class="row center" style="margin-bottom: 0">
                <form id="workshop-filter" method="GET" action="{{route('admission.applicants.index')}}">
                    @if($workshops->count() > 1)
                        <x-input.select id="workshop" :elements="$workshops" :default="$workshop" :allowEmpty=true
                                    text="Műhely"/>
                    @endif

                    @can('viewUnfinished', \App\Models\Application::class)
                        <label>
                            <input type="radio" name="status_filter" value="everybody" {{$status_filter == 'everybody' ? "checked": ""}}>
                            <span style="padding-left: 25px; margin: 5px">Minden jelentkező</span>
                        </label>

                        <label>
                            <input type="radio" name="status_filter" value="unsubmitted" {{$status_filter == 'unsubmitted' ? "checked": ""}}>
                            <span style="padding-left: 25px; margin: 5px">Csak nem véglegesítettek</span>
                        </label>
                    @endif
                    <label>
                        <input type="radio" name="status_filter" value="submitted" {{$status_filter == 'submitted' ? "checked": ""}}>
                        <span style="padding-left: 25px; margin: 5px">Csak véglegesítettek</span>
                    </label>
                    <label>
                        <input type="radio" name="status_filter" value="called_in" {{($status_filter == 'called_in') ? "checked": ""}}>
                        <span style="padding-left: 25px; margin: 5px">Csak behívottak</span>
                    </label>
                    <label>
                        <input type="radio" name="status_filter" value="admitted" {{($status_filter == 'admitted') ? "checked": ""}}>
                        <span style="padding-left: 25px; margin: 5px">Csak felvettek</span>
                    </label>
                    <x-input.button type="submit" text="Szűrés"/>
                </form>
                <form id="empty-filter" method="GET" action="{{route('admission.applicants.index')}}"
                        style="{{($workshop=='')?'display: none':''}}">
                    <x-input.button id="delete-filter" class="grey" text="Szűrő törlése"/>
                </form>
            </div>
            <blockquote>
                @can('editStatus', \App\Models\Application::class)
                    <p>A behívott/felvett státusz a jelentkezők számára nem nyilvános.</p>
                @endcan
                @can('finalize', \App\Models\Application::class)
                    <p>{{$applicationDeadline?->addWeeks(1)?->format('Y. m. d.')}} után lehet a lap alján felvenni a
                        kiválasztott jelentkezőket, ezzel véglegesíteni a felvételit.</p>
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
    @foreach($applications as $application)
        @include('auth.application.application', ['user' => $application->user, 'expanded' => false])
    @endforeach
    <hr>
    <h6>Összesen: <b class="right">{{$applications->count()}} jelentkező</b></h6>
    @can('finalize', \App\Models\Application::class)
        @if($applicationDeadline?->addWeeks(1) < now())
            <div class="card" style="margin-top:20px">
                <div class="card-content">
                    <div class="row" style="margin:0">
                        <form id="finalize-application-process" method="POST"
                              action="{{route('admission.finalize')}}">
                            @csrf
                            <div class="col">
                                Hogyha a felvételi eljárás befejeződött, akkor a felvett jelentkezőket itt lehet
                                jóváhagyni.
                                Ezzel együtt minden más felvételiző elutasításra, anyagai törlésre, valamint az összes
                                felvételihez kapcsolódó (felvételiztető) jog elvételre kerül.
                                A "Véglegesítve" és "Behívva" státuszú jelentkezőket előbb el kell utasítani, vagy fel
                                kell venni.
                            </div>
                            <x-input.button class="red right" text="Felvételi lezárása"/>
                        </form>
                    </div>
                </div>
            </div>
        @endif
    @endcan
    @can('viewAll', \App\Models\Application::class)
        <div class="fixed-action-btn">
            <a href="{{ route('admission.export') }}" class="btn-floating btn-large">
                <i class="large material-icons">file_download</i>
            </a>
        </div>
    @endcan

@endsection
