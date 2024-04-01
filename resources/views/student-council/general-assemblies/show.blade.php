@extends('layouts.app')

@section('title')
<a href="{{ route('general_assemblies.index') }}" class="breadcrumb" style="cursor: pointer">@lang('voting.assembly')</a>
<a href="#!" class="breadcrumb" style="cursor: pointer">{{ $general_assembly->title }}</a>
@endsection
@section('student_council_module') active @endsection

@section('content')

<div class="row">
    <div class="col s12">
        <div class="card">
            <div class="card-content">
                <span class="card-title">{{ $general_assembly->title }}
                    @if($general_assembly->isOpen())
                    <span class="right">
                        <a href="{{ route('general_assemblies.show_code', $general_assembly) }}">
                            @livewire('passcode', ['isFullscreen' => false])
                        </a>
                    </span>
                    @endif
                </span>
                <table>
                    <tbody>
                        <tr>
                            <th scope="row">@lang('voting.opened_at')</th>
                            <td>{{ $general_assembly->opened_at }}

                                @can('administer', $general_assembly)
                                @if(!$general_assembly->hasBeenOpened())
                                <form action="{{ route('general_assemblies.open', $general_assembly->id) }}" method="POST">
                                    @csrf
                                    <x-input.button text="voting.open_sitting" class="green" />
                                </form>
                                @endif
                                @endcan
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">@lang('voting.closed_at')</th>
                            <td>{{ $general_assembly->closed_at }}

                                @can('administer', $general_assembly)
                                @if($general_assembly->isOpen())
                                    <form action="{{ route('general_assemblies.close', $general_assembly->id) }}" method="POST">
                                        @csrf
                                        <x-input.button text="voting.close_sitting" class="red" />
                                    </form>
                                @endif
                                @endcan
                            </td>
                        </tr>
                        <tr>
                            @php
                                $attendees = $general_assembly->attendees();
                            @endphp
                            <th scope="row">@lang('voting.attendees') ({{$attendees->count()}} fő)*</th>
                            <td>
                                <ul>
                                @foreach ($attendees->sortBy('name') as $attendee)
                                    <li>{{ $attendee->uniqueName }}</li>
                                @endforeach
                                </ul>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <blockquote>
                    * Résztvevőnek számít az, aki legfeljebb 2 jelenlét-ellenőrzésen nem vett részt (amennyiben összesen legfeljebb 2 volt, úgy az összes jelenlét-ellenőrzésen részt vettek számítanak). Csak aktív státuszú collegisták szavazhatnak.
                </blockquote>
                @if(!Auth::user()->isActive())
                <blockquote class="red-text">@lang('voting.not_active')</blockquote>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col s12">
        <div class="card">
            <div class="card-content">
                <span class="card-title">@lang('voting.questions')</span>
                <table>
                    <thead>
                    <tr>
                        <th>@lang('voting.question_title')</th>
                        <th>@lang('voting.opened_at')</th>
                        <th>@lang('voting.closed_at')</th>
                        <th>
                            @if(!$general_assembly->isClosed())
                            @can('administer', $general_assembly)
                            <x-input.button href="{{ route('general_assemblies.questions.create', ['general_assembly' => $general_assembly]) }}" floating class="right" icon="add" />
                            @endcan
                            @endif
                        </th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($general_assembly->questions()->orderByDesc('opened_at')->get() as $question)
                    <tr>
                        <td>{{$question->title}}</td>
                        <td>
                            {{$question->opened_at}}
                            @if(!$question->hasBeenOpened() && $general_assembly->isOpen())
                            @can('administer', $general_assembly)
                            <form action="{{ route('general_assemblies.questions.open', [
                                "general_assembly" => $general_assembly->id,
                                "question" => $question->id,
                            ]) }}" method="POST">
                                @csrf
                                <x-input.button text="voting.open_question" class="green" />
                            </form>
                            @endcan
                            @endif
                        </td>
                        <td>
                            {{$question->closed_at}}
                            @if($question->isOpen())
                            @can('administer', $general_assembly)
                            <form action="{{ route('general_assemblies.questions.close', [
                                "general_assembly" => $general_assembly->id,
                                "question" => $question->id,
                            ]) }}" method="POST">
                                @csrf
                                <x-input.button text="voting.close_question" class="red" />
                            </form>
                            @endcan
                            @endif
                        </td>
                        <td>
                            @php
                                $route = route('general_assemblies.questions.show', [
                                        "general_assembly" => $general_assembly->id,
                                        "question" => $question->id,
                                ]);
                            @endphp
                            @can('vote', $question)
                            <x-input.button href="{{ $route }}" floating class="right" icon="thumbs_up_down" />
                            @elsecan('viewResults', $question)
                            <x-input.button href="{{ $route }}" floating class="right" icon="remove_red_eye" />
                            @endcan
                        </td>
                    </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col s12">
        <div class="card">
            <div class="card-content">
                <span class="card-title">@lang('voting.presence_checks')</span>
                <table>
                    <thead>
                    <tr>
                        <th>@lang('voting.presence_note')</th>
                        <th>@lang('voting.opened_at')</th>
                        <th>@lang('voting.closed_at')</th>
                        <th>
                            @if($general_assembly->isOpen())
                            @can('administer', $general_assembly)
                            <x-input.button href="{{ route('general_assemblies.presence_checks.create', ['general_assembly' => $general_assembly]) }}" floating class="right" icon="add" />
                            @endcan
                            @endif
                        </th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($general_assembly->presenceChecks()->orderByDesc('opened_at')->get() as $presence_check)
                    <tr>
                        <td>{{$presence_check->title}}</td>
                        <td>{{$presence_check->opened_at}}</td>
                        <td>
                            {{$presence_check->closed_at}}
                            @if($presence_check->isOpen())
                            @can('administer', $general_assembly)
                            <form action="{{ route('general_assemblies.presence_checks.close', [
                                "general_assembly" => $general_assembly->id,
                                "presence_check" => $presence_check->id,
                            ]) }}" method="POST">
                                @csrf
                                <x-input.button text="voting.close_presence_check" class="red" />
                            </form>
                            @endcan
                            @endif
                        </td>
                        <td>
                            @php
                                $route = route('general_assemblies.presence_checks.show', [
                                        "general_assembly" => $general_assembly->id,
                                        "presence_check" => $presence_check->id,
                                ]);
                            @endphp
                            @can('signPresence', $presence_check)
                            <x-input.button href="{{ $route }}" floating class="right" icon="thumbs_up_down" />
                            @elsecan('viewResults', $presence_check)
                            <x-input.button href="{{ $route }}" floating class="right" icon="remove_red_eye" />
                            @endcan
                        </td>
                    </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@can('administer', $general_assembly)
    @livewire('excused-users', ['general_assembly' => $general_assembly])
@endcan
@endsection
