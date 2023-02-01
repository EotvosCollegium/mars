@extends('layouts.app')

@section('title')
<a href="#!" class="breadcrumb">@lang('voting.assembly')</a>
@endsection
@section('admin_module') active @endsection

@section('content')

<div class="row">
    <div class="col s12">
        <div class="card">
            <div class="card-content">
                <span class="card-title">@lang('voting.sittings')</span>
                <table>
                    <thead>
                        <tr>
                            <th>@lang('voting.name')</th>
                            <th>@lang('voting.opened_at')</th>
                            <th>@lang('voting.closed_at')</th>
                            <th>@lang('voting.is_open')</th>
                            
                            @can('administer', \App\Models\Sitting::class)
                                <th>
                                    <a href="{{ route('voting.new_sitting') }}" class="btn-floating waves-effect waves-light right">
                                        <i class="material-icons">add</i>
                                    </a>
                                </th>
                            @endcan
                            
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($sittings as $sitting)
                        <tr>
                            <td>
                                {{ $sitting->title }}
                            </td>
                            <td>
                                {{ $sitting->opened_at }}
                            </td>
                            <td>
                                {{ $sitting->closed_at }}
                            </td>
                            <td>
                                @if($sitting->isOpen())
                                    <span class="new badge green" data-badge-caption="">@lang('voting.open')</span>
                                @else
                                    <span class="new badge red" data-badge-caption="">@lang('voting.closed')</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('voting.view_sitting', $sitting->id) }}" class="btn-floating waves-effect waves-light right">
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