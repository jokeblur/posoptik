<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Optik Melati') }}</title>

        <!-- PWA Meta Tags -->
        <meta name="description" content="Aplikasi manajemen optik dan resep kacamata Optik Melati">
        <meta name="theme-color" content="#e74c3c">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="default">
        <meta name="apple-mobile-web-app-title" content="Optik Melati">
        <meta name="msapplication-TileColor" content="#e74c3c">
        <meta name="msapplication-config" content="/browserconfig.xml">

        <!-- PWA Icons -->
        <link rel="icon" type="image/png" sizes="32x32" href="/image/optik-melati.png">
        <link rel="icon" type="image/png" sizes="16x16" href="/image/optik-melati.png">
        <link rel="apple-touch-icon" href="/image/optik-melati.png">
        <link rel="manifest" href="/manifest.json">

        <!-- Fonts -->
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap">

        <!-- Styles -->
        <!-- @livewireStyles -->

        <!-- Scripts -->
        <!-- @vite(['resources/css/app.css', 'resources/js/app.js']) -->
        <link rel="stylesheet" href="{{ asset('css/app.css') }}">

        <!-- Scripts -->
        <script src="{{ asset('js/app.js') }}" defer></script>
        <script src="{{ asset('js/pwa.js') }}" defer></script>
        
        <!-- PWA Styles -->
        <link rel="stylesheet" href="{{ asset('css/pwa.css') }}">
    </head>
    <body class="font-sans antialiased">
        <x-jet-banner />

        <div class="min-h-screen bg-gray-100">
            @livewire('navigation-menu')

            <!-- Page Heading -->
            @if (isset($header))
                <header class="bg-white shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endif

            <!-- Page Content -->
            <main>
                {{ $slot }}
            </main>
        </div>

        @stack('modals')

        @livewireScripts
    </body>
</html>
