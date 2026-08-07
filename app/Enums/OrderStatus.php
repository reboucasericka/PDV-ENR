<?php

namespace App\Enums;

enum OrderStatus: string
{
    case OPEN = 'open';
    case PAID = 'paid';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::OPEN => 'Aberto',
            self::PAID => 'Pago',
            self::CANCELLED => 'Cancelado',
        };
    }
}
