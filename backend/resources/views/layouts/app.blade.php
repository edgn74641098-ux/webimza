<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'TRINOX Signature Manager') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans bg-slate-50 text-slate-900">
        <div class="app-shell">
            @include('layouts.navigation')

            @isset($header)
                <header class="app-header">
                    <div class="app-container py-5">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <main class="app-container py-6">
                <div class="space-y-4">
                    @if (session('success'))
                        <x-ui.alert type="success">{{ session('success') }}</x-ui.alert>
                    @endif
                    @if (session('error'))
                        <x-ui.alert type="error">{{ session('error') }}</x-ui.alert>
                    @endif
                    @if ($errors->any())
                        <x-ui.alert type="error">{{ $errors->first() }}</x-ui.alert>
                    @endif
                </div>
                {{ $slot }}
            </main>
        </div>
    </body>
</html>
