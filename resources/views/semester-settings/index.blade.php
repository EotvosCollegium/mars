@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Manage Semester Settings</h1>

    <table class="table">
        <thead>
            <tr>
                <th>Setting</th>
                <th>Value</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($settings as $setting)
            <tr>
                <td>{{ $setting->name }}</td>
                <td>{{ $setting->semester->tag }}</td>
                <td>
                    <a href="{{ route('semester_settings.edit', $setting->id) }}" class="btn btn-primary">Edit</a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection