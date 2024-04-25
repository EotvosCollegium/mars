@extends('layouts.app')

@section('title')
    <a href="#!" class="breadcrumb">@lang('general.admin')</a>
    <a href="#!" class="breadcrumb">Regisztrációk</a>
@endsection
@section('secretariat_module')
    active
@endsection

@section('content')

    <div class="row">
        <div class="col s12">
            <div class="card">
                <div class="card-content">
                    <span class="card-title">Regisztrációk kezelése</span>
                    <table>
                        <tbody>
                        @foreach ($users as $user)
                            <tr>
                                <td>
                                    <b>{{ $user->name }}</b> - {{ $user->email }}
                                    @if($user->hasPersonalInformation())
                                        <br>{{$user->personalInformation->phone_number}}
                                        <br>Tervezett kiköltözés: {{$user->personalInformation->tenant_until ?? '?'}}
                                    @endif
                                </td>
                                <td>
                                    <div class="right">
                                        <x-input.button
                                            :href="route('secretariat.registrations.accept', ['id' => $user->id])"
                                            floating icon="done" class="green"/>
                                        <x-input.button
                                            :href="route('secretariat.registrations.reject', ['id' => $user->id])"
                                            floating icon="block" class="red"/>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                        @if(count($users) == 0)
                            <tr>
                                <td>
                                    @lang('internet.nothing_to_show')
                                </td>
                            </tr>
                        @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    @can('is-admin')
        <div class="card">
            <div class="card-content">
                <div class="row">
                    <div class="col s12 xl7">
                        <span class="card-title">Meghívó küldése</span>
                    </div>
                    <form method="POST" action="{{ route('secretariat.invite') }}">
                        @csrf
                        <div class="col s12">
                            <blockquote>Itt lehet adott e-mail-címmel egy felhasználót készíteni, és meghívót küldeni a
                                címre.
                            </blockquote>
                        </div>
                        <div class="col s12 m12 l4">
                            <x-input.text id="name" text="user.name" required/>
                        </div>
                        <div class="col s12 m12 l4">
                            <x-input.text id="email" type="email" text="user.email" required/>
                        </div>
                        <div class="col s12 m12 l4">
                            <x-input.button class="right" text="Létrehozás és meghívó küldése"/>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endcan
@endsection
