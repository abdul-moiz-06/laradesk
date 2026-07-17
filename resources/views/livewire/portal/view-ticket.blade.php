<div>
    <a href="{{ route('portal.home') }}" class="text-sm text-gray-500 hover:text-gray-900 dark:hover:text-white">
        Back to my tickets
    </a>

    <h1 class="mt-2 text-xl font-semibold">{{ $ticket->title }}</h1>
    <p class="text-sm text-gray-500">{{ ucfirst($ticket->status->value) }} &middot; {{ $ticket->created_at->format('M j, Y') }}</p>

    <div class="mt-4 rounded-lg border border-gray-200 bg-white p-4 dark:border-white/10 dark:bg-gray-900">
        <p class="whitespace-pre-line">{{ $ticket->description }}</p>
    </div>

    <div class="mt-6 space-y-3">
        @foreach ($ticket->publishedReplies as $reply)
            <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-white/10 dark:bg-gray-900">
                <div class="text-sm font-medium">{{ $reply->author->name }}</div>
                <p class="mt-1 whitespace-pre-line">{{ $reply->body }}</p>
                <p class="mt-1 text-xs text-gray-400">{{ $reply->created_at->format('M j, Y g:i A') }}</p>
            </div>
        @endforeach
    </div>

    @if ($ticket->status === \App\Enums\TicketStatus::Closed)
        <p class="mt-6 text-sm text-gray-500">This ticket is closed.</p>
    @else
        <form wire:submit="reply" class="mt-6 space-y-3">
            <textarea wire:model="body" rows="4" placeholder="Write a reply..."
                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-white/10 dark:bg-gray-950"></textarea>
            @error('body')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
            <button type="submit"
                class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-800 dark:bg-white dark:text-gray-900">
                Send reply
            </button>
        </form>
    @endif
</div>
