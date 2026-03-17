<section class="mx-auto grid max-w-5xl gap-6 lg:grid-cols-[1.1fr_0.9fr]">
    <div class="rounded-[2rem] border border-white/10 bg-white/5 p-8 shadow-2xl shadow-black/30">
        <p class="text-xs font-semibold uppercase tracking-[0.35em] text-cyan-300">Player Access</p>
        <h1 class="mt-4 text-4xl font-semibold text-white">Sign in to your game wallet</h1>
        <p class="mt-4 max-w-xl text-sm leading-6 text-stone-300">
            Track open games, lock entries in real time, and monitor wallet movement from one place.
        </p>
        <div class="mt-8 grid gap-4 md:grid-cols-3">
            <div class="rounded-3xl border border-white/10 bg-stone-900/60 p-4">
                <p class="text-xs uppercase tracking-[0.2em] text-stone-400">Realtime</p>
                <p class="mt-3 text-lg font-medium text-amber-200">Live polling for games, wallet, and results</p>
            </div>
            <div class="rounded-3xl border border-white/10 bg-stone-900/60 p-4">
                <p class="text-xs uppercase tracking-[0.2em] text-stone-400">Protected</p>
                <p class="mt-3 text-lg font-medium text-amber-200">Funds are locked and settled through services</p>
            </div>
            <div class="rounded-3xl border border-white/10 bg-stone-900/60 p-4">
                <p class="text-xs uppercase tracking-[0.2em] text-stone-400">Automated</p>
                <p class="mt-3 text-lg font-medium text-amber-200">Lifecycle commands move games forward</p>
            </div>
        </div>
    </div>

    <form wire:submit="login" class="rounded-[2rem] border border-white/10 bg-stone-950/80 p-8 shadow-2xl shadow-black/30">
        <h2 class="text-2xl font-semibold text-white">Login</h2>
        <p class="mt-2 text-sm text-stone-400">Use the credentials created in your seed data or your registered account.</p>

        <div class="mt-6 space-y-4">
            <div>
                <label for="email" class="mb-2 block text-sm text-stone-300">Email</label>
                <input wire:model="email" id="email" type="email" class="w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-white outline-none ring-0 placeholder:text-stone-500 focus:border-cyan-300" placeholder="player@example.com">
                @error('email') <p class="mt-2 text-sm text-rose-300">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="password" class="mb-2 block text-sm text-stone-300">Password</label>
                <input wire:model="password" id="password" type="password" class="w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-white outline-none ring-0 placeholder:text-stone-500 focus:border-cyan-300" placeholder="password">
                @error('password') <p class="mt-2 text-sm text-rose-300">{{ $message }}</p> @enderror
            </div>
        </div>

        <label class="mt-4 flex items-center gap-3 text-sm text-stone-300">
            <input wire:model="remember" type="checkbox" class="rounded border-white/20 bg-white/5 text-amber-400">
            Remember me
        </label>

        <button type="submit" class="mt-6 w-full rounded-full bg-amber-400 px-5 py-3 font-semibold text-stone-950 transition hover:bg-amber-300">
            Enter dashboard
        </button>

        <p class="mt-4 text-sm text-stone-400">
            Need an account?
            <a href="{{ route('register') }}" class="text-cyan-300 hover:text-cyan-200">Create one</a>
        </p>
    </form>
</section>
