<div class="rounded-[2rem] border border-white/10 bg-white/5 p-6">
    <div class="flex items-center justify-between">
        <h2 class="text-xl font-semibold text-white">Transaction history</h2>
        <span class="text-xs uppercase tracking-[0.25em] text-stone-500">Paginated</span>
    </div>

    <div class="mt-5 overflow-hidden rounded-3xl border border-white/10">
        <table class="min-w-full divide-y divide-white/10 text-left">
            <thead class="bg-stone-950/70 text-xs uppercase tracking-[0.2em] text-stone-500">
                <tr>
                    <th class="px-4 py-3">Type</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3">Amount</th>
                    <th class="px-4 py-3">Reference</th>
                    <th class="px-4 py-3">Created</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-white/10 bg-white/5 text-sm text-stone-200">
                @forelse ($transactions as $transaction)
                    <tr>
                        <td class="px-4 py-3 capitalize">{{ str_replace('_', ' ', $transaction['type']) }}</td>
                        <td class="px-4 py-3">{{ $transaction['status'] }}</td>
                        <td class="px-4 py-3">{{ $transaction['amount'] }}</td>
                        <td class="px-4 py-3 text-xs text-stone-400">{{ $transaction['reference'] }}</td>
                        <td class="px-4 py-3 text-xs text-stone-400">{{ $transaction['created_at'] }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-8 text-center text-stone-400">No transactions found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $transactions->links() }}
    </div>
</div>
