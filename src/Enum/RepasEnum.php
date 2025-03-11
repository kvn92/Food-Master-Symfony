<?php
namespace App\Enum;

enum RepasEnum: int
{
    case PETIT_DEJEUNER = 1;
    case DEJEUNER = 2;
    case DINER = 3;

    public function label(): string
    {
        return match ($this) {
            self::PETIT_DEJEUNER => 'Petit Déjeuner',
            self::DEJEUNER => 'Déjeuner',
            self::DINER => 'Dîner',
        };
    }
}

