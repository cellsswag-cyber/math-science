<?php

namespace App\Domain\Wallet\Enums;

enum DepositStatus: string
{
    case Pending = 'pending';
    case Confirmed = 'confirmed';
    case Failed = 'failed';
    case PartiallyPaid = 'partially_paid';
    case Expired = 'expired';
}
