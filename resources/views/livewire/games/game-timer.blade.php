<div class="flex items-center justify-between gap-4">
    <div>
        <p class="text-xs uppercase tracking-[0.25em] text-stone-500">{{ $contextLabel }}</p>
        <p class="mt-2 text-2xl font-semibold text-white">{{ $timer['label'] }}</p>
    </div>
    <div class="rounded-full px-3 py-1 text-xs {{ $timer['is_expired'] ? 'bg-rose-400/15 text-rose-200' : 'bg-cyan-400/15 text-cyan-200' }}">
        {{ $timer['seconds_remaining'] }}s
    </div>
</div>
