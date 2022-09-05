@extends('layouts.app')
@section('title')
<i class="material-icons left">lock</i>@lang('secretariat.status_update')
@endsection

@section('content')
<div class="row">
    <div class="col s12">
        <div class="card">
            <div class="card-content">
                <span class="card-title">@lang('secretariat.status')</span>
                <form action="{{ route('secretariat.status-update.update') }}" method="post">
                    @csrf
                    <div class="row">
                        <blockquote>
                            @lang('secretariat.collegist_role_info')
                        </blockquote>
                        <x-input.select s=12 :elements="['resident', 'extern']" required id="collegist_role" :formatter="function($o) { return __('role.'.$o); }" without-label :placeholder="__('secretariat.collegist_status')"/>
                    </div>
                    <div class="row">
                        <blockquote>
                            @lang('secretariat.semester_status_info')
                        </blockquote>
                        <x-input.select s=12 without_label :elements="[\App\Models\SemesterStatus::ACTIVE,\App\Models\SemesterStatus::PASSIVE, \App\Models\SemesterStatus::DEACTIVATED]" id="semester_status" required :formatter="function($o) { return __('user.'.$o); }" :placeholder="__('secretariat.semester_status')"/>
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