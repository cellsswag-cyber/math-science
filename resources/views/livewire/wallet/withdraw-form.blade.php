<form wire:submit="submit" class="rounded-[2rem] border border-white/10 bg-stone-950/70 p-6">
    <h2 class="text-xl font-semibold text-white">Withdraw request</h2>
    <p class="mt-2 text-sm text-stone-400">Requests are stored as pending and can be processed from the admin panel.</p>

    <div class="mt-5">
        <label for="withdraw-amount" class="mb-2 block text-sm text-stone-300">Amount</label>
        <input wire:model="amount" id="withdraw-amount" type="number" min="0.01" step="0.01" class="w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-white outline-none focus:border-cyan-300" placeholder="50.00">
        @error('amount') <p class="mt-2 text-sm text-rose-300">{{ $message }}</p> @enderror
    </div>

    <button type="submit" class="mt-5 inline-flex w-full items-center justify-center rounded-full border border-white/10 px-5 py-3 font-semibold text-stone-100 hover:border-amber-300 hover:text-amber-200">
        Request withdrawal
    </button>
</form>
