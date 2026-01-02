@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Epistola list</h1>
    <form method="GET" action="{{ route('epistola.preview') }}">
        <div class="mb-3">
            <label for="preview_date">Preview date</label>
            <input type="date" id="preview_date" name="date" value="{{ now()->format('Y-m-d') }}" class="form-control" />
        </div>

        <table class="table table-striped">
            <thead>
                <tr>
                    <th></th>
                    <th>Title</th>
                    <th>Subtitle</th>
                </tr>
            </thead>
            <tbody>
                @foreach($epistolas as $ep)
                    <tr>
                        <td>
                            <x-input.checkbox
                                        only-input
                                        :id="'ids_'.$ep->id"
                                        :value="$ep->id"
                                        name="ids[]"
                                        text=""
                                    />
                        </td>
                        <td>{{ $ep->title }}</td>
                        <td>{{ $ep->subtitle }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <button class="btn btn-primary" type="submit">Preview selected</button>
    </form>
</div>
@endsection
