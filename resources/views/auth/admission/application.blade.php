<!-- Admin site showing one applicants -->
@extends('layouts.app')
@section('title')
    <a href="{{ route('admission.applicants.index') }}" class="breadcrumb" style="cursor: pointer">Felvételi jelentkezők</a>
    <a href="#!" class="breadcrumb">{{$user->name}}</a>
@endsection

@section('content')
    @include('auth.application.application', ['user' => $user, 'expanded' => true, 'admin' => $admin ?? false])
    <div class="card">
        <form method="POST" route="{{route('admission.applicants.edit', ['application' => $user->application->id])}}">
            <div class="card-content">
                <div class="row">
                    @csrf
                    <x-input.textarea id="note"
                                      text="Megjegyzés"
                                      helper="A megjegyzéseket a felvételiző nem látja, de azok láthatóak a többi felvételiztető számára (akár más műhelyekből is)."
                                      :value="$user->application->note"/>
                </div>
                <x-input.button floating class="right" icon="save"/>
            </div>
        </form>
    </div>
@endsection
