<?php

namespace App\Repositories;

use App\Domain\Entry\Enums\EntryStatus;
use App\Models\Entry;
use Illuminate\Database\Eloquent\Collection;

class EntryRepository
{
    public function createEntry(array $attributes): Entry
    {
        return Entry::query()->create($attributes);
    }

    public function getGameEntries(int $gameId): Collection
    {
        return Entry::query()
            ->with('user')
            ->where('game_id', $gameId)
            ->orderBy('id')
            ->get();
    }

    public function getUserEntries(int $userId): Collection
    {
        return Entry::query()
            ->with('game')
            ->where('user_id', $userId)
            ->latest()
            ->get();
    }

    public function updateStatus(Entry $entry, EntryStatus $status): Entry
    {
        $entry->forceFill(['status' => $status])->save();

        return $entry->refresh();
    }
}
