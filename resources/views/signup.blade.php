<x-marketing.layout title="Sign up">
    <section class="mx-auto max-w-md px-6 py-16">
        <h1 class="text-2xl font-bold">Create your company workspace</h1>
        <p class="mt-2 text-gray-600 dark:text-gray-300">We will email you a link to set your password and sign in.</p>

        <form method="POST" action="{{ route('signup.store') }}" class="mt-6 space-y-4">
            @csrf

            <div>
                <label for="company_name" class="block text-sm font-medium">Company name</label>
                <input id="company_name" name="company_name" type="text" required value="{{ old('company_name') }}"
                    class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-white/10 dark:bg-gray-900">
                @error('company_name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="name" class="block text-sm font-medium">Your name</label>
                <input id="name" name="name" type="text" required value="{{ old('name') }}"
                    class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-white/10 dark:bg-gray-900">
                @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="email" class="block text-sm font-medium">Work email</label>
                <input id="email" name="email" type="email" required value="{{ old('email') }}"
                    class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-white/10 dark:bg-gray-900">
                @error('email')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            @if (config('services.turnstile.sitekey'))
                <div class="cf-turnstile" data-sitekey="{{ config('services.turnstile.sitekey') }}"></div>
                <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
            @endif

            <button type="submit"
                class="w-full rounded-lg bg-gray-900 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-800 dark:bg-white dark:text-gray-900">
                Create workspace
            </button>
        </form>

        <p class="mt-4 text-sm text-gray-500">
            Already have an account? <a href="{{ url('/admin') }}" class="font-medium underline">Log in</a>
        </p>
    </section>
</x-marketing.layout>
