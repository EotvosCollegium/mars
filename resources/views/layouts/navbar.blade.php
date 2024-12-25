<!--navbar-->
<div class="navbar-fixed">
    <nav class="top-nav">
        <div class="nav-wrapper">
            <div class="row">
                <!--sidenav trigger for mobile-->
                <a href="#" data-target="sidenav" class="sidenav-trigger hide-on-large-only"><i
                            class="material-icons">menu</i></a>
                <!--logo for mobile-->
                <a class="brand-logo center hide-on-large-only"
                    style="text-transform: uppercase;font-weight:300;letter-spacing:3px;" href="{{ url('/') }}">
                    {{ config('app.name', 'Urán') }} </a>
                <!--title-->

                <div class="col hide-on-med-and-down noselect" style="margin-left:310px">
                    @yield('title')
                </div>

                <!-- Right Side Of Navbar -->
                <ul class="right hide-on-med-and-down">
                    @include('layouts.navigators.user')
                </ul>
            </div>
        </div>
    </nav>
</div>

<!--sidebar-->
<ul class="sidenav @if ($fixed) sidenav-fixed @endif" id="sidenav">
    <!-- logo -->
    @include('layouts.logo')
    @if(config('app.env') == 'production' && config('app.debug'))
        <li class="grey darken-1 white-text" style="padding:5px;line-height:1.5em">@lang('general.debug_descr')</li>
    @endif

    <!-- main options -->
    @if(Auth::user()?->verified)
        <!-- print page -->
        @can('use', \App\Models\PrintAccount::class)
        <li><a class="waves-effect" href="{{ route('print.index') }}"><i class="material-icons left">local_printshop</i>@lang('print.print')</a></li>
        @endif
        <!-- internet page -->
        <li><a class="waves-effect" href="{{ route('internet.index') }}"><i
                        class="material-icons left">wifi</i>@lang('internet.internet')</a></li>

        <!-- documents page -->
        @can('document.any')
            <li><a class="waves-effect" href="{{ route('documents') }}"><i class="material-icons left">assignment</i>Dokumentumok</a>
            </li>
        @endcan
        <!-- rooms page -->
        @can('viewAny', \App\Models\Room::class)
            <li><a class="waves-effect" href="{{ route('rooms') }}"><i class="material-icons left">bed</i>Szobabeosztás</a>
            </li>
        @endcan
        @can('fillOrManage', \App\Models\SemesterEvaluation::class)
            <li><a class="waves-effect" href="{{ route('secretariat.evaluation.show') }}"><i
                            class="material-icons left">rate_review</i>Szemeszter értékelés</a>
            </li>
        @endcan
        <!-- admission page -->
        @can('viewSome', \App\Models\Application::class)
            <li><a class="waves-effect" href="{{ route('admission.applicants.index') }}"><i
                            class="material-icons left">person_search</i>Felvételi</a></li>
        @endcan
        <!-- collapsible modules -->
        <li class="no-padding">
            <ul class="collapsible collapsible-accordion">
                <!-- dormitory module -->
                <li class="@yield('dormitory_module')">
                    <a class="collapsible-header waves-effect" style="padding-left:32px">
                        <i class="material-icons left">domain</i>
                        @lang('general.dormitory')
                        <i class="material-icons right">arrow_drop_down</i>
                    </a>
                    <div class="collapsible-body">
                        <ul>
                            <!-- reservations -->
                            @can('viewType', [\App\Models\ReservableItem::class, \App\Enums\ReservableItemType::WASHING_MACHINE])
                                <li><a class="waves-effect" href="{{ route('reservations.items.index', ['type' => \App\Enums\ReservableItemType::WASHING_MACHINE]) }}"><i
                                    class="material-icons left">local_laundry_service</i>@lang('reservations.washing_machine_reservations')</a></li>
                            @endif
                            @can('viewType', [\App\Models\ReservableItem::class, \App\Enums\ReservableItemType::ROOM])
                                <li><a class="waves-effect" href="{{ route('reservations.items.index', ['type' => \App\Enums\ReservableItemType::ROOM]) }}"><i
                                    class="material-icons left">schedule</i>@lang('reservations.room_reservations')</a></li>
                            @endif
                            <!-- faults page -->
                            @can('view', \App\Models\Fault::class)
                                <li><a class="waves-effect" href="{{ route('faults') }}"><i
                                                class="material-icons left">build</i>@lang('faults.faults')
                                        @can('update', \App\Models\Fault::class)
                                            @notification(\App\Models\Fault::class)
                                        @endif
                                    </a>
                                </li>
                            @endcan
                        </ul>
                    </div>
                </li>
                <!-- students' council module -->
                @if(user()->can('is-collegist') || user()->hasRole(\App\Models\Role::SECRETARY))
                    <li class="@yield('student_council_module')">
                        <a class="collapsible-header waves-effect" style="padding-left:32px">
                            <i class="material-icons left">groups</i> <!-- star icon? -->
                            Választmány
                            <i class="material-icons right">arrow_drop_down</i>
                        </a>
                        <div class="collapsible-body">
                            <ul>
                                @can('is-collegist')
                                    <!-- economic committee -->
                                    <li>
                                        <a class="waves-effect" href="{{ route('economic_committee') }}">
                                            <i class="material-icons left">attach_money</i> Választmányi kassza
                                        </a>
                                    </li>
                                    <!-- communication committee -->
                                    <li>
                                        <a class="waves-effect" href="{{ route('epistola') }}">
                                            <i class="material-icons left">campaign</i> Epistola Collegii
                                        </a>
                                    </li>
                                    <!-- community committee -->
                                    @can('voteOrManage', \App\Models\MrAndMissVote::class)
                                        <li>
                                            <a class="waves-effect" href="{{ route('mr_and_miss.index') }}">
                                                <i class="material-icons left">how_to_vote</i> Mr. és Miss Eötvös
                                            </a>
                                        </li>
                                    @endcan
                                    <!-- community service-->
                                    <li>
                                        <a class="waves-effect" href="{{ route('community_service') }}">
                                            <i class="material-icons left">business_center</i> Közösségi tevékenység
                                        </a>
                                    </li>
                                @endcan
                                {{-- the secretariat can only see general assemblies --}}
                                @can('viewAny', \App\Models\GeneralAssembly::class)
                                    <!-- general assemblies -->
                                    <li>
                                        <a class="waves-effect" href="{{ route('general_assemblies.index') }}">
                                            <i class="material-icons left">thumbs_up_down</i> @lang('voting.assembly')
                                        </a>
                                    </li>
                                @endcan
                                {{-- results of anonymous questions --}}
                                @can('administer', \App\Models\AnonymousQuestions\AnswerSheet::class)
                                    <li>
                                        <a class="waves-effect"
                                           href="{{ route('anonymous_questions.index_semesters') }}">
                                            <i class="material-icons left">assessment</i>
                                            @lang('anonymous_questions.anonymous_questions')
                                        </a>
                                    </li>
                                @endcan
                            </ul>
                        </div>
                    </li>
                @endif
                @if(Auth::user()->isAdmin() || Auth::user()->isCollegist())
                {{-- Sysadmin module --}}
                <li class="@yield('admin_module')">
                    <a class="collapsible-header waves-effect" style="padding-left:32px">
                        <i class="material-icons left">admin_panel_settings</i>
                        Rendszergazda
                        <i class="material-icons right">arrow_drop_down</i>
                    </a>
                    <div class="collapsible-body">
                        <ul>
                            <!-- print admin -->
                            @can('handleAny', \App\Models\PrintAccount::class)
                            <li>
                                <a class="waves-effect" href="{{ route('print.index.admin') }}">
                                    <i class="material-icons left">local_printshop</i>Nyomtatás
                                </a>
                            </li>
                            @endcan

                                <!-- internet admin -->
                                @can('handleAny', \App\Models\Internet\InternetAccess::class)
                                    <li>
                                        <a class="waves-effect" href="{{ route('internet.admin.index') }}">
                                            <i class="material-icons left">wifi</i>Internet elérés
                                            @notification(\App\Models\Internet\MacAddress::class)
                                        </a>
                                    </li>
                                @endcan
                                <li>
                                    <a class="waves-effect" href="{{ route('routers') }}">
                                        <i class="material-icons left">router</i>Routerek
                                        @notification(\App\Models\Internet\Router::class)
                                    </a>
                                </li>
                                @can('view', \App\Models\Checkout::admin())
                                    <li>
                                        <a class="waves-effect" href="{{ route('admin.checkout') }}">
                                            <i class="material-icons left">credit_card</i> Rendszergazdai kassza
                                        </a>
                                    </li>
                                @endcan
                            </ul>
                        </div>
                    </li>
                @endif
                @can('viewAny', \App\Models\User::class)
                    <!-- user management -->
                    <li>
                        <a class="waves-effect" href="{{ route('users.index') }}">
                            <i class="material-icons left">supervisor_account</i>
                            @if(user()->hasRole([\App\Models\Role::SYS_ADMIN, \App\Models\Role::STAFF]))
                                @lang("general.users")
                            @else
                                Collegisták
                            @endif
                        </a>
                    </li>
                @endcan
                @can('handleGuests', \App\Models\User::class)
                    <li>
                        <a class="waves-effect" href="{{ route('secretariat.registrations') }}">
                            <i class="material-icons left">how_to_reg</i> Regisztrációk
                            @notification(\App\Models\User::class)
                        </a>
                    </li>
                @endcan
            </ul>
        </li>
    @endif

    <li>
        <div class="divider"></div>
    </li>
    <!-- User page or register/login -->
    <div class="hide-on-large-only">
        @include('layouts.navigators.user')
    </div>

    <li>
        <ul class="collapsible collapsible-accordion">
            <!-- language select -->
            <li>
                <a class="collapsible-header waves-effect" style="padding-left:32px">
                    <i class="material-icons left">language</i>Language
                    <i class="material-icons right">arrow_drop_down</i></a>
                <div class="collapsible-body">
                    <ul>
                        @foreach (config('app.locales') as $code => $name)
                            <li><a class="waves-effect" href="{{ route('setlocale', $code) }}">{{ $name }}</a></li>
                        @endforeach
                    </ul>
                </div>
            </li>

            <!-- other -->
            @if(Auth::user()?->verified)
                <li>
                    <a class="collapsible-header waves-effect" style="padding-left:32px">
                        <i class="material-icons left">more_horiz</i>@lang('general.other')
                        <i class="material-icons right">arrow_drop_down</i></a>
                    <div class="collapsible-body">
                        <ul>
                            <!-- language contributions -->
                            <li><a href="{{ route('localizations') }}">
                                    <i class="material-icons left">sentiment_satisfied_alt</i>@lang('localizations.help_translate')
                                </a></li>

                            <!-- report a bug -->
                            <li><a href="{{ route('issues.create') }}">
                                    <i class="material-icons left">sentiment_very_dissatisfied</i>@lang('issue.report')
                                </a></li>
                        </ul>
                    </div>
                </li>
                <li>
                    <a class="dark-toggle" href="#" onclick="toggleColorMode()" title="Dark/light"><i
                                class="material-icons left">brightness_4</i>@lang('general.toggle-dark-mode')</a>
                </li>
            @endif
        </ul>
    </li>

    <!-- logout -->
    @if(Auth::user())
        <li>
            <a class="waves-effect" href="{{ route('logout') }}"
               onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                <i class="material-icons left">login</i>@lang('general.logout')
            </a>
        </li>
        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
            @csrf
        </form>
    @endif
</ul>

@push('scripts')
    <script>
        //The href: mailto may not work on every device. In this case, show a notification.
        var myHTML = "<span>@lang('general.if_mail_not_working')</span><button class='btn-flat toast-action' onclick='dismiss()'>OK</button>";

        function dismiss() {
            M.Toast.dismissAll();
        };
    </script>
@endpush
