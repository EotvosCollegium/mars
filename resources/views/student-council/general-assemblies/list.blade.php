@extends('layouts.app')

@section('title')
<a href="#!" class="breadcrumb">@lang('voting.assembly')</a>
@endsection
@section('student_council_module') active @endsection

@section('content')

<div class="row">
    <div class="col s12">
        <div class="card">
            <div class="card-content">
                <span class="card-title">@lang('voting.general_assemblies')</span>
                <table>
                    <thead>
                        <tr>
                            <th>@lang('voting.name')</th>
                            <th>@lang('voting.opened_at')</th>
                            <th>@lang('voting.closed_at')</th>
                            <th>
                            @can('administer', \App\Models\GeneralAssembly::class)
                                <a href="{{ route('general_assemblies.create') }}" class="btn-floating waves-effect waves-light right">
                                    <i class="material-icons">add</i>
                                </a>
                            @endcan
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($general_assemblies as $general_assembly)
                        <tr>
                            <td>
                                {{ $general_assembly->title }}
                            </td>
                            <td>
                                {{ $general_assembly->opened_at }}
                            </td>
                            <td>
                                {{ $general_assembly->closed_at }}
                                @if($general_assembly->isOpen())
                                <span class="new badge green" data-badge-caption="">@lang('voting.open')</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('general_assemblies.show', $general_assembly->id) }}" class="btn-floating waves-effect waves-light right">
                                    <i class="material-icons">remove_red_eye</i>
                                </a>
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
