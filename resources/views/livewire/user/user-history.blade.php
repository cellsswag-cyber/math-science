<section class="space-y-6">
    <div>
        <p class="text-xs font-semibold uppercase tracking-[0.35em] text-cyan-300">History</p>
        <h1 class="mt-3 text-4xl font-semibold text-white">Entry history</h1>
        <p class="mt-2 max-w-2xl text-sm leading-6 text-stone-300">
            Filter your predictions by date range, game, and result status.
        </p>
    </div>

    <div class="grid gap-4 rounded-[2rem] border border-white/10 bg-white/5 p-6 md:grid-cols-4">
        <div>
            <label for="from_date" class="mb-2 block text-sm text-stone-300">From</label>
            <input wire:model.live="from_date" id="from_date" type="date" class="w-full rounded-2xl border border-white/10 bg-stone-950/70 px-4 py-3 text-white outline-none focus:border-cyan-300">
        </div>
        <div>
            <label for="to_date" class="mb-2 block text-sm text-stone-300">To</label>
            <input wire:model.live="to_date" id="to_date" type="date" class="w-full rounded-2xl border border-white/10 bg-stone-950/70 px-4 py-3 text-white outline-none focus:border-cyan-300">
        </div>
        <div>
            <label for="game_id" class="mb-2 block text-sm text-stone-300">Game</label>
            <select wire:model.live="game_id" id="game_id" class="w-full rounded-2xl border border-white/10 bg-stone-950/70 px-4 py-3 text-white outline-none focus:border-cyan-300">
                <option value="">All games</option>
                @foreach ($games as $game)
                    <option value="{{ $game['id'] }}">{{ $game['name'] }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="status" class="mb-2 block text-sm text-stone-300">Status</label>
            <select wire:model.live="status" id="status" class="w-full rounded-2xl border border-white/10 bg-stone-950/70 px-4 py-3 text-white outline-none focus:border-cyan-300">
                <option value="">All statuses</option>
                <option value="pending">Pending</option>
                <option value="win">Win</option>
                <option value="lose">Lose</option>
                <option value="refunded">Refunded</option>
            </select>
        </div>
    </div>

    <div class="overflow-hidden rounded-[2rem] border border-white/10 bg-white/5">
        <table class="min-w-full divide-y divide-white/10 text-left">
            <thead class="bg-stone-950/70 text-xs uppercase tracking-[0.2em] text-stone-500">
                <tr>
                    <th class="px-4 py-3">Game</th>
                    <th class="px-4 py-3">Prediction</th>
                    <th class="px-4 py-3">Winning #</th>
                    <th class="px-4 py-3">Amount</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3">Played</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-white/10 bg-white/5 text-sm text-stone-200">
                @forelse ($entries as $entry)
                    <tr>
                        <td class="px-4 py-3">{{ $entry['game_name'] }}</td>
                        <td class="px-4 py-3">{{ $entry['prediction_number'] }}</td>
                        <td class="px-4 py-3">{{ $entry['winning_number'] ?? 'Pending' }}</td>
                        <td class="px-4 py-3">{{ $entry['amount'] }}</td>
                        <td class="px-4 py-3 capitalize">{{ $entry['status'] }}</td>
                        <td class="px-4 py-3 text-xs text-stone-400">{{ $entry['created_at'] }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-stone-400">No entries match the selected filters.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>
        {{ $entries->links() }}
    </div>
</section>
