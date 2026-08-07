<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case CASH = 'cash';
    case CARD = 'card';
    case MBWAY = 'mbway';
    case MULTIBANCO = 'multibanco';

    public function label(): string
    {
        return match ($this) {
            self::CASH => 'Dinheiro',
            self::CARD => 'Cartão',
            self::MBWAY => 'MBWay',
            self::MULTIBANCO => 'Multibanco',
        };
    }

    public function requiresChange(): bool
    {
        return $this === self::CASH;
    }

    public function usesKeypad(): bool
    {
        return $this === self::CASH;
    }

    /**
     * @return list<self>
     */
    public static function posMethods(): array
    {
        return [
            self::CASH,
            self::CARD,
            self::MBWAY,
            self::MULTIBANCO,
        ];
    }
}
