@extends('layouts.app')

@section('title')
<a href="{{route('sittings.index')}}" class="breadcrumb">@lang('voting.assembly')</a>
<a href="{{route('sittings.show', $question->sitting->id)}}" class="breadcrumb">{{ $question->sitting->title }}</a>
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
                        @foreach($question->options()->get() as $option)
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
                <div class="row" style="margin-bottom: 0">
                    @if($question->isOpen())
                    @can('administer', \App\Models\Sitting::class)
                    <form action="{{ route('questions.close', $question->id) }}" method="POST" class="right" style="margin-right:10px">
                        @csrf
                        <x-input.button text="voting.close_question" class="red" />
                    </form>
                    @endcan
                    @endif

                    @can('vote', $question)
                    <a href="{{ route('questions.votes.create', $question->id) }}" class="right">
                        <x-input.button text="voting.voting" class="red" />
                    </a>
                    @endcan
                </div>
            </div>
        </div>
    </div>
</div>
@endsection