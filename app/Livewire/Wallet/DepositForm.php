<?php

namespace App\Livewire\Wallet;

use App\Services\WalletService;
use Livewire\Component;

class DepositForm extends Component
{
    public string $amount = '';

    public function submit(): void
    {
        $payload = $this->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
        ]);

        app(WalletService::class)->deposit((int) auth()->id(), $payload['amount']);

        $this->reset('amount');
        $this->dispatch('wallet-updated');
        session()->flash('success', 'Deposit completed successfully.');
    }

    public function render()
    {
        return view('livewire.wallet.deposit-form');
    }
}
