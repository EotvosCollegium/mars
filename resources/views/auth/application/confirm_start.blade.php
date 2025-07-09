@extends('layouts.app')

@section('content')
    <div class="card">
        <div class="card-content">
            <div class="markdown_with_red">
                @markdown(\App\Models\ConfigurableText::getText("START_APPLICATION"))
            </div>
            <form method="POST" action="{{ route('application.confirm_start') }}">
                @csrf
                <button type="submit" class="btn btn-primary">Igen</button>
            </form>
        </div>
    </div>
@endsection
