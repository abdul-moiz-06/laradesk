<x-filament-panels::page>
    <x-filament::section>
        <x-slot name="heading">Your support link</x-slot>
        <x-slot name="description">
            Put this link on your website so your customers can submit support tickets. It is signed and cannot be tampered with.
        </x-slot>

        @if ($url !== '')
            <input type="text" readonly value="{{ $url }}"
                class="w-full rounded-lg border border-gray-300 bg-gray-50 px-3 py-2 text-sm dark:border-white/10 dark:bg-gray-950">
        @else
            <p class="text-sm text-gray-500">No company is currently set.</p>
        @endif
    </x-filament::section>
</x-filament-panels::page>
