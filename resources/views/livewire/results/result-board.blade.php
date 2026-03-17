<section wire:poll.10s class="space-y-6">
    <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.35em] text-cyan-300">Results</p>
            <h1 class="mt-3 text-4xl font-semibold text-white">Result board</h1>
            <p class="mt-2 max-w-2xl text-sm leading-6 text-stone-300">
                Recent results, game history, and leaderboard refresh every 10 seconds.
            </p>
        </div>
    </div>

    <div class="grid gap-6 xl:grid-cols-[1.2fr_1fr]">
        <div class="rounded-[2rem] border border-white/10 bg-white/5 p-6">
            <h2 class="text-xl font-semibold text-white">Game history</h2>
            <div class="mt-5 space-y-3">
                @forelse ($board['game_history'] as $game)
                    <div class="rounded-3xl border border-white/10 bg-stone-950/60 p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="font-medium text-white">{{ $game['name'] }}</p>
                                <p class="text-xs uppercase tracking-[0.25em] text-stone-500">{{ strtoupper($game['status']) }}</p>
                            </div>
                            <span class="rounded-full bg-cyan-400/15 px-3 py-1 text-cyan-200">
                                {{ $game['winning_number'] ?? 'Pending' }}
                            </span>
                        </div>
                    </div>
                @empty
                    <p class="rounded-3xl border border-dashed border-white/10 px-4 py-8 text-center text-sm text-stone-400">
                        Result history will populate once games settle.
                    </p>
                @endforelse
            </div>
        </div>

        <div class="space-y-6">
            <livewire:results.recent-results />

            <div class="rounded-[2rem] border border-white/10 bg-stone-950/70 p-6">
                <div class="flex items-center justify-between">
                    <h2 class="text-xl font-semibold text-white">Leaderboard</h2>
                    <span class="text-xs uppercase tracking-[0.25em] text-stone-500">Cached</span>
                </div>
                <div class="mt-5 space-y-3">
                    @forelse ($board['leaderboard'] as $entry)
                        <div class="flex items-center justify-between rounded-3xl border border-white/10 bg-white/5 px-4 py-4">
                            <div>
                                <p class="font-medium text-white">{{ $entry['user_name'] }}</p>
                                <p class="text-xs uppercase tracking-[0.25em] text-stone-500">Player {{ $entry['user_id'] }}</p>
                            </div>
                            <span class="rounded-full bg-amber-400/15 px-3 py-1 text-amber-200">{{ $entry['total_winnings'] }}</span>
                        </div>
                    @empty
                        <p class="rounded-3xl border border-dashed border-white/10 px-4 py-8 text-center text-sm text-stone-400">
                            No winnings have been recorded yet.
                        </p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</section>
