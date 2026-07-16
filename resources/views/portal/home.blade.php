<x-portal.layout title="Home">
    <div class="rounded-xl border border-gray-200 bg-white p-6 dark:border-white/10 dark:bg-gray-900">
        <h1 class="text-xl font-semibold">
            Welcome{{ auth('customer')->user()?->name ? ', '.auth('customer')->user()->name : '' }}
        </h1>
        <p class="mt-2 text-gray-500">Your support tickets will appear here.</p>
    </div>
</x-portal.layout>
