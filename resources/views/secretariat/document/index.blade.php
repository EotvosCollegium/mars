@extends('layouts.app')

@section('title')
<i class="material-icons left">assignment</i>Dokumentumok
@endsection

@section('content')

@php
    $currentUser = Auth::user();
@endphp

<div class="row">
    <div class="col s12">
        <div class="card">
            <div class="card-content">
                <span class="card-title">Dokumentumok</span>
                @include("secretariat.document.printing_info")
                <blockquote>
                    @if($currentUser->hasRole(\App\Models\Role::SECRETARY))
                    Az alábbi paneleken a különböző félévekben beérkezett,
                    tevékenységekre vonatkozó igazolási kérelmek szerepelnek.<br/>
                    Ha jóváhagy egy tevékenységet,
                    a jóváhagyás után megjelenik egy 'Letöltés' gomb,
                    amivel PDF-formátumú igazolást lehet generálni.
                    @else
                    Igazolásokat a titkárságtól tudsz igényelni az "Igénylés" gombra kattintva, erről a titkárság értesítést kap. Az igazolásokat általában a következő munkanapon veheted át. Csak az aláírt és lepecsételt igazolások érvényesek! Az igazolásokat jellemzően a titkárság nyomtatja.
                    @endif
                </blockquote>
                {{-- TODO: show printing errors --}}
                <table>
                    <tbody>
                        @can('document.register-statement')
                        <tr>
                            <td>
                                {{__("document.register-statement")}}
                            </td>
                            <td>
                            </td>
                            <td>
                                <x-input.button :href="route('documents.register-statement.download')" text="document.download" />
                            </td>
                            @if($printing_available)
                                <td>
                                    <form method="post" action="{{route('documents.register-statement.print')}}">
                                        @csrf
                                        <x-input.button type="submit" class="coli blue" text="print.print" />
                                    </form>
                                </td>
                            @endif
                        </tr>
                        @endcan
                        @can('document.departure-statement')
                        <tr>
                            <td>
                                {{__("document.departure-statement")}}
                            </td>
                            <td>
                            </td>
                            <td>
                                <x-input.button :href="route('documents.departure-statement.download')" text="document.download" />
                            </td>
                            @if($printing_available)
                                <td>
                                    <form method="post" action="{{route('documents.departure-statement.print')}}">
                                        @csrf
                                        <x-input.button type="submit" class="coli blue" text="print.print" />
                                    </form>
                                </td>
                            @endif
                        </tr>
                        @endcan
                        @can('document.import-license')
                        <tr>
                            <td>
                                {{__("document.import")}}
                            </td>
                            <td>
                                <x-input.button :href="route('documents.import.show')" text="document.fill" />
                            </td>
                            <td>
                                <x-input.button :href="route('documents.import.download')" text="document.download" />
                            </td>
                            @if($printing_available)
                                <td>
                                    <form method="post" action="{{route('documents.import.print')}}">
                                        @csrf
                                        <x-input.button type="submit" class="coli blue" text="print.print" />
                                    </form>
                                </td>
                            @endif
                        </tr>
                        @endcan
                        @can('document.status-certificate')
                        <tr>
                            <td>
                                {{__("document.status-cert")}}
                            </td>
                            <td>
                                <form method="post" action="{{route('documents.status-cert.request')}}">
                                    @csrf
                                    <x-input.button type="submit" class="coli blue" text="document.request" />
                                </form>
                            </td>
                            <td>
                                <x-input.button :href="route('documents.status-cert.download')" text="document.download" />
                            </td>
                            @if($printing_available)
                                <td>
                                    <form method="post" action="{{route('documents.status-cert.print')}}">
                                        @csrf
                                        <x-input.button type="submit" class="coli blue" text="print.print" />
                                    </form>
                                </td>
                            @endif
                        </tr>
                        @endcan
                    </tbody>
                </table>
            </div>
        </div>
        @if($currentUser->hasRole(\App\Models\Role::SECRETARY))
        @include('student-council.community-service.table')
        @elseif($currentUser->can('create', \App\Models\CommunityService::class))
        @include('student-council.community-service.request', ['isForSecretariat' => true])
        @endif
    </div>
</div>
@endsection
