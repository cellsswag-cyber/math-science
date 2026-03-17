<?php

namespace App\Livewire\User;

use App\Services\ProfileService;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Livewire\Component;

class ProfileSettings extends Component
{
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';

    public function mount(): void
    {
        $profile = app(ProfileService::class)->getProfileData((int) auth()->id());

        $this->name = $profile['name'];
        $this->email = $profile['email'];
    }

    public function save(): void
    {
        $payload = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore((int) auth()->id())],
            'password' => ['nullable', 'confirmed', Password::defaults()],
        ]);

        app(ProfileService::class)->updateProfile((int) auth()->id(), $payload);

        $this->reset('password', 'password_confirmation');
        session()->flash('success', 'Profile updated successfully.');
    }

    public function render()
    {
        return view('livewire.user.profile-settings')
            ->layout('layouts.app', [
                'title' => 'Profile',
            ]);
    }
}
