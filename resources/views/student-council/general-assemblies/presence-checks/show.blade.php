@extends('layouts.app')

@section('title')
<a href="{{route('general_assemblies.index')}}" class="breadcrumb" style="cursor: pointer">@lang('voting.assembly')</a>
<a href="{{route('general_assemblies.show', $general_assembly->id)}}" class="breadcrumb" style="cursor: pointer">{{ $general_assembly->title }}</a>
<a href="#!" class="breadcrumb">{{ $presence_check->title }}</a>
@endsection
@section('student_council_module') active @endsection

@section('content')

<div class="row">
    @can('signPresence', $presence_check)
    <div class="col s12">
        <div class="card">
            <form method="POST" action="{{ route('general_assemblies.presence_checks.presence.store', [
                "general_assembly" => $general_assembly->id,
                "presence_check" => $presence_check->id,
            ])}}">
                @csrf
                <div class="card-content">
                    <span class="card-title">
                        {{ $presence_check->title }}
                        <span class="right">
                            @livewire('passcode', ['isFullscreen' => false])
                        </span>
                    </span>
                    <div class="row">
                        <x-input.text id="passcode" text="voting.passcode" required />
                    </div>
                </div>
                <div class="card-action row">
                    <x-input.button class="right" text="voting.submit_presence_check"/>
                </div>
            </form>
        </div>
    </div>
    @endcan
    @can('viewResults', $presence_check)
    <div class="col s12">
        <ul class="collapsible">
            <li @if($presence_check->isClosed()) class="active" @endif>
                <div class="collapsible-header">
                    <b>@lang('voting.results')</b>
                </div>
                <div class="collapsible-body">
                    <table>
                        <thead>
                            <tr>
                                <th>{{ $presence_check->title }}</th>
                                <th>{{ $presence_check->users()->count() }}</th>
                            </tr>
                        </thead>
                    </table>
                    <blockquote>
                        <b>Itt voltak:</b>
                        <ul>
                        @foreach($presence_check->users()->orderBy('name')->get() as $user)
                            <li>{{$user->uniqueName}}</li>
                        @endforeach
                        </ul>
                    </blockquote>
                    @if($presence_check->isOpen())
                        @can('administer', \App\Models\GeneralAssemblies\GeneralAssembly::class)
                        <form action="{{ route('general_assemblies.presence_checks.close', [
                            "general_assembly" => $general_assembly->id,
                            "presence_check" => $presence_check->id,
                        ]) }}" method="POST" style="display:inline;">
                            @csrf
                            <x-input.button only-input text="voting.close_presence_check" class="red" />
                        </form>
                        @endcan
                    @endif
                </div>
            </li>
        </ul>
    </div>
    @endcan
</div>
@endsection
