@extends('layouts.app')
@section('title')
    <a href="{{ route('admission.applicants.index') }}" class="breadcrumb" style="cursor: pointer">Felvételi
        jelentkezők</a>
    <a href="#!" class="breadcrumb">Felvételi véglegesítés</a>
@endsection

@section('content')
    <div class="card" style="margin-top:20px">
        <div class="card-content">
            <div class="row">
                <div class="col">
                    Hogyha a felvételi eljárás befejeződött, akkor a felvett jelentkezőket itt lehet
                    jóváhagyni. A felvettek státusza a {{ $semester->tag }} szemeszterre automatikusan aktív lesz.
                </div>
                <div class="col" style="width: 100%">
                    <table>
                        <thead>
                        <tr>
                            <th>Felvett neve</th>
                            <th>Státusza</th>
                            <th>Műhelye(i)</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($admitted_applications as $application)
                            <tr>
                                <td>
                                    <a href="{{route('admission.applicants.show', ['application' => $application->id])}}">
                                        {{ $application->user->name }}
                                    </a></td>
                                <td>{{ $application->admitted_for_resident_status ? 'Bentlakó' : 'Bejáró'}}</td>
                                <td>
                                    @include('user.workshop_tags', ['user' => $application->user, 'workshops' => $application->admittedWorkshops, 'newline' => true])
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="row">
                <div class="col">
                    <p><b>Összesen:</b></p>
                    <ul>
                        <li><b>Felvett</b>: {{ $admitted_applications->count() }}</li>
                        <li><b>Bentlakó</b>: {{ $admitted_applications->where('admitted_for_resident_status', true)->count() }}</li>
                        <li><b>Bejáró</b>: {{ $admitted_applications->where('admitted_for_resident_status', false)->count() }}</li>
                        @foreach($admitted_applications->flatMap(fn ($a) => $a->admittedWorkshops)->countBy(fn ($w) => $w->name) as $workshop => $count)
                            <li><b>{{ $workshop }}</b>: {{ $count }} </li>
                        @endforeach
                    </ul>
                </div>
            </div>
            <div class="row">
                <div class="col">
                    <blockquote>Ezzel együtt minden más felvételiző (az újrafelvételizők mellőzésével) elutasításra, anyagai törlésre, valamint az összes
                        felvételihez kapcsolódó (felvételiztető) jog elvételre kerül. Az alábbi felhasználók kerülnek törlésre:</blockquote>
                    @foreach($users_to_delete as $user)
                        <a href="{{route('admission.applicants.show', ['application' => $user->application->id])}}">
                            {{ $user->name }}
                        </a><br/>
                    @endforeach
                </div>
                <div class="col">
                    <blockquote>A művelet végrehajtása előtt ajánlott biztonsági mentést készíteni az adatbázisról. A lezárást célszerű parancssoron keresztül, a szerveren végrehajtani.</blockquote>
                    <!--<form id="finalize-application-process" method="POST"
                          action="{{route('admission.finalize')}}">
                        @csrf
                        <x-input.button class="red" text="A felvételi lezárása"/>
                    </form>-->
                </div>
            </div>
        </div>
    </div>
@endsection
