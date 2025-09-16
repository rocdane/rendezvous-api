<?php

namespace App\Enums;

enum NiveauAcces: int
{
    case SUPERADMINISTRATEUR = 1;
    case ADMINISTRATEUR = 2;
    case MODERATEUR = 3;

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function options(): array
    {
        return [
            self::SUPERADMINISTRATEUR => 1,
            self::ADMINISTRATEUR => 2,
            self::MODERATEUR => 3,
        ];
    }

    public function isSuperAdministrateur(): bool
    {
        return $this === self::SUPERADMINISTRATEUR;
    }

    public function isAdministrateur(): bool
    {
        return $this === self::ADMINISTRATEUR;
    }

    public function isModerateur(): bool
    {
        return $this === self::MODERATEUR;
    }
}
