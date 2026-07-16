<x-portal.layout title="Home">
    <x-slot:header>
        <form method="POST" action="{{ route('portal.logout') }}">
            @csrf
            <button type="submit" class="text-sm text-gray-500 hover:text-gray-900 dark:hover:text-white">Sign out</button>
        </form>
    </x-slot:header>

    <div class="rounded-xl border border-gray-200 bg-white p-6 dark:border-white/10 dark:bg-gray-900">
        <h1 class="text-xl font-semibold">
            Welcome{{ auth('customer')->user()?->name ? ', '.auth('customer')->user()->name : '' }}
        </h1>
        <p class="mt-2 text-gray-500">Your support tickets will appear here.</p>
    </div>
</x-portal.layout>
