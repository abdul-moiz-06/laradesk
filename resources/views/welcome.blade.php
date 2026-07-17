<x-marketing.layout title="LaraDesk, AI-powered support for every company">
    <section class="mx-auto max-w-6xl px-6 py-20 text-center sm:py-28">
        <h1 class="text-4xl font-bold tracking-tight sm:text-5xl">AI-powered support, for every company</h1>
        <p class="mx-auto mt-5 max-w-2xl text-lg text-gray-600 dark:text-gray-300">
            {{ config('app.name') }} triages every ticket with AI, keeps each company's data fully separated, and gives your
            agents and customers a clean place to work. Set up in minutes.
        </p>
        <div class="mt-8 flex justify-center gap-3">
            <a href="{{ route('signup') }}"
                class="rounded-lg bg-gray-900 px-6 py-3 text-sm font-semibold text-white hover:bg-gray-800 dark:bg-white dark:text-gray-900">
                Start free
            </a>
            <a href="{{ url('/admin') }}"
                class="rounded-lg border border-gray-300 px-6 py-3 text-sm font-semibold hover:bg-gray-50 dark:border-white/10 dark:hover:bg-white/5">
                Log in
            </a>
        </div>
    </section>

    <section class="border-t border-gray-100 dark:border-white/10">
        <div class="mx-auto max-w-6xl px-6 py-16">
            <h2 class="text-center text-2xl font-bold">Everything a support desk needs</h2>
            <div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ([
                    ['AI triage', 'Every ticket is read by AI and tagged with a category, priority, and sentiment, plus a draft reply, so agents start ahead.'],
                    ['True multi-tenancy', "Each company sees only its own data, enforced in the app and again at the database with row-level security."],
                    ['Agent workspace', 'A fast panel to work the queue: reply, assign, and close, with granular permissions and required two-factor.'],
                    ['Customer portal', 'Customers submit, track, and reply to tickets with passwordless magic-link sign-in. No password to forget.'],
                    ['Audit trail', 'A tamper-evident, append-only record of every staff action, with who did what and from where.'],
                    ['Built to scale', 'Background AI and email run on Redis-backed queues supervised by Horizon, so nothing blocks the request.'],
                ] as [$feature, $body])
                    <div class="rounded-xl border border-gray-200 p-6 dark:border-white/10">
                        <h3 class="font-semibold">{{ $feature }}</h3>
                        <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">{{ $body }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="border-t border-gray-100 dark:border-white/10">
        <div class="mx-auto max-w-6xl px-6 py-16 text-center">
            <h2 class="text-2xl font-bold">Ready to get started?</h2>
            <p class="mt-3 text-gray-600 dark:text-gray-300">Create your company workspace in a minute.</p>
            <a href="{{ route('signup') }}"
                class="mt-6 inline-block rounded-lg bg-gray-900 px-6 py-3 text-sm font-semibold text-white hover:bg-gray-800 dark:bg-white dark:text-gray-900">
                Start free
            </a>
        </div>
    </section>
</x-marketing.layout>
