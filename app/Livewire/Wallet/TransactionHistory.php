<?php

namespace App\Livewire\Wallet;

use App\Services\WalletQueryService;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class TransactionHistory extends Component
{
    use WithPagination;

    #[On('wallet-updated')]
    public function refreshHistory(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        return view('livewire.wallet.transaction-history', [
            'transactions' => app(WalletQueryService::class)->getTransactionHistory((int) auth()->id()),
        ]);
    }
}
