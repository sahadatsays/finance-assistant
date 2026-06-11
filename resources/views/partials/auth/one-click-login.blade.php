@if ($oneClickLogin['enabled'] && count($oneClickLogin['accounts']) > 0)
    <div
        class="mb-6 rounded-xl border border-dashed border-brand/40 bg-brand/5 p-4"
        data-test="one-click-login"
    >
        <div class="mb-3 flex items-center gap-2">
            <svg class="size-4 text-brand" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
            </svg>
            <p class="text-sm font-medium text-heading">One-Click Login</p>
            <span class="rounded-full bg-brand/10 px-2 py-0.5 text-xs font-medium text-brand">Local</span>
        </div>

        <div class="grid gap-2">
            @foreach ($oneClickLogin['accounts'] as $account)
                <form method="POST" action="{{ route('dev.login', $account['id']) }}">
                    @csrf
                    <button
                        type="submit"
                        class="flex w-full items-center justify-between rounded-lg border border-gray-200 bg-white px-3 py-2 text-left transition hover:border-brand hover:bg-brand/5"
                        data-test="one-click-login-{{ $account['email'] }}"
                    >
                        <div class="min-w-0">
                            <p class="truncate text-sm font-medium text-heading">{{ $account['label'] }}</p>
                            <p class="truncate text-xs text-gray-500">{{ $account['description'] }}</p>
                        </div>
                        <span class="ml-2 shrink-0 text-xs text-gray-400">{{ $account['email'] }}</span>
                    </button>
                </form>
            @endforeach
        </div>
    </div>
@endif
