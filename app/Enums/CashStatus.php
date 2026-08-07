<?php

namespace App\Enums;

enum CashStatus: string
{
    case OPEN = 'OPEN';
    case CLOSED = 'CLOSED';

    public function label(): string
    {
        return match ($this) {
            self::OPEN => 'Aberto',
            self::CLOSED => 'Fechado',
        };
    }
}
