<section wire:poll.5s class="space-y-6">
    <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.35em] text-cyan-300">Wallet</p>
            <h1 class="mt-3 text-4xl font-semibold text-white">Balance and transactions</h1>
            <p class="mt-2 max-w-2xl text-sm leading-6 text-stone-300">
                Available and locked balances refresh every 5 seconds to keep the wallet view current.
            </p>
        </div>
    </div>

    <div class="grid gap-4 md:grid-cols-3">
        <div class="rounded-[2rem] border border-white/10 bg-white/5 p-6">
            <p class="text-sm text-stone-400">Available balance</p>
            <p class="mt-3 text-4xl font-semibold text-white">{{ $wallet['available_balance'] }}</p>
        </div>
        <div class="rounded-[2rem] border border-white/10 bg-white/5 p-6">
            <p class="text-sm text-stone-400">Locked balance</p>
            <p class="mt-3 text-4xl font-semibold text-white">{{ $wallet['locked_balance'] }}</p>
        </div>
        <div class="rounded-[2rem] border border-white/10 bg-stone-950/70 p-6">
            <p class="text-sm text-stone-400">Total wallet value</p>
            <p class="mt-3 text-4xl font-semibold text-amber-300">{{ $wallet['total_balance'] }}</p>
        </div>
    </div>

    <div class="grid gap-6 xl:grid-cols-[0.8fr_0.8fr_1.4fr]">
        <livewire:wallet.deposit-form />
        <livewire:wallet.withdraw-form />

        <div class="rounded-[2rem] border border-white/10 bg-white/5 p-6">
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold text-white">Recent transactions</h2>
                <span class="text-xs uppercase tracking-[0.25em] text-stone-500">Live</span>
            </div>
            <div class="mt-5 space-y-3">
                @forelse ($recentTransactions as $transaction)
                    <div class="rounded-3xl border border-white/10 bg-stone-950/60 p-4">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <p class="font-medium capitalize text-white">{{ str_replace('_', ' ', $transaction['type']) }}</p>
                                <p class="text-xs text-stone-500">{{ $transaction['reference'] }}</p>
                            </div>
                            <span class="rounded-full px-3 py-1 text-xs {{ $transaction['status'] === 'completed' ? 'bg-emerald-400/15 text-emerald-200' : ($transaction['status'] === 'pending' ? 'bg-amber-400/15 text-amber-200' : 'bg-rose-400/15 text-rose-200') }}">
                                {{ strtoupper($transaction['status']) }}
                            </span>
                        </div>
                        <div class="mt-3 flex items-center justify-between text-sm text-stone-400">
                            <span>Amount {{ $transaction['amount'] }}</span>
                            <span>{{ $transaction['created_at'] }}</span>
                        </div>
                    </div>
                @empty
                    <p class="rounded-3xl border border-dashed border-white/10 px-4 py-8 text-center text-sm text-stone-400">
                        No transactions recorded yet.
                    </p>
                @endforelse
            </div>
        </div>
    </div>

    <livewire:wallet.transaction-history />
</section>
