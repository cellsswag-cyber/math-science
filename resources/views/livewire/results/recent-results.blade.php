<div class="rounded-[2rem] border border-white/10 bg-white/5 p-6">
    <div class="flex items-center justify-between">
        <h2 class="text-xl font-semibold text-white">Recent results</h2>
        <span class="text-xs uppercase tracking-[0.25em] text-stone-500">Cached</span>
    </div>

    <div class="mt-5 space-y-3">
        @forelse ($results as $result)
            <div class="rounded-3xl border border-white/10 bg-stone-950/60 p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="font-medium text-white">{{ $result['game_name'] }}</p>
                        <p class="text-xs text-stone-500">{{ $result['declared_at'] }}</p>
                    </div>
                    <span class="rounded-full bg-cyan-400/15 px-3 py-1 text-cyan-200">
                        {{ $result['winning_number'] }}
                    </span>
                </div>
            </div>
        @empty
            <p class="rounded-3xl border border-dashed border-white/10 px-4 py-8 text-center text-sm text-stone-400">
                No recent results are available.
            </p>
        @endforelse
    </div>
</div>
