@props(['title' => 'Support'])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }} · {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full bg-gray-50 text-gray-900 antialiased dark:bg-gray-950 dark:text-gray-100">
    <div class="mx-auto flex min-h-full max-w-3xl flex-col px-4">
        <header class="flex items-center justify-between py-6">
            <span class="text-lg font-semibold">{{ config('app.name') }} Support</span>
            {{ $header ?? '' }}
        </header>

        <main class="flex-1 py-4">
            {{ $slot }}
        </main>

        <footer class="py-6 text-center text-sm text-gray-500">
            {{ config('app.name') }}
        </footer>
    </div>
</body>
</html>
