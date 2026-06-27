<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'OEE') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gradient-to-br from-blue-900 to-blue-800 min-h-screen flex items-center justify-center">
    <div class="w-full max-w-md px-4">
        @yield('content')
    </div>

    <!-- Decorative Elements -->
    <div class="fixed -bottom-10 -right-10 w-40 h-40 bg-blue-500 rounded-full opacity-10 blur-3xl"></div>
    <div class="fixed -top-10 -left-10 w-40 h-40 bg-blue-600 rounded-full opacity-10 blur-3xl"></div>
</body>
</html>
