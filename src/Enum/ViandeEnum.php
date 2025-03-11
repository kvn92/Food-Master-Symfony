<?php
namespace App\Enum;

enum ViandeEnum: int
{
    case SANS_VIANDE = 0;
    case VEGETALE = 1;
    case PORC = 2;
    case POULET = 3;
    case BOEUF = 4;
    Case POISSON = 5;
    

    public function label(): string {
        return match($this) {
            self::SANS_VIANDE => 'Sans-Viande',
            self::VEGETALE => 'Végétale',
            self::PORC =>'Porc',
            self::POULET=>'Poulet',
            self::BOEUF=>'Boeuf',
            self::POISSON =>'Poisson'
        };
        }
}
