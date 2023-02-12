@extends('layouts.app')

@section('title')
<a href="#!" class="breadcrumb">Választmány</a>
<a href="#!" class="breadcrumb">Közösségi tevékenység</a>
@endsection

@section('student_council_module') active @endsection

@section('content')

@can('create', \App\Models\CommunityService::class)
<div class="card">
    <div class="card-content">
        <span class="card-title">Új közösségi tevékenység hozzáadása</span>
        <blockquote>A választott jóváhagyó e-mailben fog értesítést kapni a kérelemről</blockquote>
        <form method="POST" action="{{ route('community_service.create') }}">
            @csrf
            <div class="row">
                <x-input.text m=6 l=6 id="description" required text="leírás" />
                <x-input.select m=6 l=6 id="approver" :elements="$possible_approvers" text="jóváhagyó"/>
            </div>
            <x-input.button floating class="btn=large right" icon="send" />
        </form>
    </div>
</div>
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