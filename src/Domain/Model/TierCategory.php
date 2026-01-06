<?php

namespace App\Domain\Model;

enum TierCategory: string
{
    case S = 'S';
    case A = 'A';
    case B = 'B';
    case C = 'C';
    case D = 'D';

    public function getLabel(): string
    {
        return match ($this) {
            self::S => 'Les chefs-d\'oeuvre du branding',
            self::A => 'Excellents logos',
            self::B => 'Bons logos',
            self::C => 'Logos moyens',
            self::D => 'Logos à améliorer',
        };
    }
}

