@extends('layouts.app')

@section('title')
<a href="#!" class="breadcrumb">Admin</a>
<a href="#!" class="breadcrumb">@lang('general.feature_configuration')</a>
@endsection
@section('admin_module') active @endsection

@section('content')
<div class="row">
    <div class="col s12">
        <div class="card">
            <div class="card-content">
                <span class="card-title">@lang('general.feature_configuration')</span>
                <blockquote>@lang('feature.feature_descr')</blockquote>
                <blockquote style="color:red">@lang('feature.feature_warn')</blockquote>
                <table>
                    <thead>
                        <tr>
                            <th>@lang('feature.name')</th>
                            <th>@lang('feature.description')</th>
                            <th>@lang('feature.state')</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($features as $feature)
                        <tr>
                            <td>
                                {{ $feature->name }}
                            </td>
                            <td>
                                {{ $feature->description }}
                            </td>
                            <td>
                                @if($feature->enabled)
                                    <x-input.button class="coli green" text="feature.enabled" />
                                    <form method="post" action="{{ route('feature.update', $feature) }}" enctype="multipart/form-data">
                                        @csrf
                                        @method('PUT')
                                        <input type="checkbox" id="enabled" name="enabled" value="enabled">
                                        <x-input.button class="coli blue" text="feature.disable" />
                                    </form>
                                @else
                                    <x-input.button class="coli red" text="feature.disabled" />
                                    <form method="post" action="{{ route('feature.update', $feature) }}" enctype="multipart/form-data">
                                        @csrf
                                        @method('PUT')
                                        <input type="checkbox" id="enabled" name="enabled" value="enabled" checked>
                                        <x-input.button class="coli blue" text="feature.enable" />
                                    </form>
                                @endif
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
