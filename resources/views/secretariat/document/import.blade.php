@extends('layouts.app')

@section('title')
<i class="material-icons left">assignment</i>Dokumentumok
@endsection

@section('content')

<div class="row">
    <div class="col s12">
        <div class="card">
            <div class="card-content">
                <span class="card-title">Behozatali engedély</span>
                <blockquote>
                    Be kell jelenteni:
                    <ul class="browser-default">
                        <li>Személyi számítógép</li>
                        <li>Egyéb nem említett elektronikai eszközök (kenyérpirító, vízforraló)</li>
                        <li>Egyéb bútorzat, irodai eszköz (szék, asztal, tábla stb.)</li>
                    </ul>
                    Nem kell bejelenteni:
                    <ul class="browser-default">
                        <li>Konyhai eszközök (pl. edények)</li>
                        <li>Hajszárító, hajsütő</li>
                    </ul>
                </blockquote>
                <div class="row">
                    <div class="col s12">
                        <table>
                            <tbody>
                                <tr>
                                    <form method="POST" action="{{ route('documents.import.add') }}">
                                        @csrf
                                        <td>
                                            <x-input.text id="item" text="Eszköz" autofocus maxlength="100" required />
                                        </td>
                                        <td>
                                            <x-input.text id="serial_number" text="Sorozatszám (számítógépeknél)" maxlength="30" />
                                        <td>
                                            <x-input.button floating class="right" icon="add"/>
                                        </td>
                                    </form>
                                </tr>
                                @foreach ($items as $item)
                                <tr>
                                    <td>{{ $item->name }}</td>
                                    <td>{{ $item->serial_number ?? ''}}</td>
                                    <td>
                                        <form method="POST" action="{{ route('documents.import.remove') }}">
                                            @csrf
                                            <x-input.text hidden id="id" :value="$item->id" />
                                            <x-input.button floating class="right" icon="remove" />
                                        </form>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="card-action">
                <a href="{{ route('documents.import.download') }}" type="submit"
                    class="btn waves-effect coli">letöltés</a>
                <a href="{{ route('documents.import.print') }}" type="submit"
                    class="btn waves-effect coli blue right">Nyomtatás</a>
            </div>
        </div>
    </div>
</div>
@endsection
