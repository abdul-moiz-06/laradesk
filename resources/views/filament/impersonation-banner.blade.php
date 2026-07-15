<div style="position:sticky;top:0;z-index:50;">
    <div class="flex items-center justify-between gap-4 bg-amber-500 px-4 py-2 text-sm text-white dark:bg-amber-600">
        <span>
            Impersonating <strong>{{ auth()->user()?->name }}</strong>
            ({{ auth()->user()?->email }}). Platform operator: {{ session('impersonator_email') }}.
        </span>
        <form method="POST" action="{{ route('impersonation.stop') }}" class="shrink-0">
            @csrf
            <button type="submit" class="rounded-md bg-white/20 px-3 py-1 font-medium hover:bg-white/30">
                Return to platform
            </button>
        </form>
    </div>
</div>
