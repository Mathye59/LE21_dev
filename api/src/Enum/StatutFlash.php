<?php

namespace App\Enum;

enum StatutFlash: string
{
    case DISPONIBLE   = 'disponible';
    case RESERVE      = 'reserve';
    case INDISPONIBLE = 'indisponible';

    public static function choices(): array
    {
        return [
            'Disponible'   => self::DISPONIBLE,
            'Réservé'      => self::RESERVE,
            'Indisponible' => self::INDISPONIBLE,
        ];
    }
}