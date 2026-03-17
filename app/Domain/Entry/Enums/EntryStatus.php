<?php

namespace App\Domain\Entry\Enums;

enum EntryStatus: string
{
    case Pending = 'pending';
    case Win = 'win';
    case Lose = 'lose';
    case Refunded = 'refunded';
}
