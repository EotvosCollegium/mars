@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Edit semester setting ({{$setting->name}})</h2>

    <form method="POST" action="{{ route('semester_settings.update', $setting) }}">
        @csrf
        @method('PUT')

        <div class="form-group">
            <label for="value">Value</label>

            <x-input.select m="3" id="semester_id" :elements="\App\Models\Semester::all()" :value="$setting->semester_id" :default="\App\Models\Semester::current()->id" helper="Szemeszter"/>
        </div>

        <button type="submit" class="btn btn-success">Save</button>
    </form>
</div>
@endsection