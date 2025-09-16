<?php

namespace App\Services;

use App\Models\Profile;
use App\Models\ProfileAdministrateur;
use App\Models\ProfileUtilisateur;
use InvalidArgumentException;

class ProfileFactory
{
    /**
     * Crée un nouveau profil selon le type
     */
    public static function create(string $type, array $data): Profile
    {
        return match ($type) {
            'utilisateur' => Profile::create(array_merge($data, ['type' => 'utilisateur'])),
            'administrateur' => Profile::create(array_merge($data, ['type' => 'administrateur'])),
            default => throw new InvalidArgumentException("Type de profil inconnu : {$type}")
        };
    }

    /**
     * Trouve un profil par son ID et retourne l'instance du bon type
     */
    public static function find(int $id): ?Profile
    {
        // Récupère le profil avec son type depuis la base
        $profileData = \DB::table('profiles')->where('id', $id)->first();

        if (! $profileData) {
            return null;
        }

        // Retourne l'instance du bon type
        $modelClass = self::getModelClass($profileData->type);

        return $modelClass::find($id);
    }

    /**
     * Trouve tous les profils et retourne les bonnes instances
     */
    public static function findAll(): \Illuminate\Support\Collection
    {
        $profiles = \DB::table('profiles')->get();

        return $profiles->map(function ($profileData) {
            $modelClass = self::getModelClass($profileData->type);

            return $modelClass::find($profileData->id);
        });
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
