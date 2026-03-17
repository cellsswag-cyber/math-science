<section wire:poll.5s class="space-y-6">
    <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.35em] text-cyan-300">Games</p>
            <h1 class="mt-3 text-4xl font-semibold text-white">Active games</h1>
            <p class="mt-2 max-w-2xl text-sm leading-6 text-stone-300">
                Live game states refresh every 5 seconds for an MVP-friendly realtime feel without websockets.
            </p>
        </div>
        <a href="{{ route('results.index') }}" class="inline-flex items-center justify-center rounded-full border border-white/10 px-4 py-3 text-sm text-stone-200 hover:border-cyan-300 hover:text-cyan-200">
            View recent results
        </a>
    </div>

    <div class="grid gap-4 lg:grid-cols-2">
        @forelse ($games as $game)
            <article class="rounded-[2rem] border border-white/10 bg-white/5 p-6">
                <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                    <div>
                        <p class="text-xs uppercase tracking-[0.25em] text-stone-500">{{ strtoupper(str_replace('_', ' ', $game['status'])) }}</p>
                        <h2 class="mt-2 text-2xl font-semibold text-white">{{ $game['name'] }}</h2>
                        <p class="mt-2 text-sm text-stone-400">{{ $game['entries_count'] }} total entries</p>
                    </div>
                    <span class="rounded-full px-3 py-1 text-xs {{ $game['has_entry'] ? 'bg-amber-400/15 text-amber-200' : 'bg-white/10 text-stone-200' }}">
                        {{ $game['entry_label'] ?? 'No entry yet' }}
                    </span>
                </div>

                <div class="mt-5 rounded-3xl border border-white/10 bg-stone-950/60 p-4">
                    <livewire:games.game-timer
                        :target-time="$game['countdown_target']"
                        :context="$game['countdown_type']"
                        :key="'game-timer-'.$game['id'].'-'.$game['countdown_target']"
                    />
                </div>

                <div class="mt-5 flex flex-col gap-3 text-sm text-stone-400">
                    <div class="flex items-center justify-between">
                        <span>Open</span>
                        <span>{{ $game['open_time'] }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span>Close</span>
                        <span>{{ $game['close_time'] }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span>Result</span>
                        <span>{{ $game['result_time'] }}</span>
                    </div>
                </div>

                <div class="mt-6">
                    <a href="{{ route('games.play', $game['id']) }}" class="inline-flex w-full items-center justify-center rounded-full bg-amber-400 px-4 py-3 font-semibold text-stone-950 transition hover:bg-amber-300">
                        {{ $game['status'] === 'open' ? 'Enter game' : 'View game' }}
                    </a>
                </div>
            </article>
        @empty
            <div class="rounded-[2rem] border border-dashed border-white/10 bg-white/5 p-10 text-center text-sm text-stone-400 lg:col-span-2">
                There are no active games right now.
            </div>
        @endforelse
    </div>
</section>
