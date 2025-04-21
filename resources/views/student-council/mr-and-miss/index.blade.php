@extends('layouts.app')

@section('title')
    <a href="#!" class="breadcrumb">Választmány</a>
    <a href="#!" class="breadcrumb">Mr. és Miss Eötvös</a>
@endsection

@section('student_council_module')
    active
@endsection

@section('content')
    <div class="row">
        <div class="col s12">
            @can('manage', \App\Models\MrAndMissVote::class)
                <p>
                    <x-input.button :href="route('mr_and_miss.admin')" text="Szerkesztés" />
                    <x-input.button :href="route('mr_and_miss.results')" text="Eredmények" />
                </p>
            @endcan
            @can('optOut', \App\Models\MrAndMissVote::class)
            <div class="card">
                <div class="card-content">
                    <span class="card-title">Mr. és Miss Eötvös részvétel</span>
                    <p>
                        Amennyiben nem szeretnél részt venni az idei Mr. és Miss Eötvösben, a szavazási időszak kezdetéig <span>({{ $start_date }})</span> a lenti gombbal jelezheted.
                        Ekkor mások nem adhatnak le rád szavazatot, és te sem tudsz szavazni.
                    </p>
                </div>
                <div class="card-action right-align">
                    <form action="{{ route('mr_and_miss.opt_out') }}" method="post">
                        <input type="hidden" name="opt_out" value="{{ $opted_out ? '0' : '1' }}">
                        @csrf
                        @if ($opted_out)
                            <button class="btn waves-effect waves-light green" type="submit">Részt veszek
                                <i class="material-icons right">check</i>
                            </button>
                        @else
                            <button class="btn waves-effect waves-light red" type="submit">Nem szeretnék részt venni
                                <i class="material-icons right">clear</i>
                            </button>
                        @endif
                    </form>
                </div>
            </div>
            @elsecan('vote', \App\Models\MrAndMissVote::class)
            <div class="card">
                <div class="card-content">
                    <span class="card-title">Szavazás</span>
                    <p>
                        Külön tudtok szavazni a Mr, a Miss, illetve az egyéni kategóriákban. Lehetőleg a keresők segítségével válasszátok ki a jelölteteket.
                        A listában azok az aktív collegisták szerepelnek, akik nem jelezték, hogy nem szeretnének részt venni a szavazásban. Ha a felsorolt lehetőségek helyett másra szavaznátok, a sor végén található gombbal szabad kezes bevitelre válthattok.
                        A határidőig bárhányszor módosíthatjátok a szavazatokat.
                    </p>
                    <blockquote>
                        A szavazás határideje: {{ $deadline }}
                    </blockquote>
                    @foreach ($errors->all() as $error)
                        <blockquote class="error">{{ $error }}</blockquote>
                    @endforeach
                </div>
            </div>
            <div class="card">
                <div class="card-tabs">
                    <ul class="tabs tabs-fixed-width">
                        <li class="tab"><a @if (!session('activate_custom')) class="active" @endif
                                href="#tab1">{{ $miss_first ? 'miss' : 'mr' }}</a></li>
                        <li class="tab"><a href="#tab2">{{ $miss_first ? 'mr' : 'miss' }}</a></li>
                        <li class="tab"><a @if (session('activate_custom')) class="active" @endif
                                href="#tab3">Egyéni kategóriák</a></li>
                    </ul>
                </div>
                <div class="card-content lighten-4">
                    <form action="{{ route('mr_and_miss.vote.save') }}" method="post">
                        @csrf
                        <div id="tab1">
                            @include(
                                'student-council.mr-and-miss.votefields',
                                ['categories' => $categories->where('mr', !$miss_first)->where('custom', false)]
                            )
                        </div>
                        <div id="tab2">
                            @include(
                                'student-council.mr-and-miss.votefields',
                                ['categories' => $categories->where('mr', $miss_first)->where('custom', false)]
                            )
                        </div>
                        <div id="tab3">
                            @include(
                                'student-council.mr-and-miss.votefields',
                                ['categories' => $categories->where('custom', true)]
                            )
                        </div>
                    </form>
                </div>
            </div>
            {{-- Custom categoories --}}
            <div class="card">
                <div class="card-content">
                    <span class="card-title">Egyéni kategória hozzáadása</span>
                    <form action="{{ route('mr_and_miss.vote.custom') }}" method="post">
                        @csrf
                        <div class="row">
                            <div class="input-field col xl3 switch s12">
                                <label>
                                    Privát
                                    <input name="is-public" type="checkbox">
                                    <span class="lever"></span>
                                    Nyilvános
                                </label>
                            </div>
                            <div class="input-field col xl1 s4">
                                <select name="mr-or-miss">
                                    <option value="Mr." selected>Mr.</option>
                                    <option value="Miss">Miss</option>
                                </select>
                            </div>
                            <x-input.text s=12 xl=8 id="title" text="kategória neve" />
                        </div>
                        <blockquote>
                            A publikus egyéni kategóriákra mások is szavazhatnak, ezek másoknál is megjelennek. Az új kategóriára szavazni majd a kategória létrehozása után tudsz.
                        </blockquote>
                        <button class="btn waves-effect waves-light" type="submit">Hozzáadás
                            <i class="material-icons right">add</i>
                        </button>
                    </form>
                </div>
            </div>
                @endcan
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
