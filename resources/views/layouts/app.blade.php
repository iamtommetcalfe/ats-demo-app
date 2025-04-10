<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Amiqus ATS Demo</title>
    @vite('resources/css/app.css')
    @vite('resources/js/app.js')
</head>
<body class="bg-gray-50 text-gray-800">
<div class="container mx-auto p-6">
    <div class="mb-6 flex justify-between items-center">
        <h1 class="text-xl font-bold text-gray-900">Amiqus ATS Demo</h1>
        <a href="/"
           class="text-sm text-blue-600 hover:text-blue-800 underline">
            Home
        </a>
        <a href="{{ route('amiqus.connect') }}"
           class="text-sm text-blue-600 hover:text-blue-800 underline">
            Manage Amiqus Connection
        </a>
    </div>
    <div class="mb-6 flex justify-between items-start">
        @include('partials.breadcrumbs')
    </div>

    @yield('content')
</div>
</body>
</html>
