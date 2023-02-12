@extends('layouts.app')

@section('title')
<a href="#!" class="breadcrumb">Választmány</a>
<a href="{{route('community_service')}}" style="cursor: pointer" class="breadcrumb">Közösségi tevékenység</a>
<a href="#!" class="breadcrumb">{{$selectedUser->name??"Felhasználó keresése"}}</a>
@endsection

@section('student_council_module') active @endsection

@section('content')
<div class="card">
    <div class="card-content">
        <span class="card-title">Felhasználó keresése</span>
        <blockquote>Keresd meg egy collegista közösségi tevékenységeit</blockquote>
        <form action={{ route('community_service.search')}} method="GET">
            <div class="row center">
                <x-input.select m=12 l=12 id="requester" :elements="\App\Models\User::active()->get()" text="kérvényező" :default="$selectedUser->id??null"/>
                <x-input.button text="keresés"/>
            </div>
        </form>
    </div>
</div>

@include('student-council.community-service.table', ['showApprove' => false])

@endsection