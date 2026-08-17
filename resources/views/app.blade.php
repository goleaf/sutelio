<!DOCTYPE html>
<html
    lang="{{ str_replace('_', '-', $page['props']['locale'] ?? 'en') }}"
    dir="ltr"
>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
        <meta name="application-name" content="{{ $page['props']['name'] }}">
        <meta name="apple-mobile-web-app-title" content="{{ $page['props']['name'] }}">
        <meta name="theme-color" content="#123C8B">

        <link rel="icon" href="/favicon.ico" sizes="any">
        <link rel="icon" href="/favicon.svg" type="image/svg+xml">
        <link rel="apple-touch-icon" href="/apple-touch-icon.png">

        @fonts

        @vite(['resources/css/app.css', 'resources/js/app.ts', "resources/js/pages/{$page['component']}.vue"])
        <x-inertia::head>
            <title>{{ $page['props']['name'] }}</title>
        </x-inertia::head>
    </head>
    <body class="nativephp-safe-area font-sans antialiased">
        <x-inertia::app />
    </body>
</html>
