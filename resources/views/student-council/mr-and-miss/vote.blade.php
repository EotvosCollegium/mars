@extends('layouts.app')

@section('title')
    <a href="#!" class="breadcrumb">@lang('role.student-council')</a>
    <a href="#!" class="breadcrumb">@lang('mr-and-miss.mr-and-miss')</a>
@endsection

@section('student_council_module')
    active
@endsection

@section('content')
    <div class="row">
        <div class="col s12">
            <div class="card">
                <div class="card-content">
                    <span class="card-title">@lang('mr-and-miss.vote')</span>
                    @can('manage', \App\Models\MrAndMiss::class)
                    <p>
                        <x-input.button :href="route('mr_and_miss.categories')" :text="__('mr-and-miss.mr-and-miss-categories')" />
                        <x-input.button :href="route('mr_and_miss.results')" :text="__('mr-and-miss.mr-and-miss-results')" />
                    </p>
                    @endcan
                    <p>@lang('mr-and-miss.vote-explanation')</p>
                    <blockquote>
                        @lang('mr-and-miss.deadline', ['deadline' => $deadline])
                    </blockquote>
                    @foreach ($errors->all() as $error)
                        <blockquote class="error">{{ $error }}</blockquote>
                    @endforeach
                </div>
            </div>
            @if ($deadline > now())
                <div class="card">
                    <div class="card-tabs">
                        <ul class="tabs tabs-fixed-width">
                            <li class="tab"><a @if (!session('activate_custom')) class="active" @endif
                                    href="#tab1">{{ $miss_first ? 'miss' : 'mr' }}</a></li>
                            <li class="tab"><a href="#tab2">{{ $miss_first ? 'mr' : 'miss' }}</a></li>
                            <li class="tab"><a @if (session('activate_custom')) class="active" @endif
                                    href="#tab3">@lang('mr-and-miss.custom')</a></li>
                        </ul>
                    </div>
                    <div class="card-content lighten-4">
                        <form action="{{ route('mr_and_miss.vote.save') }}" method="post">
                            @csrf
                            <div id="tab1">
                                @include(
                                    'student-council.mr-and-miss.votefields',
                                    [
                                        'genderCheck' => !$miss_first,
                                        'custom' => false,
                                        'votes' => $votes,
                                    ]
                                )
                            </div>
                            <div id="tab2">
                                @include(
                                    'student-council.mr-and-miss.votefields',
                                    [
                                        'genderCheck' => $miss_first,
                                        'custom' => false,
                                        'votes' => $votes,
                                    ]
                                )
                            </div>
                            <div id="tab3">
                                @include(
                                    'student-council.mr-and-miss.votefields',
                                    [
                                        'custom' => true,
                                        'votes' => $votes,
                                    ]
                                )
                            </div>
                        </form>
                    </div>
                </div>
                {{-- Custom categoories --}}
                <div class="card">
                    <div class="card-content">
                        <span class="card-title">@lang('mr-and-miss.add-custom')</span>
                        <form action="{{ route('mr_and_miss.vote.custom') }}" method="post">
                            @csrf
                            <div class="row">
                                <div class="input-field col xl3 switch s12">
                                    <label>
                                        @lang('mr-and-miss.private')
                                        <input name="is-public" type="checkbox">
                                        <span class="lever"></span>
                                        @lang('mr-and-miss.public')
                                    </label>
                                </div>
                                <div class="input-field col xl1 s4">
                                    <select name="mr-or-miss">
                                        <option value="Mr." selected>Mr.</option>
                                        <option value="Miss">Miss</option>
                                    </select>
                                </div>
                                <x-input.text s=12 xl=8 id="title" locale="mr-and-miss" />
                            </div>
                            <blockquote>
                                @lang('mr-and-miss.custom-category')
                            </blockquote>
                            <button class="btn waves-effect waves-light" type="submit">@lang('print.add')
                                <i class="material-icons right">add</i>
                            </button>
                        </form>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection


@push('scripts')
    <script>
        $('.input-changer').click(function(event) {
            event.stopImmediatePropagation();
            event.preventDefault();
            var id = event.target.dataset.number;
            var rawInput = document.getElementById("raw-" + id);
            var selectInput = document.getElementById("select-ui-" + id);
            rawInput.hidden = !rawInput.hidden;
            selectInput.hidden = !selectInput.hidden;
            event.target.innerHTML = event.target.innerHTML == "border_color" ? "clear_all" : "border_color";
        });
        $('#mr-submit').click(function(event) {
            $('.mr-textarea').each(function() {
                if (this.hidden) {
                    this.innerHTML = null
                }
            })

        })
    </script>
    <script>
        $(document).ready(function() {
            $('.tabs').tabs();
            $('select').formSelect();
        });
    </script>
@endpush
