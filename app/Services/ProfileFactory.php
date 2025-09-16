<?php

namespace App\Services;

use App\Models\Profile;
use App\Models\ProfileUtilisateur;
use App\Models\ProfileAdministrateur;
use InvalidArgumentException;
use App\Enums\StatutProfile;

class ProfileFactory extends Factory
{
    public static function create(string $type, array $data): Profile
    {
        return match ($type) {
            'utilisateur' => ProfileUtilisateur::create(array_merge($data, ['type' => 'utilisateur'])),
            'administrateur' => ProfileAdministrateur::create(array_merge($data, ['type' => 'administrateur'])),
            default => throw new InvalidArgumentException("Type de profil inconnu : {$type}")
        };
    }

    public static function getModelClass(string $type): string
    {
        return match ($type) {
            'utilisateur' => ProfileUtilisateur::class,
            'administrateur' => ProfileAdministrateur::class,
            default => throw new InvalidArgumentException("Type de profil inconnu : {$type}")
        };
    }

    public static function getAvailableTypes(): array
    {
        return ['utilisateur', 'administrateur'];
    }
}
