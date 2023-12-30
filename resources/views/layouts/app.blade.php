<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <!-- indicate mobile friendly page-->
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- change status bar color on supported mobile browsers -->
    <meta name="theme-color" content="#252A51">
    <!-- change the page's icon in the browser's tab -->
    <link rel="icon" href="{{ config('app.logo_with_bg_path') }}">
    <!-- CSRF Token for Laravel's forms -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Urán') }}</title>

    <!-- Styles -->
    <link type="text/css" rel="stylesheet" href="{{ mix('css/materialize.css') }}" media="screen,projection" />
    <link type="text/css" rel="stylesheet" href="{{ mix('css/app.css') }}" media="screen,projection" >
    @livewireStyles

    <!-- Scripts -->
    <script type="text/javascript" src="{{ mix('js/jquery.min.js') }}"></script>
    <script type="text/javascript" src="{{ mix('js/tabulator.min.js') }}" defer></script>
    <script type="text/javascript" src="{{ mix('js/cookieconsent.min.js') }}" defer></script>
    <script type="text/javascript" src="{{ mix('js/cookieconsent-initialize.js') }}" defer></script>
    <script type="text/javascript" src="{{ mix('js/materialize.js') }}"></script>
    <script type="text/javascript" src="{{ mix('js/site.js') }}"></script>
    <script>
        $(document).ready(
            function() {
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': "{{ csrf_token() }}"
                    }
                });
                $('.sidenav').sidenav();
                $('.collapsible').collapsible();
                $(".preloader").fadeOut();
                // Attach a submit event handler to all forms
                $('form').submit(function() {
                    // Disable the submit button of the current form
                    $(this).find(':submit').prop('disabled', true);
                });
            }
        );
    </script>
    <style>
    .no-js #loader { display: none;  }
    .js #loader { display: block; position: absolute; left: 100px; top: 0; }
    .preloader {
        position: fixed;
        left: 0px;
        top: 0px;
        width: 100%;
        height: 100%;
        z-index: 9999;
        background: url(/loading.gif) center no-repeat #fff;
    }
    </style>

</head>

<div class="preloader"></div>
<body class="{{ Cookie::get('theme') }}">
    <header>
        @include('layouts.navbar')
    </header>
    <div class="row">
        <div class="container">
            <div class="col s12 m12 l11 offset-xl2 offset-l3">
                @yield('content')
            </div>
        </div>
    </div>
    @include('utils.toast')
    @push('scripts')
        <script>
        var cookieMessages = {
            'dismiss': "@lang('cookie.dismiss')",
            'allow': "@lang('cookie.allow')",
            'deny': "@lang('cookie.deny')",
            'link': "@lang('cookie.link')",
            'cookie': "@lang('cookie.message')",
            'header': "@lang('cookie.header')",
        };
        $(document).ready(function(){
            $('.tooltipped').tooltip();
            $('select').formSelect();
        });
        function toggleColorMode() {
            var mode = (localStorage.getItem('mode') || 'dark') === 'dark' ? 'light' : 'dark';
            localStorage.setItem('mode', mode);
            if(localStorage.getItem('mode') === 'dark') {
                document.querySelector('body').classList.add('dark');
            } else {
                document.querySelector('body').classList.remove('dark');
            }

            // Save as cookie
            $.ajax({
                type: "POST",
                url: "{{ route('set-color-mode', [':mode']) }}".replace(':mode', mode),
            });
        }

        // for our custom arrow dropdowns
        // (e.g. the dropdown of workshop secretaries)
        function toggleCollContent(title) {
            return function() {
                if (title.is(".closed")) {
                    title.removeClass("closed");
                    title.addClass("open");
                    // the rest is done by CSS rules
                } else {
                    title.removeClass("open");
                    title.addClass("closed");
                }
            }
        }

        $(".arrow-dropdown .arrow-dropdown-title").each(function () {
            $(this).on('click', toggleCollContent($(this)));
            // initialize it as closed:
            $(this).addClass('closed');
        });
        </script>
    @endpush

    @stack('scripts')
    @livewireScripts
</body>

</html>
