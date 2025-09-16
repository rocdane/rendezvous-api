<?php

namespace App\Enums;

enum StatutProfile: string
{
    case ACTIF = 'actif';
    case INACTIF = 'inactif';
    case SUSPENDU = 'suspendu';
    case EN_ATTENTE = 'en_attente';
    case ARCHIVE = 'archive';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function options(): array
    {
        return [
            self::ACTIF->value => 'Actif',
            self::INACTIF->value => 'Inactif',
            self::SUSPENDU->value => 'Suspendu',
            self::EN_ATTENTE->value => 'En attente',
            self::ARCHIVE->value => 'Archivé',
        ];
    }

    public function label(): string
    {
        return match($this) {
            self::ACTIF => 'Actif',
            self::INACTIF => 'Inactif',
            self::SUSPENDU => 'Suspendu',
            self::EN_ATTENTE => 'En attente',
            self::ARCHIVE => 'Archivé',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::ACTIF => 'green',
            self::INACTIF => 'gray',
            self::SUSPENDU => 'red',
            self::EN_ATTENTE => 'yellow',
            self::ARCHIVE => 'purple',
        };
    }

    public function isActive(): bool
    {
        return $this === self::ACTIF;
    }

    public function canLogin(): bool
    {
        return in_array($this, [self::ACTIF]);
    }

    public function canEdit(): bool
    {
        return in_array($this, [self::ACTIF, self::INACTIF]);
    }

    public static function activeStatuses(): array
    {
        return [self::ACTIF];
    }

    public static function inactiveStatuses(): array
    {
        return [self::INACTIF, self::SUSPENDU, self::ARCHIVE];
    }

    public static function editableStatuses(): array
    {
        return [self::ACTIF, self::INACTIF, self::EN_ATTENTE];
    }
}