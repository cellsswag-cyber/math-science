<section class="mx-auto max-w-3xl rounded-[2rem] border border-white/10 bg-white/5 p-8 shadow-2xl shadow-black/30">
    <p class="text-xs font-semibold uppercase tracking-[0.35em] text-cyan-300">Profile</p>
    <h1 class="mt-3 text-4xl font-semibold text-white">Profile settings</h1>
    <p class="mt-2 text-sm leading-6 text-stone-300">
        Keep your account information current without leaving the Livewire interface.
    </p>

    <form wire:submit="save" class="mt-8 space-y-5">
        <div>
            <label for="profile-name" class="mb-2 block text-sm text-stone-300">Name</label>
            <input wire:model="name" id="profile-name" type="text" class="w-full rounded-2xl border border-white/10 bg-stone-950/70 px-4 py-3 text-white outline-none focus:border-cyan-300">
            @error('name') <p class="mt-2 text-sm text-rose-300">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="profile-email" class="mb-2 block text-sm text-stone-300">Email</label>
            <input wire:model="email" id="profile-email" type="email" class="w-full rounded-2xl border border-white/10 bg-stone-950/70 px-4 py-3 text-white outline-none focus:border-cyan-300">
            @error('email') <p class="mt-2 text-sm text-rose-300">{{ $message }}</p> @enderror
        </div>

        <div class="grid gap-4 md:grid-cols-2">
            <div>
                <label for="profile-password" class="mb-2 block text-sm text-stone-300">New password</label>
                <input wire:model="password" id="profile-password" type="password" class="w-full rounded-2xl border border-white/10 bg-stone-950/70 px-4 py-3 text-white outline-none focus:border-cyan-300">
                @error('password') <p class="mt-2 text-sm text-rose-300">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="profile-password_confirmation" class="mb-2 block text-sm text-stone-300">Confirm password</label>
                <input wire:model="password_confirmation" id="profile-password_confirmation" type="password" class="w-full rounded-2xl border border-white/10 bg-stone-950/70 px-4 py-3 text-white outline-none focus:border-cyan-300">
            </div>
        </div>

        <button type="submit" class="inline-flex items-center justify-center rounded-full bg-amber-400 px-6 py-3 font-semibold text-stone-950 hover:bg-amber-300">
            Save profile
        </button>
    </form>
</section>
