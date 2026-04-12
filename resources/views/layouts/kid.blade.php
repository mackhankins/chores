<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <title>{{ $title ?? 'Chores' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        * { -webkit-tap-highlight-color: transparent; touch-action: manipulation; }
        [x-cloak] { display: none !important; }
        body { padding: env(safe-area-inset-top) env(safe-area-inset-right) env(safe-area-inset-bottom) env(safe-area-inset-left); }
    </style>
</head>
<body class="min-h-svh bg-gray-100 text-gray-900 antialiased">
    <div class="mx-auto min-h-svh max-w-lg">
        {{ $slot }}
    </div>
</body>
</html>
