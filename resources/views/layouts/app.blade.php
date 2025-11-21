<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    {{-- Favicon / Apple touch icon (taruh file di public/assets/img) --}}
    <link rel="apple-touch-icon" sizes="76x76" href="{{ asset('assets/img/apple-touch-icon.png') }}">
    <link rel="icon" type="image/png" href="{{ asset('assets/img/favicon.ico') }}">

    <title>{{ $title ?? config('app.name', 'Kerja Bro') }}</title>

    {{-- Fonts & icons --}}
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700" rel="stylesheet" />
    <script src="https://kit.fontawesome.com/42d5adcbca.js" crossorigin="anonymous"></script>

    {{-- Nucleo Icons --}}
    <link href="{{ asset('assets/css/nucleo-icons.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/css/nucleo-svg.css') }}" rel="stylesheet" />

    {{-- Argon CSS --}}
    <link id="pagestyle" href="{{ asset('assets/css/argon-dashboard.css') }}" rel="stylesheet" />

    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>

<style>
    /* Active state: gradient teal → green */
    .sidenav .nav-link.active {
        background: linear-gradient(135deg, #3D8D7A 0%, #6FCF97 100%);
        color: #fff !important;
        border-radius: 0.75rem;
        box-shadow: 0 4px 10px rgba(61, 141, 122, 0.25);
    }

    /* Pastikan icon & teks ikut putih saat active */
    .sidenav .nav-link.active .icon {
        background: transparent !important;
    }

    .sidenav .nav-link.active .icon i,
    .sidenav .nav-link.active i,
    .sidenav .nav-link.active .nav-link-text {
        color: #fff !important;
        opacity: 1 !important;
    }

    /* Hover/focus tetap konsisten pada link aktif */
    .sidenav .nav-link.active:hover,
    .sidenav .nav-link.active:focus {
        background: linear-gradient(135deg, #3D8D7A 0%, #6FCF97 100%);
        color: #fff !important;
    }

    /* Optional: haluskan transisi */
    .sidenav .nav-link {
        transition: background-color .2s ease, color .2s ease, box-shadow .2s ease;
    }
</style>


<body class="{{ $class ?? '' }}">

    @guest
        @yield('content')
    @endguest

    @auth
        @php $rn = request()->route()->getName(); @endphp

        @if (in_array($rn, [
                'sign-in-static',
                'sign-up-static',
                'login',
                'register',
                'recover-password',
                'rtl',
                'virtual-reality',
            ]))
            @yield('content')
        @else
            @if (!in_array($rn, ['profile', 'profile-static']))
                <div class="min-height-300 bg-primary position-absolute w-100"></div>
            @else
                <div class="position-absolute w-100 min-height-300 top-0"
                    style="background-image:url('{{ asset('assets/img/profile-layout-header.jpg') }}'); background-position-y:50%;">
                    <span class="mask bg-primary opacity-6"></span>
                </div>
            @endif

            @include('layouts.navbars.auth.sidenav')

            <main class="main-content border-radius-lg">
                @yield('content')
            </main>

            @include('components.fixed-plugin')
        @endif
    @endauth

    {{-- Core JS --}}
    <script src="{{ asset('assets/js/core/popper.min.js') }}"></script>
    <script src="{{ asset('assets/js/core/bootstrap.min.js') }}"></script>

    {{-- Plugins --}}
    <script src="{{ asset('assets/js/plugins/perfect-scrollbar.min.js') }}"></script>
    <script src="{{ asset('assets/js/plugins/smooth-scrollbar.min.js') }}"></script>
    <script>
        var win = navigator.platform.indexOf('Win') > -1;
        if (win && document.querySelector('#sidenav-scrollbar')) {
            Scrollbar.init(document.querySelector('#sidenav-scrollbar'), {
                damping: '0.5'
            });
        }
    </script>

    {{-- Argon main JS --}}
    <script src="{{ asset('assets/js/argon-dashboard.js') }}"></script>

    {{-- Optional: Github buttons --}}
    <script async defer src="https://buttons.github.io/buttons.js"></script>

    @stack('js')
</body>

</html>
