<section class="mx-auto max-w-3xl rounded-[2rem] border border-white/10 bg-white/5 p-8 shadow-2xl shadow-black/30">
    <p class="text-xs font-semibold uppercase tracking-[0.35em] text-cyan-300">Create Account</p>
    <h1 class="mt-4 text-4xl font-semibold text-white">Join the game platform</h1>
    <p class="mt-3 text-sm leading-6 text-stone-300">
        Registration creates your player profile and wallet so you can deposit, enter games, and track results immediately.
    </p>

    <form wire:submit="register" class="mt-8 grid gap-4 md:grid-cols-2">
        <div class="md:col-span-2">
            <label for="name" class="mb-2 block text-sm text-stone-300">Full name</label>
            <input wire:model="name" id="name" type="text" class="w-full rounded-2xl border border-white/10 bg-stone-950/70 px-4 py-3 text-white outline-none placeholder:text-stone-500 focus:border-cyan-300" placeholder="Player name">
            @error('name') <p class="mt-2 text-sm text-rose-300">{{ $message }}</p> @enderror
        </div>

        <div class="md:col-span-2">
            <label for="email" class="mb-2 block text-sm text-stone-300">Email</label>
            <input wire:model="email" id="email" type="email" class="w-full rounded-2xl border border-white/10 bg-stone-950/70 px-4 py-3 text-white outline-none placeholder:text-stone-500 focus:border-cyan-300" placeholder="player@example.com">
            @error('email') <p class="mt-2 text-sm text-rose-300">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="password" class="mb-2 block text-sm text-stone-300">Password</label>
            <input wire:model="password" id="password" type="password" class="w-full rounded-2xl border border-white/10 bg-stone-950/70 px-4 py-3 text-white outline-none placeholder:text-stone-500 focus:border-cyan-300" placeholder="Minimum secure password">
            @error('password') <p class="mt-2 text-sm text-rose-300">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="password_confirmation" class="mb-2 block text-sm text-stone-300">Confirm password</label>
            <input wire:model="password_confirmation" id="password_confirmation" type="password" class="w-full rounded-2xl border border-white/10 bg-stone-950/70 px-4 py-3 text-white outline-none placeholder:text-stone-500 focus:border-cyan-300" placeholder="Repeat password">
        </div>

        <div class="md:col-span-2 flex flex-col gap-4 pt-2 sm:flex-row sm:items-center sm:justify-between">
            <p class="text-sm text-stone-400">
                Already registered?
                <a href="{{ route('login') }}" class="text-cyan-300 hover:text-cyan-200">Sign in</a>
            </p>

            <button type="submit" class="rounded-full bg-amber-400 px-6 py-3 font-semibold text-stone-950 transition hover:bg-amber-300">
                Create player account
            </button>
        </div>
    </form>
</section>
