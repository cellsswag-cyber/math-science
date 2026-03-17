<?php

namespace App\Livewire\Auth;

use App\Services\AuthService;
use Livewire\Component;

class LoginPage extends Component
{
    public string $email = '';
    public string $password = '';
    public bool $remember = false;

    public function mount(): void
    {
        if (auth()->check()) {
            $this->redirectRoute('dashboard', navigate: true);
        }
    }

    public function login(): void
    {
        $payload = $this->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'remember' => ['boolean'],
        ]);

        app(AuthService::class)->login($payload, $this->remember);

        session()->flash('success', 'Welcome back.');

        $this->redirectRoute('dashboard', navigate: true);
    }

    public function render()
    {
        return view('livewire.auth.login-page')
            ->layout('layouts.app', [
                'title' => 'Login',
            ]);
    }
}
