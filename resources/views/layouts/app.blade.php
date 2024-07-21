<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<script>
    document.documentElement.setAttribute('theme', 'light');
</script>

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
    <style>
        .light {
            --md-sys-color-primary: #b38f2f;
            --md-sys-color-on-primary: rgb(255 255 255);
            --md-sys-color-primary-container: rgb(192 155 58);
            --md-sys-color-on-primary-container: rgb(27 18 0);
            --md-sys-color-secondary: rgb(36, 40, 81);
            --md-sys-color-surface-tint: rgb(83 92 139);
            --md-sys-color-on-secondary: rgb(255 255 255);
            --md-sys-color-secondary-container: rgb(47 56 101);
            --md-sys-color-on-secondary-container: rgb(255 255 255);
            --md-sys-color-tertiary: rgb(47 13 49);
            --md-sys-color-on-tertiary: rgb(255 255 255);
            --md-sys-color-tertiary-container: rgb(83 46 83);
            --md-sys-color-on-tertiary-container: rgb(241 190 236);
            --md-sys-color-error: rgb(186 26 26);
            --md-sys-color-on-error: rgb(255 255 255);
            --md-sys-color-error-container: rgb(255 218 214);
            --md-sys-color-on-error-container: rgb(65 0 2);
            --md-sys-color-background: rgb(251 248 253);
            --md-sys-color-on-background: rgb(27 27 31);
            --md-sys-color-surface: rgb(251 248 253);
            --md-sys-color-on-surface: rgb(27 27 31);
            --md-sys-color-surface-variant: rgb(227 225 236);
            --md-sys-color-on-surface-variant: rgb(70 70 79);
            --md-sys-color-outline: rgb(118 118 128);
            --md-sys-color-outline-variant: rgb(198 197 208);
            --md-sys-color-shadow: rgb(0 0 0);
            --md-sys-color-scrim: rgb(0 0 0);
            --md-sys-color-inverse-surface: rgb(48 48 52);
            --md-sys-color-inverse-on-surface: rgb(243 240 244);
            --md-sys-color-inverse-primary: rgb(187 196 249);
            --md-sys-color-primary-fixed: rgb(222 225 255);
            --md-sys-color-on-primary-fixed: rgb(14 23 67);
            --md-sys-color-primary-fixed-dim: rgb(187 196 249);
            --md-sys-color-on-primary-fixed-variant: rgb(59 68 113);
            --md-sys-color-secondary-fixed: rgb(255 223 151);
            --md-sys-color-on-secondary-fixed: rgb(37 26 0);
            --md-sys-color-secondary-fixed-dim: rgb(234 194 93);
            --md-sys-color-on-secondary-fixed-variant: rgb(90 68 0);
            --md-sys-color-tertiary-fixed: rgb(255 214 249);
            --md-sys-color-on-tertiary-fixed: rgb(48 13 49);
            --md-sys-color-tertiary-fixed-dim: rgb(233 183 228);
            --md-sys-color-on-tertiary-fixed-variant: rgb(96 57 95);
            --md-sys-color-surface-dim: rgb(220 217 221);
            --md-sys-color-surface-bright: rgb(251 248 253);
            --md-sys-color-surface-container-lowest: rgb(255 255 255);
            --md-sys-color-surface-container-low: rgb(245 242 247);
            --md-sys-color-surface-container: rgb(240 237 241);
            --md-sys-color-surface-container-high: rgb(234 231 236);
            --md-sys-color-surface-container-highest: rgb(228 225 230);
        }
            /*.light {*/
        /*    --md-sys-color-primary: #b38f2f;*/
        /*    --md-sys-color-on-primary: #fff;*/
        /*    --md-sys-color-on-primary-text: #fff;*/


        /*    --md-sys-color-secondary: #161b33;*/
        /*    --md-sys-color-on-secondary: #fff;*/
        /*    --md-sys-color-secondary-container: rgb(57 62 116);*/
        /*    --md-sys-color-on-secondary-container: rgb(35 35 43);*/


        /*    --md-sys-color-error: rgb(78 0 2);*/
        /*    --md-sys-color-on-error: rgb(255 255 255);*/
        /*    --md-sys-color-error-container: rgb(140 0 9);*/
        /*    --md-sys-color-on-error-container: rgb(255 255 255);*/

        /*    --md-sys-color-background: rgb(251 248 255);*/
        /*    --md-sys-color-on-background: rgb(27 27 33);*/

        /*    --md-sys-color-surface: rgb(251 248 255);*/
        /*    --md-sys-color-on-surface: rgb(0 0 0);*/

        /*    --md-sys-color-surface-variant: rgb(227 225 236);*/
        /*    --md-sys-color-on-surface-variant: rgb(35 35 43);*/

        /*    --md-sys-color-outline: rgb(66 66 75);*/
        /*    --md-sys-color-outline-variant: rgb(66 66 75);*/

        /*    --md-sys-color-shadow: rgb(0 0 0);*/
        /*    --md-sys-color-scrim: rgb(0 0 0);*/
        /*    --md-sys-color-inverse-surface: rgb(48 48 54);*/
        /*    --md-sys-color-inverse-on-surface: rgb(255 255 255);*/
        /*    --md-sys-color-inverse-primary: rgb(235 234 255);*/
        /*    --md-sys-color-primary-fixed: rgb(57 62 116);*/


        /*    --md-sys-color-on-secondary-fixed: #000;*/
        /*    --md-sys-color-secondary-fixed-dim: #000;*/
        /*    --md-sys-color-on-secondary-fixed-variant: #000;*/
        /*    --md-sys-color-tertiary-fixed: #000;*/
        /*    --md-sys-color-on-tertiary-fixed: #000;*/
        /*    --md-sys-color-tertiary-fixed-dim: #000;*/
        /*    --md-sys-color-on-tertiary-fixed-variant: #000;*/
        /*    --md-sys-color-surface-dim: #000;*/
        /*    --md-sys-color-surface-bright: #000;*/
        /*    --md-sys-color-surface-container-lowest: #000;*/
        /*    --md-sys-color-surface-container-low: #000;*/
        /*    --md-sys-color-surface-container:#000;*/
        /*    --md-sys-color-surface-container-high: #000;*/
        /*    --md-sys-color-surface-container-highest: #000;*/

        /*    --md-sys-color-on-primary-container: #000;*/
        /*    --md-sys-color-surface-tint: #000;*/
        /*    --md-sys-color-tertiary: #000;*/
        /*    --md-sys-color-on-tertiary: #000;*/
        /*    --md-sys-color-tertiary-container: #000;*/
        /*    --md-sys-color-on-tertiary-container: #000;*/
        /*    --md-sys-color-on-primary-fixed: #000;*/
        /*    --md-sys-color-primary-fixed-dim: #000;*/
        /*    --md-sys-color-on-primary-fixed-variant: #000;*/
        /*    --md-sys-color-secondary-fixed: #000;*/



        }



    </style>
    <link type="text/css" rel="stylesheet" href="{{ mix('css/materialize.css') }}" media="screen,projection" />
    <link type="text/css" rel="stylesheet" href="{{ mix('css/app.css') }}" media="screen,projection" >
    @livewireStyles

    <!-- Scripts -->


    <script type="text/javascript" src="{{ mix('js/jquery.min.js') }}"></script>
    <script type="text/javascript" src="{{ mix('js/tabulator.min.js') }}" defer></script>
    <script type="text/javascript" src="{{ mix('js/materialize.min.js') }}"></script>
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
                document.documentElement.setAttribute('theme', 'dark');

            }
        );

    </script>

</head>

<body>
    <script>document.body.classList.add(localStorage.getItem('themeMode') || 'light');</script>
    <header>
        @include('layouts.navbar')
    </header>
        <div class="container">
            <div class="row">
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
            M.AutoInit();
        });
        function toggleColorMode() {
            const oldThemeMode = localStorage.getItem('themeMode') || 'light'; // default is light mode
            const newThemeMode = oldThemeMode === 'dark' ? 'light' : 'dark';
            localStorage.setItem('themeMode', newThemeMode);
            document.body.classList.remove(oldThemeMode);
            document.body.classList.add(newThemeMode);
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

        titles = $(".arrow-dropdown .arrow-dropdown-title");
        titles.on('click', toggleCollContent(titles));
        // initialize them as closed:
        titles.addClass('closed');
        </script>
    @endpush

    @stack('scripts')
    @livewireScripts
</body>

</html>
