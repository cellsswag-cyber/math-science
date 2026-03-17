<div class="platform-shell">
    <div class="hero">
        <p class="eyebrow">Day 1 Backend Scaffold</p>
        <h1>Real-time game platform foundation</h1>
        <p class="copy">
            Livewire is installed only as a thin scaffold. The backend services, repositories, events,
            and test endpoints are the primary focus for this phase.
        </p>
    </div>

    <div class="stats">
        <article>
            <span>Users</span>
            <strong>{{ $userCount }}</strong>
        </article>
        <article>
            <span>Wallets</span>
            <strong>{{ $walletCount }}</strong>
        </article>
        <article>
            <span>Games</span>
            <strong>{{ $gameCount }}</strong>
        </article>
        <article>
            <span>Open Games</span>
            <strong>{{ $openGameCount }}</strong>
        </article>
        <article>
            <span>Entries</span>
            <strong>{{ $entryCount }}</strong>
        </article>
    </div>

    <div class="notes">
        <p>Testing routes:</p>
        <code>POST /test/games</code>
        <code>POST /test/deposit</code>
        <code>POST /test/entry</code>
        <code>GET /test/games</code>
        <code>POST /test/result</code>
    </div>
</div>
