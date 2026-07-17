<div>
    <h1 class="text-xl font-semibold">My tickets</h1>

    <div class="mt-4 space-y-3">
        @forelse ($tickets as $ticket)
            <a href="{{ route('portal.tickets.show', $ticket->ulid) }}"
                class="block rounded-lg border border-gray-200 bg-white p-4 hover:border-gray-300 dark:border-white/10 dark:bg-gray-900">
                <div class="flex items-center justify-between gap-3">
                    <span class="font-medium">{{ $ticket->title }}</span>
                    <span class="rounded-full bg-gray-100 px-2 py-0.5 text-xs text-gray-600 dark:bg-white/10 dark:text-gray-300">
                        {{ ucfirst($ticket->status->value) }}
                    </span>
                </div>
                <p class="mt-1 text-sm text-gray-500">{{ $ticket->created_at->format('M j, Y') }}</p>
            </a>
        @empty
            <p class="text-gray-500">You have not submitted any tickets yet.</p>
        @endforelse
    </div>
</div>
