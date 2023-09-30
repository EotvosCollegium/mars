@extends('layouts.app')

@section('title')
<a href="{{ route('general_assemblies.index') }}" class="breadcrumb" style="cursor: pointer">@lang('voting.assembly')</a>
<a href="{{ route('general_assemblies.show', $general_assembly) }}" class="breadcrumb" style="cursor: pointer">{{ $general_assembly->title }}</a>
<a href="#!" class="breadcrumb" style="cursor: pointer">@lang('voting.code')</a>
@endsection
@section('student_council_module') active @endsection

@section('content')

<div class="row">
    <div class="col s12">
        <div class="card">
            <div class="card-content">
                <span class="card-title">{{ $general_assembly->title }}</span>
                    @if($general_assembly->isOpen())
                    {{-- the @can is already in the livewire --}}
                        @livewire('passcode', ['isFullscreen' => true])
                    @endif
            </div>
        </div>
    </div>
</div>

@endsection
