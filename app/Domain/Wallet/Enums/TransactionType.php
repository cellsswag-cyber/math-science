<?php

namespace App\Domain\Wallet\Enums;

enum TransactionType: string
{
    case Deposit = 'deposit';
    case Withdraw = 'withdraw';
    case EntryLocked = 'entry_locked';
    case EntryRefund = 'entry_refund';
    case Winnings = 'winnings';
}
