<!-- Admin site showing one applicants -->
@extends('layouts.app')
@section('title')
    <a href="{{ route('applications') }}" class="breadcrumb" style="cursor: pointer">Felvételi jelentkezők</a>
    <a href="#!" class="breadcrumb">{{$user->name}}</a>
@endsection

@section('content')
    @include('auth.application.application', ['user' => $user, 'expanded' => true])
    <div class="card">
        <div class="card-content">
            <div class="row">
                <x-input.textarea id="note" text="Megjegyzés" helper="A megjegyzéseket a felvételiző nem látja."/>
            </div>
        </div>
    </div>
@endsection
