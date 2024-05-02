@extends('layouts.app')

@section('title')
<a href="#!" class="breadcrumb">ez egyszer jó lesz</a>
@endsection

@section('content')

<div class="row">
    <div class="col s12">
        <div class="card">
            <div class="card-content">
                <span class="card-title">{{$reservation->displayName()}}</span>
                <p>Ez egy jó foglalás.</p>
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
