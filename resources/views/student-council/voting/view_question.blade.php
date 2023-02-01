@extends('layouts.app')

@section('title')
<a href="{{route('voting')}}" class="breadcrumb">@lang('voting.assembly')</a>
<a href="{{route('voting.view_sitting', $question->sitting()->id)}}" class="breadcrumb">{{ $question->sitting()->title }}</a>
<a href="#!" class="breadcrumb">{{ $question->title }}</a>
@endsection
@section('admin_module') active @endsection

@section('content')

<div class="row">
    <div class="col s12">
        <div class="card">
            <div class="card-content">
                <span class="card-title">{{ $question->title }}</span>
                @cannot('view_results', $question)
                <p>@lang('voting.only_after_closure')</p>
                @endcan
                <table>
                    <thead>
                        <tr>
                            <th>@lang('voting.options')</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($question->getOptions() as $option)
                        <tr>
                            <td>{{$option->title}}</td>
                            @can('view_results', $question)
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
                    <form action="{{ route('voting.close_question', $question->id) }}" method="POST" class="right" style="margin-right:10px">
                        @csrf
                        <x-input.button text="voting.close_question" class="red" />
                    </form>
                    @endcan
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection