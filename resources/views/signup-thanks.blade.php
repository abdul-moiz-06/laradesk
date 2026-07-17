<x-marketing.layout title="Check your email">
    <section class="mx-auto max-w-md px-6 py-20 text-center">
        <h1 class="text-2xl font-bold">Your workspace is ready</h1>
        <p class="mt-3 text-gray-600 dark:text-gray-300">
            We have emailed you a link to set your password and sign in to your admin panel.
        </p>
        <a href="{{ url('/admin') }}"
            class="mt-6 inline-block rounded-lg border border-gray-300 px-6 py-3 text-sm font-semibold hover:bg-gray-50 dark:border-white/10 dark:hover:bg-white/5">
            Go to sign in
        </a>
    </section>
</x-marketing.layout>
