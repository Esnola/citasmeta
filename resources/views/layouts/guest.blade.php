<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Laravel') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />
    @fluxAppearance
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
    @livewireStyles
</head>
<body class="min-h-screen bg-slate-950 text-slate-100 antialiased">
    <div class="relative min-h-screen overflow-hidden">
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,rgba(16,185,129,0.2),transparent_42%),radial-gradient(circle_at_bottom_right,rgba(59,130,246,0.16),transparent_38%),linear-gradient(180deg,#020617,#0f172a)]"></div>
        <div class="relative mx-auto flex min-h-screen w-full max-w-6xl items-center justify-center px-6 py-10">
            <div class="w-full @yield('contentWidth', 'max-w-md')">
                @yield('content')
            </div>
        </div>
    </div>
    @livewireScripts
    @fluxScripts
</body>
</html>
