@extends('layouts.app')

@section('title')
<a href="#!" class="breadcrumb">Választmány</a>
<a href="#!" class="breadcrumb">Közösségi tevékenység</a>
@endsection

@section('student_council_module') active @endsection

@section('content')

@can('create', \App\Models\CommunityService::class)
@include('student-council.community-service.request', ['isForSecretariat' => false])
@endcan


@can('approveAny', \App\Models\CommunityService::class)
<div class="card">
    <div class="card-content">
        <span class="card-title">Felhasználó keresése</span>
        <blockquote>Keresd meg egy collegista közösségi tevékenységeit</blockquote>
        <div class="row center">
        <x-input.button text="general.search" :href="route('community_service.search')"></x-input.button>
        </div>
    </div>
</div>
@endcan


@include('student-council.community-service.table', ['showApprove' => true])

@endsection
