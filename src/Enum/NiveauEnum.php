<?php
namespace App\Enum;


enum NiveauEnum: int {
    case FACILE = 1;
    case NORMAL = 2;
    case  DIFFICILE = 3;

    public function label(): string {
        return match($this) {
            self::FACILE => 'Facile',
            self::NORMAL =>'Normal',
            self::DIFFICILE=>'Difficile',
        };
        }
    }
    

