@extends('layouts.app')

@section('title')
<i class="material-icons left">assignment</i>Dokumentumok
@endsection

@section('content')

<div class="row">
    <div class="col s12">
        <div class="card">
            <div class="card-content">
                <span class="card-title">Dokumentumok</span>
                <blockquote>Nyomtasd ki a kívánt dokumentumot a Collegiumban kihelyezett nyomtatóval, és add le a titkárságon/portán. A költségek levonásra kerülnek.</blockquote>
                <blockquote>Igazolásokat a titkárságtól tudsz igényelni az "Igénylés" gombra kattintva, erről a titkárság értesítést kap. Az igazolásokat a következő munkanapon veheted át. Csak az aláírt és lepecsételt igazolások érvényesek!</blockquote>
                {{-- TODO: show printing errors --}}
                <table>
                    <tbody>
                        @can('document.register-statement')
                        <tr>
                            <td>Beköltözési nyilatkozat</td>
                            <td></td>
                            <td>
                                <x-input.button :href="route('documents.register-statement.download')" text="letöltés" />
                            </td>
                            <td>
                                <x-input.button :href="route('documents.register-statement.print')" class="coli blue" text="nyomtatás" />
                            </td>
                        </tr>
                        @endcan
                        @can('document.import-license')
                        <tr>
                            <td>Behozatali engedély</td>
                            <td>
                                <x-input.button :href="route('documents.import.show')" text="kitöltés" />
                            </td>
                            <td>
                                <x-input.button :href="route('documents.import.download')" text="nyomtatás" />
                            </td>
                            <td>
                                <x-input.button :href="route('documents.import.print')" class="coli blue" text="document.print" />
                            </td>
                        </tr>
                        @endcan
                        @can('document.status-certificate')
                        <tr>
                            <td>Tagsági igazolás</td>
                            <td></td>
                            <td>
                                <x-input.button :href="route('documents.status-cert.download')" text="letöltés" />
                            </td>
                            <td>
                                <x-input.button :href="route('documents.status-cert.request')" class="coli blue" text="igénylés" />
                            </td>
                        </tr>
                        @endcan
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
