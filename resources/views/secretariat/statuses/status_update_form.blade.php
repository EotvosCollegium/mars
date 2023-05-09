@extends('layouts.app')
@section('title')
<i class="material-icons left">lock</i>Státusz
@endsection

@section('content')
<div class="row">
    <div class="col s12">
        <div class="card">
            <div class="card-content">
                <span class="card-title">Nyilatkozz a következő félévedről!</span>
                <form action="{{ route('secretariat.status-update.update') }}" method="post">
                    @csrf
                        @if(user()->isResident())
                        <blockquote>A jelenlegi bentlakási státuszod: <span class="coli-text text-blue">bentlakó</span>.</blockquote>
                        <div class="row">
                            <x-input.checkbox s=12 id="resign_residency" text="A továbbiakban lemondok bentlakó helyemről, bejáró leszek." />
                        </div>
                        @else 
                        <blockquote>A jelenlegi bentlakási státuszod: <span class="coli-text text-blue">bejáró</span>.</blockquote>
                        @endif
                    
                        Aktív - Aktív tagja leszel a Collegiumnak.<br>
                        Passzív - Külföldi féléven leszel vagy passzív vagy az egyetemen. A collegista státuszod megmarad, de ha bentlakó vagy, a helyed ideiglenesen megszűnik.<br>
                        Alumni - Kilépsz a Collegiumból, vagy megszűnik a hallgatói jogviszonyod.<br>
                        Egyéb esetben írj kérvényt az Igazgató Úrnak.
                    <div class="row">
                        <x-input.select xl=6 without_label :elements="[\App\Models\SemesterStatus::ACTIVE,\App\Models\SemesterStatus::PASSIVE, \App\Models\Role::ALUMNI]" id="semester_status" required :formatter="function($o) { return __('user.'.$o); }" placeholder="Tagsági státusz"/>
                        <x-input.text xl=6 id="comment" placeholder="Megjegyzés: BB/Erasmus/két képzés között/stb." maxlength="20"/>
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
