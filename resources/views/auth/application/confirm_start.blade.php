@extends('layouts.app')

@section('content')
    <div class="card">
        <div class="card-content">
            <h3>Biztos el akarod kezdeni a felvételi folyamatot?</h3>
            <form method="POST" action="{{ route('application.confirm_start') }}">
                @csrf
                <button type="submit" class="btn btn-primary">Igen</button>
            </form>
        </div>
    </div>
@endsection
