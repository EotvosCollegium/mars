@extends('layouts.app')
@section('title')
<i class="material-icons left">lock</i>Státusz
@endsection

@section('content')
<div class="row">
    <div class="col s12">
        <div class="card">
            <div class="card-content">
                <span class="card-title">Státusz frissítése</span>
                <form action="{{ route('secretariat.status-update.update') }}" method="post">
                    @csrf
                    <div class="row">
                        @if (\Illuminate\Support\Facades\Auth::user()->hasActivated())
                            <blockquote>
                                A félévre a státuszod már be lett állítva: @lang('user.' . \Illuminate\Support\Facades\Auth::user()->getStatusIn(\App\Models\Semester::current())).
                            </blockquote>
                        @endif
                        <blockquote>
                            Hogyha vendégként vagy bentlakó-bejáróként laksz a kollégiumban, akkor a státuszod bejáró.
                        </blockquote>
                        <x-input.select s=12 :elements="['resident', 'extern']" required id="collegist_role" :formatter="function($o) { return __('role.'.$o); }" without-label placeholder="Bentlakási státusz"/>
                    </div>
                    <div class="row">
                        <blockquote>
                        Aktív - Aktív tagja leszel a Collegiumnak<br>
                        Passzív - Külföldi féléven leszel vagy passzív vagy az egyetemen<br>
                        Deaktivált - Nem leszel aktív tagja a Collégiumnak
                        </blockquote>
                        <x-input.select s=12 without_label :elements="[\App\Models\SemesterStatus::ACTIVE,\App\Models\SemesterStatus::PASSIVE, \App\Models\SemesterStatus::DEACTIVATED]" id="semester_status" required :formatter="function($o) { return __('user.'.$o); }" placeholder="Tagsági státusz"/>
                    </div>
                    <div class="row">
                        <x-input.button class="right red" text="general.save" />
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
