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

                <blockquote>
                    @lang('reservations.item_index_instructions')
                </blockquote>

                @include('reservations.item_table')
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
