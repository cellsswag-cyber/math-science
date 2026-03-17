<?php

namespace App\Livewire\Auth;

use App\Services\AuthService;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\Rule;
use Livewire\Component;

class RegisterPage extends Component
{
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';

    public function mount(): void
    {
        if (auth()->check()) {
            $this->redirectRoute('dashboard', navigate: true);
        }
    }

    public function register(): void
    {
        $payload = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        app(AuthService::class)->register($payload);

        session()->flash('success', 'Your account is ready.');

        $this->redirectRoute('dashboard', navigate: true);
    }

    public function render()
    {
        return view('livewire.auth.register-page')
            ->layout('layouts.app', [
                'title' => 'Register',
            ]);
    }
}
