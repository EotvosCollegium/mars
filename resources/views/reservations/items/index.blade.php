@extends('layouts.app')

@section('title')
<a href="#!" class="breadcrumb">@lang($title)</a>
@endsection
@section('student_council_module') active @endsection

@section('content')

<div class="row">
    <div class="col s12">
        <div class="card">
            <div class="card-content">
                <span class="card-title">@lang($title)</span>
                <table>
                    <thead>
                        <tr>
                            <th></th> {{-- for a colored dot indicating whether it works --}}
                            <th>@lang('reservations.name')</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($items as $item)
                        <tr>
                            <td>
                                @if($item->isFree())
                                <span class="new badge green" data-badge-caption="">@lang('reservations.free')</span>
                                @else
                                <span class="new badge red" data-badge-caption="">@lang('reservations.occupied')</span>
                                @endif
                            </td>
                            <td>
                                {{ $item->name }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function(){
            $('.tooltipped').tooltip();
        });
    </script>
@endpush
