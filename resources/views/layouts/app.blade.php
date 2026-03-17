<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="min-h-screen bg-stone-950 text-stone-100">
    <div class="min-h-screen bg-[radial-gradient(circle_at_top,rgba(245,158,11,0.22),transparent_28%),radial-gradient(circle_at_bottom_right,rgba(14,116,144,0.28),transparent_24%),linear-gradient(180deg,#0c0a09_0%,#1c1917_100%)]">
        <header class="border-b border-white/10 bg-stone-950/70 backdrop-blur">
            <div class="mx-auto flex max-w-7xl flex-col gap-4 px-4 py-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <a href="{{ auth()->check() ? route('dashboard') : route('login') }}" class="text-lg font-semibold tracking-[0.2em] text-amber-300 uppercase">
                        Math Science
                    </a>
                    <p class="text-sm text-stone-400">Realtime prediction platform MVP</p>
                </div>

                <nav class="flex flex-wrap items-center gap-2 text-sm">
                    @auth
                        <a href="{{ route('dashboard') }}" class="rounded-full px-3 py-2 {{ request()->routeIs('dashboard') ? 'bg-amber-400 text-stone-950' : 'bg-white/5 text-stone-200 hover:bg-white/10' }}">Dashboard</a>
                        <a href="{{ route('games.index') }}" class="rounded-full px-3 py-2 {{ request()->routeIs('games.*') ? 'bg-amber-400 text-stone-950' : 'bg-white/5 text-stone-200 hover:bg-white/10' }}">Games</a>
                        <a href="{{ route('wallet.index') }}" class="rounded-full px-3 py-2 {{ request()->routeIs('wallet.index') ? 'bg-amber-400 text-stone-950' : 'bg-white/5 text-stone-200 hover:bg-white/10' }}">Wallet</a>
                        <a href="{{ route('results.index') }}" class="rounded-full px-3 py-2 {{ request()->routeIs('results.index') ? 'bg-amber-400 text-stone-950' : 'bg-white/5 text-stone-200 hover:bg-white/10' }}">Results</a>
                        <a href="{{ route('history.index') }}" class="rounded-full px-3 py-2 {{ request()->routeIs('history.index') ? 'bg-amber-400 text-stone-950' : 'bg-white/5 text-stone-200 hover:bg-white/10' }}">History</a>
                        <a href="{{ route('profile.index') }}" class="rounded-full px-3 py-2 {{ request()->routeIs('profile.index') ? 'bg-amber-400 text-stone-950' : 'bg-white/5 text-stone-200 hover:bg-white/10' }}">Profile</a>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="rounded-full border border-white/15 px-3 py-2 text-stone-200 hover:border-amber-300 hover:text-amber-200">
                                Logout
                            </button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="rounded-full px-3 py-2 {{ request()->routeIs('login') ? 'bg-amber-400 text-stone-950' : 'bg-white/5 text-stone-200 hover:bg-white/10' }}">Login</a>
                        <a href="{{ route('register') }}" class="rounded-full px-3 py-2 {{ request()->routeIs('register') ? 'bg-amber-400 text-stone-950' : 'bg-white/5 text-stone-200 hover:bg-white/10' }}">Register</a>
                    @endauth
                </nav>
            </div>
        </header>

        <main class="mx-auto max-w-7xl px-4 py-8">
            @if (session()->has('success'))
                <div class="mb-6 rounded-3xl border border-emerald-400/30 bg-emerald-400/10 px-4 py-3 text-sm text-emerald-100">
                    {{ session('success') }}
                </div>
            @endif

            @if (session()->has('error'))
                <div class="mb-6 rounded-3xl border border-rose-400/30 bg-rose-400/10 px-4 py-3 text-sm text-rose-100">
                    {{ session('error') }}
                </div>
            @endif

            {{ $slot }}
        </main>
    </div>

    @livewireScripts
</body>
</html>
