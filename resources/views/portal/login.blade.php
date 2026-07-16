<x-portal.layout title="Sign in">
    <div class="mx-auto max-w-md rounded-xl border border-gray-200 bg-white p-6 dark:border-white/10 dark:bg-gray-900">
        <h1 class="text-xl font-semibold">Sign in to support</h1>
        <p class="mt-2 text-gray-500">Enter your email and we will send you a secure sign-in link.</p>

        @if (session('status'))
            <div class="mt-4 rounded-lg bg-green-50 px-4 py-3 text-sm text-green-700 dark:bg-green-500/10 dark:text-green-400">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('portal.login.store') }}" class="mt-5 space-y-4">
            @csrf
            <div>
                <label for="email" class="block text-sm font-medium">Email</label>
                <input id="email" name="email" type="email" required autofocus
                    value="{{ old('email') }}"
                    class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-white/10 dark:bg-gray-950">
                @error('email')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <button type="submit"
                class="w-full rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-800 dark:bg-white dark:text-gray-900">
                Send sign-in link
            </button>
        </form>
    </div>
</x-portal.layout>
