@extends('layouts.app')

@section('title')
<a href="#!" class="breadcrumb">@lang('general.student_council')</a>
<a href="{{ route('economic_committee') }}" class="breadcrumb" style="cursor: pointer">@lang('general.checkout')</a>
<a href="#!" class="breadcrumb">@lang('general.kkt_netreg')</a>
@endsection

@section('content')

@livewire('kkt-netreg')

@endsection
