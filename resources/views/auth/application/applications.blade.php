<!-- Admin site showing all applicants -->
@extends('layouts.app')
@section('title')
    <a href="#!" class="breadcrumb">Felvételi jelentkezők</a>
@endsection

@section('content')
    <form id="workshop-filter" method="GET" route="{{route('applications')}}">

    <div class="card">
        <div class="card-content">
            <div class="row" style="margin-bottom: 0">
                <x-input.select id="workshop" :elements="$workshops" allow-empty :default="$workshop" text="Műhely" />
                @if(session()->has('can_filter_by_status'))
                    <div class="col">
                        @foreach (\App\Models\ApplicationForm::STATUSES as $st)
                            <label>
                                <input type="radio" name="status" value="{{$st}}" @if($status == $st) checked @endif">
                                <span>@include('auth.application.status', ['status' => $st, 'admin' => true])</span>
                            </label>
                        @endforeach
                    </div>
                @endif
            </div>

                @push('scripts')
                    {{--Submit form on change --}}
                    <script>
                        $(document).ready(function() {
                            $('#workshop').change(function () {
                                $('#workshop-filter').submit();
                            });
                            $("input[name='status']").change(function () {
                                $('#workshop-filter').submit();
                            });
                        });
                    </script>
                @endpush
        </div>
    </div>
    </form>


    @foreach($applications as $application)
    <!-- Todo hire/reject -->
    <a href="{{route('applications', ['id' => $application->user_id])}}">
    @include('auth.application.application', ['user' => $application->user, 'expanded' => false])
    </a>
@endforeach
    <hr>
    <h6>Összesen: <b class="right">{{$applications->count()}} jelentkező</b></h6>


@endsection
