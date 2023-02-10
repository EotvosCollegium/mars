@extends('layouts.app')

@section('title')
<a href="{{route('sittings.index')}}" class="breadcrumb" style="cursor: pointer">@lang('voting.assembly')</a>
<a href="{{route('sittings.show', $question->sitting->id)}}" class="breadcrumb" style="cursor: pointer">{{ $question->sitting->title }}</a>
<a href="#!" class="breadcrumb">{{ $question->title }}</a>
@endsection
@section('student_council_module') active @endsection

@section('content')

<div class="row">
    <div class="col s12">
        <div class="card">
            <div class="card-content">
                <span class="card-title">{{ $question->title }}</span>
                @cannot('viewResults', $question)
                <p>@lang('voting.only_after_closure')</p>
                @endcan
                <table>
                    <thead>
                        <tr>
                            <th>@lang('voting.options')</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($question->options as $option)
                        <tr>
                            <td>{{$option->title}}</td>
                            @can('viewResults', $question)
                            <td>{{$option->votes}}</td>
                            @endcan
                        </tr>
                        @endforeach   
                    </tbody>
                </table>
            </div>
            <div class="card-action">
                <div class="row right-align">
                    @if($question->isOpen())
                        @can('vote', $question)
                        <x-input.button href="{{ route('questions.votes.create', $question->id) }}" class="red" text="voting.voting" />
                        @endcan
                        @can('administer', \App\Models\Sitting::class)
                        <form action="{{ route('questions.close', $question->id) }}" method="POST" style="display:inline;">
                            @csrf
                            <x-input.button only-input text="voting.close_question" class="red" />
                        </form>
                        @endcan
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection