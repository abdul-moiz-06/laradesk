<x-portal.layout title="Submit a ticket">
    <div class="mx-auto max-w-lg rounded-xl border border-gray-200 bg-white p-6 dark:border-white/10 dark:bg-gray-900">
        <h1 class="text-xl font-semibold">Contact {{ $tenant->name }} support</h1>
        <p class="mt-2 text-gray-500">Tell us what is going on and we will get back to you by email.</p>

        <form method="POST" action="{{ request()->fullUrl() }}" class="mt-5 space-y-4">
            @csrf

            <div>
                <label for="name" class="block text-sm font-medium">Name</label>
                <input id="name" name="name" type="text" required value="{{ old('name') }}"
                    class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-white/10 dark:bg-gray-950">
                @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="email" class="block text-sm font-medium">Email</label>
                <input id="email" name="email" type="email" required value="{{ old('email') }}"
                    class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-white/10 dark:bg-gray-950">
                @error('email')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="subject" class="block text-sm font-medium">Subject</label>
                <input id="subject" name="subject" type="text" required value="{{ old('subject') }}"
                    class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-white/10 dark:bg-gray-950">
                @error('subject')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="message" class="block text-sm font-medium">Message</label>
                <textarea id="message" name="message" rows="5" required
                    class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-white/10 dark:bg-gray-950">{{ old('message') }}</textarea>
                @error('message')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            @if (config('services.turnstile.sitekey'))
                <div class="cf-turnstile" data-sitekey="{{ config('services.turnstile.sitekey') }}"></div>
                <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
            @endif

            <button type="submit"
                class="w-full rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-800 dark:bg-white dark:text-gray-900">
                Submit ticket
            </button>
        </form>
    </div>
</x-portal.layout>
