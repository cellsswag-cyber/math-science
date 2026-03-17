<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\WalletRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AuthService
{
    public function __construct(
        private readonly WalletRepository $wallets,
    ) {}

    public function register(array $payload): User
    {
        $user = DB::transaction(function () use ($payload): User {
            $user = User::query()->create([
                'name' => $payload['name'],
                'email' => $payload['email'],
                'password' => $payload['password'],
            ]);

            $this->wallets->getUserWallet($user->id);

            return $user;
        });

        Auth::login($user);
        request()->session()->regenerate();

        return $user;
    }

    public function login(array $payload, bool $remember = false): void
    {
        if (! Auth::attempt([
            'email' => $payload['email'],
            'password' => $payload['password'],
        ], $remember)) {
            throw ValidationException::withMessages([
                'email' => 'The provided credentials do not match our records.',
            ]);
        }

        request()->session()->regenerate();

        /** @var User $user */
        $user = Auth::user();

        if ($user->is_suspended) {
            $this->logout();

            throw ValidationException::withMessages([
                'email' => 'This account has been suspended.',
            ]);
        }
    }

    public function logout(): void
    {
        Auth::guard('web')->logout();

        request()->session()->invalidate();
        request()->session()->regenerateToken();
    }
}
