@props(['title' => config('app.name')])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex h-full flex-col bg-white text-gray-900 antialiased dark:bg-gray-950 dark:text-gray-100">
    <header class="border-b border-gray-100 dark:border-white/10">
        <div class="mx-auto flex max-w-6xl items-center justify-between px-6 py-4">
            <a href="{{ route('landing') }}" class="text-lg font-bold">{{ config('app.name') }}</a>
            <nav class="flex items-center gap-2">
                <a href="{{ url('/admin') }}"
                    class="rounded-lg px-3 py-2 text-sm font-medium text-gray-600 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white">
                    Log in
                </a>
                <a href="{{ route('signup') }}"
                    class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-800 dark:bg-white dark:text-gray-900">
                    Sign up
                </a>
            </nav>
        </div>
    </header>

    <main class="flex-1">
        {{ $slot }}
    </main>

    <footer class="border-t border-gray-100 dark:border-white/10">
        <div class="mx-auto flex max-w-6xl flex-col items-center justify-between gap-3 px-6 py-8 text-sm text-gray-500 sm:flex-row">
            <span>&copy; {{ date('Y') }} {{ config('app.name') }}</span>
            <div class="flex gap-4">
                <a href="{{ url('/admin') }}" class="hover:text-gray-900 dark:hover:text-white">Staff sign in</a>
                <a href="{{ route('portal.login') }}" class="hover:text-gray-900 dark:hover:text-white">Customer sign in</a>
            </div>
        </div>
    </footer>
</body>
</html>
