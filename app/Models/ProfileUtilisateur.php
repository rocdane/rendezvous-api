<?php

namespace App\Models;
use App\Enums\StatutProfile;

class ProfileUtilisateur extends Profile
{
    protected $attributes = [
        'type' => 'utilisateur',
    ];

    protected static function booted()
    {
        static::addGlobalScope('type', function ($query) {
            $query->where('type', 'utilisateur');
        });
    }
    
    public function getTypeProfile(): string
    {
        return 'utilisateur';
    }

    public function getValidationRules(): array
    {
        return [
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'email' => 'required|email|unique:profiles,email,' . $this->id,
            'telephone' => 'nullable|string|max:20',
            'statut' => 'required|in:' . implode(',', StatutProfile::values()),
            'date_naissance' => 'nullable|date|before:today',
        ];
    }

    public function getPermissions(): array
    {
        return [
            'reserver_rendezvous',
            'consulter_historique',
            'modifier_profil',
        ];
    }

    /*
    public function rendezVous()
    {
        return $this->hasMany(RendezVous::class, 'profile_utilisateur_id');
    }

    public function getRendezVousAVenir()
    {
        return $this->rendezVous()
                    ->where('date_heure', '>', now())
                    ->orderBy('date_heure')
                    ->get();
    }*/
}
