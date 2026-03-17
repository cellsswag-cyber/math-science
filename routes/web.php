<?php

use App\Livewire\Auth\LoginPage;
use App\Livewire\Auth\RegisterPage;
use App\Livewire\Dashboard\UserDashboard;
use App\Livewire\Games\GameList;
use App\Livewire\Games\GamePlay;
use App\Livewire\Results\ResultBoard;
use App\Livewire\User\ProfileSettings;
use App\Livewire\User\UserHistory;
use App\Livewire\Wallet\WalletBalance;
use App\Services\AuthService;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
})->name('home');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', LoginPage::class)->name('login');
    Route::get('/register', RegisterPage::class)->name('register');
});

Route::post('/logout', function (AuthService $authService) {
    $authService->logout();

    return redirect()->route('login');
})->middleware('auth')->name('logout');

Route::middleware(['auth', 'active'])->group(function (): void {
    Route::get('/dashboard', UserDashboard::class)->name('dashboard');
    Route::get('/games', GameList::class)->name('games.index');
    Route::get('/games/{game}', GamePlay::class)->name('games.play');
    Route::get('/wallet', WalletBalance::class)->name('wallet.index');
    Route::get('/results', ResultBoard::class)->name('results.index');
    Route::get('/history', UserHistory::class)->name('history.index');
    Route::get('/profile', ProfileSettings::class)->name('profile.index');
});

Route::view('/ops/foundation', 'platform-scaffold');
