<?php

namespace App\Domain\Game\Enums;

enum GameStatus: string
{
    case Pending = 'pending';
    case Open = 'open';
    case Closed = 'closed';
    case ResultDeclared = 'result_declared';

    /**
     * @return list<string>
     */
    public static function activeValues(): array
    {
        return [
            self::Pending->value,
            self::Open->value,
            self::Closed->value,
        ];
    }
}
