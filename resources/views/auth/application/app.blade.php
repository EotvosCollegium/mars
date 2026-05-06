@extends('layouts.app')

@section('content')
    <div class="card">
        <div class="card-content">
            <div class="card-title center-align">Jelentkezés az Eötvös József Collegiumba</div>
            <p class="center-align"><a href="https://eotvos.elte.hu/felveteli">Pályázati felhívás és egyéb
                    információk</a></p>
        </div>
    </div>
    <div class="card">
        <div class="card-content">
            <h6>Jelentkezés státusza:
                @if ($user->application->submitted)
                    <span class="green-text">Beadva.</span>
                @else
                    <span class="coli-text text-orange"><i>Folyamatban.</i></span>
                @endif
            </h6>

            <h6>Jelentkezési határidő:
                <i>{{ $deadline?->format('Y-m-d H:i') }}</i>
                @if ($deadline_extended)
                    <small class="coli-text text-orange">(Meghosszabbítva)</small>
                @endif
            </h6>
            @if (!$user->application->submitted)
                Hátra van: <i>{{ (int) \Carbon\Carbon::now()->diffInDays($deadline, false) }}</i> nap.
            @endif

            <blockquote>
                @if (!$user->application->submitted)

                    <div class="markdown_with_red">
                        @markdown(\App\Models\ConfigurableValue::getText("APPLICATION_INFORMATION_PRIOR_TO_FINALIZATION"))
                    </div>
                    
                @else

                    <div class="markdown_with_red">
                        @markdown(\App\Models\ConfigurableValue::getText("APPLICATION_INFORMATION_AFTER_FINALIZATION"))
                    </div>
                    @foreach ($user->application->appliedWorkshops()->get() as $workshop)
                        <div class="markdown_with_red">
                            @markdown(\App\Models\ConfigurableValue::getText("APPLICATION_INFORMATION_PER_WORKSHOP_AFTER_FINALIZATION", $workshop->id))
                        </div>
                    @endforeach
                @endif
            </blockquote>
            @foreach ($errors->all() as $error)
                <blockquote class="error">{{ $error }}</blockquote>
            @endforeach
        </div>
    </div>
    @if (!$user->application->submitted)
        <nav style="height: auto;">
            <ul class="tabs tabs-transparent" style="display: flex; flex-wrap: wrap; height: auto;">
                <li class="tab">
                    <a href="{{ route('application', ['page' => 'personal']) }}"
                        class="{{ request()->get('page', 'personal') == 'personal' ? 'active' : '' }}">Személyes adatok</a>
                </li>
                <li class="tab">
                    <a href="{{ route('application', ['page' => 'educational']) }}"
                        class="{{ request()->get('page') == 'educational' ? 'active' : '' }}">Tanulmányok</a>
                </li>
                <li class="tab">
                    <a href="{{ route('application', ['page' => 'questions']) }}"
                        class="{{ request()->get('page') == 'questions' ? 'active' : '' }}">Szakmai és motivációs kérdések</a>
                </li>
                <li class="tab">
                    <a href="{{ route('application', ['page' => 'files']) }}"
                        class="{{ request()->get('page') == 'files' ? 'active' : '' }}">Fájlok</a>
                </li>
            </ul>
        </nav>
        @yield('form')
    @else
        @include('auth.application.application', ['user' => $user])
    @endif
    @if ($user->application->submitted)
        @include('network.internet.wifi_password', [
            'internet_access' => $user->internetAccess,
            'application' => true,
        ])
    @endif

    @if (request()->get('page') != 'submit' && !$user->application->submitted)
        <x-input.button href="{{ route('application', ['page' => 'submit']) }}" style="margin-bottom: 40px"
            class="right coli blue" text="Áttekintés és véglegesítés" />
    @endif
@endsection
