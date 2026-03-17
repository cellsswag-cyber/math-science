<?php

namespace App\Domain\Wallet\Enums;

enum WithdrawStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';
}
