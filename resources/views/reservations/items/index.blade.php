@extends('layouts.app')

@section('title')
<a href="#!" class="breadcrumb">@lang('reservations.items')</a>
@endsection

@section('content')

<div class="row">
    <div class="col s12">
        <div class="card">
            <div class="card-content">
                <span class="card-title">@lang('reservations.items')</span>
                <table>
                    <thead>
                        <tr>
                            <th>@lang('reservations.item_status')</th>
                            <th>@lang('reservations.item_name')</th>
                            <th>@lang('reservations.out_of_order_from')</th>
                            <th>@lang('reservations.out_of_order_until')</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($items as $item)
                        <tr>
                            <td>
                                @if($item->isOutOfOrder())
                                <span class="new badge grey" data-badge-caption="">@lang('reservations.out_of_order')</span>
                                @elseif($item->isFree())
                                <span class="new badge green" data-badge-caption="">@lang('reservations.is_free')</span>
                                @else
                                <span class="new badge red" data-badge-caption="">@lang('reservations.is_occupied')</span>
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
