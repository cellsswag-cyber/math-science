<section class="grid gap-6 xl:grid-cols-[1.1fr_0.9fr]">
    <div class="rounded-[2rem] border border-white/10 bg-white/5 p-6">
        <div class="flex items-center justify-between gap-3">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.35em] text-cyan-300">Game Play</p>
                <h1 class="mt-3 text-4xl font-semibold text-white">{{ $payload['game']['name'] }}</h1>
            </div>
            <span class="rounded-full bg-white/10 px-3 py-1 text-xs text-stone-200">{{ strtoupper($payload['game']['status']) }}</span>
        </div>

        <div class="mt-6 rounded-3xl border border-white/10 bg-stone-950/70 p-4">
            <livewire:games.game-timer
                :target-time="$payload['game']['countdown_target']"
                :context="$payload['game']['countdown_type']"
                :key="'play-timer-'.$payload['game']['id'].'-'.$payload['game']['countdown_target']"
            />
        </div>

        <form wire:submit="placeEntry" class="mt-6 space-y-5">
            <div>
                <label for="prediction_number" class="mb-2 block text-sm text-stone-300">Prediction number</label>
                <input wire:model="prediction_number" id="prediction_number" type="number" min="0" max="99" class="w-full rounded-2xl border border-white/10 bg-stone-950/70 px-4 py-3 text-white outline-none focus:border-cyan-300" placeholder="Choose 0-99">
                @error('prediction_number') <p class="mt-2 text-sm text-rose-300">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-5 gap-2">
                @foreach (range(0, 9) as $number)
                    <button type="button" wire:click="$set('prediction_number', {{ $number }})" class="rounded-2xl border border-white/10 bg-white/5 px-3 py-3 text-sm text-stone-200 hover:border-amber-300 hover:text-amber-200">
                        {{ str_pad((string) $number, 2, '0', STR_PAD_LEFT) }}
                    </button>
                @endforeach
            </div>

            <div>
                <label for="amount" class="mb-2 block text-sm text-stone-300">Entry amount</label>
                <input wire:model="amount" id="amount" type="number" min="0.01" step="0.01" class="w-full rounded-2xl border border-white/10 bg-stone-950/70 px-4 py-3 text-white outline-none focus:border-cyan-300" placeholder="Enter amount">
                @error('amount') <p class="mt-2 text-sm text-rose-300">{{ $message }}</p> @enderror
            </div>

            <button type="submit" class="inline-flex w-full items-center justify-center rounded-full bg-amber-400 px-5 py-3 font-semibold text-stone-950 transition hover:bg-amber-300">
                Submit prediction
            </button>
        </form>
    </div>

    <div class="space-y-6">
        <div class="rounded-[2rem] border border-white/10 bg-stone-950/70 p-6">
            <p class="text-sm text-stone-400">Available balance</p>
            <p class="mt-3 text-4xl font-semibold text-white">{{ $wallet['available_balance'] }}</p>
            <p class="mt-2 text-sm text-stone-400">Locked balance {{ $wallet['locked_balance'] }}</p>
        </div>

        <div class="rounded-[2rem] border border-white/10 bg-white/5 p-6">
            <h2 class="text-xl font-semibold text-white">Your entries in this game</h2>
            <div class="mt-5 space-y-3">
                @forelse ($payload['user_entries'] as $entry)
                    <div class="rounded-3xl border border-white/10 bg-stone-950/60 p-4">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-stone-300">Prediction {{ $entry['prediction_number'] }}</span>
                            <span class="rounded-full px-3 py-1 text-xs {{ $entry['status'] === 'win' ? 'bg-emerald-400/15 text-emerald-200' : ($entry['status'] === 'lose' ? 'bg-rose-400/15 text-rose-200' : 'bg-amber-400/15 text-amber-200') }}">
                                {{ strtoupper($entry['status']) }}
                            </span>
                        </div>
                        <div class="mt-3 flex items-center justify-between text-sm text-stone-400">
                            <span>Amount {{ $entry['amount'] }}</span>
                            <span>{{ $entry['created_at'] }}</span>
                        </div>
                    </div>
                @empty
                    <p class="rounded-3xl border border-dashed border-white/10 px-4 py-8 text-center text-sm text-stone-400">
                        No entries placed in this game yet.
                    </p>
                @endforelse
            </div>
        </div>
    </div>
</section>
