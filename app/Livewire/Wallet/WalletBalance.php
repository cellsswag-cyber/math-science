<?php

namespace App\Livewire\Wallet;

use App\Services\WalletQueryService;
use Livewire\Component;

class WalletBalance extends Component
{
    protected $listeners = ['wallet-updated' => '$refresh'];

    public function render()
    {
        $service = app(WalletQueryService::class);

        return view('livewire.wallet.wallet-balance', [
            'wallet' => $service->getWalletSnapshot((int) auth()->id()),
            'recentTransactions' => $service->getRecentTransactions((int) auth()->id(), 5),
        ])->layout('layouts.app', [
            'title' => 'Wallet',
        ]);
    }
}
