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
                            <th>@lang('reservations.out_of_order_from')</th>
                            <th>@lang('reservations.out_of_order_until')</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($items as $item)
                        <tr>
                            <td>
                                @if($item->isFree())
                                <span class="new badge green" data-badge-caption="">@lang('reservations.free')</span>
                                @elseif($item->isOutOfOrder())
                                <span class="new badge grey" data-badge-caption="">@lang('reservations.out_of_order')</span>
                                @else
                                <span class="new badge red" data-badge-caption="">@lang('reservations.occupied')</span>
                                @endif
                            </td>
                            <td>
                                {{ $item->name }}
                            </td>
                            <td>
                                {{ $item->out_of_order_from }}
                            </td>
                            <td>
                                {{ $item->out_of_order_until }}
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
