<section class="space-y-8">
    <div class="grid gap-4 lg:grid-cols-[1.2fr_0.8fr]">
        <div class="rounded-[2rem] border border-white/10 bg-white/5 p-6">
            <p class="text-xs font-semibold uppercase tracking-[0.35em] text-cyan-300">Dashboard</p>
            <h1 class="mt-4 text-4xl font-semibold text-white">Welcome back, {{ $dashboard['user']['name'] }}</h1>
            <p class="mt-3 max-w-2xl text-sm leading-6 text-stone-300">
                Your game wallet, live entries, and result visibility are all connected through the service layer now.
            </p>
        </div>

        <div class="rounded-[2rem] border border-white/10 bg-stone-950/70 p-6">
            <p class="text-sm text-stone-400">Unread notifications</p>
            <p class="mt-3 text-4xl font-semibold text-amber-300">{{ count($dashboard['notifications']) }}</p>
            <p class="mt-2 text-sm text-stone-400">Wins, results, and withdrawal decisions show up here.</p>
        </div>
    </div>

    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-[2rem] border border-white/10 bg-white/5 p-5">
            <p class="text-sm text-stone-400">Available balance</p>
            <p class="mt-3 text-3xl font-semibold text-white">{{ $dashboard['wallet']['available_balance'] }}</p>
        </div>
        <div class="rounded-[2rem] border border-white/10 bg-white/5 p-5">
            <p class="text-sm text-stone-400">Locked balance</p>
            <p class="mt-3 text-3xl font-semibold text-white">{{ $dashboard['wallet']['locked_balance'] }}</p>
        </div>
        <div class="rounded-[2rem] border border-white/10 bg-white/5 p-5">
            <p class="text-sm text-stone-400">Wins</p>
            <p class="mt-3 text-3xl font-semibold text-emerald-300">{{ $dashboard['stats']['wins'] }}</p>
        </div>
        <div class="rounded-[2rem] border border-white/10 bg-white/5 p-5">
            <p class="text-sm text-stone-400">Losses</p>
            <p class="mt-3 text-3xl font-semibold text-rose-300">{{ $dashboard['stats']['losses'] }}</p>
        </div>
    </div>

    <div class="grid gap-6 xl:grid-cols-[1fr_1fr_0.9fr]">
        <div class="rounded-[2rem] border border-white/10 bg-white/5 p-6">
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold text-white">Recent entries</h2>
                <a href="{{ route('history.index') }}" class="text-sm text-cyan-300 hover:text-cyan-200">View history</a>
            </div>
            <div class="mt-5 space-y-3">
                @forelse ($dashboard['recent_entries'] as $entry)
                    <div class="rounded-3xl border border-white/10 bg-stone-950/60 p-4">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <p class="font-medium text-white">{{ $entry['game_name'] }}</p>
                                <p class="text-xs uppercase tracking-[0.25em] text-stone-500">Prediction {{ $entry['prediction_number'] }}</p>
                            </div>
                            <span class="rounded-full px-3 py-1 text-xs {{ $entry['status'] === 'win' ? 'bg-emerald-400/15 text-emerald-200' : ($entry['status'] === 'lose' ? 'bg-rose-400/15 text-rose-200' : 'bg-amber-400/15 text-amber-200') }}">
                                {{ strtoupper($entry['status']) }}
                            </span>
                        </div>
                        <div class="mt-4 flex items-center justify-between text-sm text-stone-400">
                            <span>Amount {{ $entry['amount'] }}</span>
                            <span>Winning number {{ $entry['winning_number'] ?? 'Pending' }}</span>
                        </div>
                    </div>
                @empty
                    <p class="rounded-3xl border border-dashed border-white/10 px-4 py-8 text-center text-sm text-stone-400">
                        You have not played a game yet.
                    </p>
                @endforelse
            </div>
        </div>

        <div class="rounded-[2rem] border border-white/10 bg-white/5 p-6">
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold text-white">Recent results</h2>
                <a href="{{ route('results.index') }}" class="text-sm text-cyan-300 hover:text-cyan-200">Open board</a>
            </div>
            <div class="mt-5 space-y-3">
                @forelse ($dashboard['recent_results'] as $result)
                    <div class="rounded-3xl border border-white/10 bg-stone-950/60 p-4">
                        <p class="font-medium text-white">{{ $result['game_name'] }}</p>
                        <div class="mt-3 flex items-center justify-between text-sm text-stone-400">
                            <span>Winning number</span>
                            <span class="rounded-full bg-cyan-400/15 px-3 py-1 text-cyan-200">{{ $result['winning_number'] }}</span>
                        </div>
                    </div>
                @empty
                    <p class="rounded-3xl border border-dashed border-white/10 px-4 py-8 text-center text-sm text-stone-400">
                        Results will appear here after your games settle.
                    </p>
                @endforelse
            </div>
        </div>

        <div class="rounded-[2rem] border border-white/10 bg-stone-950/70 p-6">
            <h2 class="text-xl font-semibold text-white">Latest notifications</h2>
            <div class="mt-5 space-y-3">
                @forelse ($dashboard['notifications'] as $notification)
                    <div class="rounded-3xl border border-white/10 bg-white/5 p-4">
                        <p class="font-medium text-white">{{ $notification['data']['title'] ?? $notification['type'] }}</p>
                        <p class="mt-2 text-sm leading-6 text-stone-300">{{ $notification['data']['message'] ?? 'Update available.' }}</p>
                    </div>
                @empty
                    <p class="rounded-3xl border border-dashed border-white/10 px-4 py-8 text-center text-sm text-stone-400">
                        Notifications will appear once results and withdrawals start flowing.
                    </p>
                @endforelse
            </div>
        </div>
    </div>
</section>
