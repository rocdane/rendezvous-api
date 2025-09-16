<?php

namespace App\Models;
use App\Enums\StatutProfile;

class ProfileAdministrateur extends Profile
{
    protected $attributes = [
        'type' => 'administrateur',
    ];
    
    protected static function booted()
    {
        static::addGlobalScope('type', function ($query) {
            $query->where('type', 'administrateur');
        });
    }
    
    public function getTypeProfile(): string
    {
        return 'administrateur';
    }

    public function getValidationRules(): array
    {
        return [
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'email' => 'required|email|unique:profiles,email,' . $this->id,
            'telephone' => 'nullable|string|max:20',
            'statut' => 'required|in:' . implode(',', StatutProfile::values()),
            'niveau_acces' => 'required|in:super_administrateur,administrateur,moderateur',
        ];
    }

    public function getPermissions(): array
    {
        $basePermissions = [
            'consulter_tous_profils',
            'modifier_tous_profils',
            'consulter_statistiques',
        ];

        $niveauAcces = $this->metadata['niveau_acces'] ?? 'moderateur';
        
        switch ($niveauAcces) {
            case 'super_administrateur':
                return array_merge($basePermissions, [
                    'supprimer_profils',
                    'gerer_parametres_systeme',
                    'gerer_administrateurs',
                ]);
                
            case 'administrateur':
                return array_merge($basePermissions, [
                    'suspendre_profils',
                    'gerer_utilisateurs',
                ]);
                
            default:
                return $basePermissions;
        }
    }

    public function estSuperAdmin(): bool
    {
        return ($this->metadata['niveau_acces'] ?? '') === 'super_administrateur';
    }
}