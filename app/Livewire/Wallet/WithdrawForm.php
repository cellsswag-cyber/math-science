<?php

namespace App\Livewire\Wallet;

use App\Services\WalletService;
use Livewire\Component;

class WithdrawForm extends Component
{
    public string $amount = '';

    public function submit(): void
    {
        $payload = $this->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
        ]);

        app(WalletService::class)->withdraw((int) auth()->id(), $payload['amount']);

        $this->reset('amount');
        $this->dispatch('wallet-updated');
        session()->flash('success', 'Withdrawal request submitted and marked pending.');
    }

    public function render()
    {
        return view('livewire.wallet.withdraw-form');
    }
}
