<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class ProfileService
{
    public function __construct(
        private readonly PlatformCacheService $cache,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function getProfileData(int $userId): array
    {
        $user = User::query()->findOrFail($userId);

        return [
            'name' => $user->name,
            'email' => $user->email,
            'is_admin' => $user->is_admin,
            'is_suspended' => $user->is_suspended,
        ];
    }

    public function updateProfile(int $userId, array $payload): User
    {
        $user = User::query()->findOrFail($userId);

        DB::transaction(function () use ($user, $payload): void {
            $attributes = [
                'name' => $payload['name'],
                'email' => $payload['email'],
            ];

            if (! empty($payload['password'])) {
                $attributes['password'] = $payload['password'];
            }

            $user->fill($attributes)->save();
        });

        $this->cache->forgetUserScopedCaches($user->id);

        return $user->refresh();
    }

    public function suspendUser(int $userId): User
    {
        $user = User::query()->findOrFail($userId);

        $user->forceFill([
            'is_suspended' => true,
            'suspended_at' => now(),
        ])->save();

        $this->cache->forgetUserScopedCaches($user->id);

        return $user->refresh();
    }

    public function restoreUser(int $userId): User
    {
        $user = User::query()->findOrFail($userId);

        $user->forceFill([
            'is_suspended' => false,
            'suspended_at' => null,
        ])->save();

        $this->cache->forgetUserScopedCaches($user->id);

        return $user->refresh();
    }
}
