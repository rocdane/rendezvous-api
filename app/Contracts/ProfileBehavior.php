<?php

namespace App\Contracts;

use Illuminate\Database\Eloquent\Builder;
use App\Enums\StatutProfile;

trait ProfileBehavior
{
    public function getNomCompletAttribute(): string
    {
        return trim($this->prenom . ' ' . $this->nom);
    }

    public function getInitialesAttribute(): string
    {
        $prenom = substr($this->prenom ?? '', 0, 1);
        $nom = substr($this->nom ?? '', 0, 1);
        return strtoupper($prenom . $nom);
    }

    public function getAgeAttribute(): ?int
    {
        return $this->date_naissance?->age;
    }

    public function getAdresseCompleteAttribute(): string
    {
        $parts = array_filter([
            $this->adresse,
            $this->code_postal,
            $this->ville,
            $this->pays
        ]);
        
        return implode(', ', $parts);
    }

    public function setEmailAttribute(?string $value): void
    {
        $this->attributes['email'] = $value ? strtolower($value) : null;
    }

    public function setTelephoneAttribute(?string $value): void
    {
        if ($value) {
            // Supprimer tous les caractères non numériques sauf le +
            $cleaned = preg_replace('/[^\d+]/', '', $value);
            $this->attributes['telephone'] = $cleaned;
        } else {
            $this->attributes['telephone'] = null;
        }
    }

    public function scopeActifs(Builder $query): Builder
    {
        return $query->where('statut', StatutProfile::ACTIF);
    }

    public function scopeInactifs(Builder $query): Builder
    {
        return $query->whereIn('statut', StatutProfile::inactiveStatuses());
    }

    public function scopeEditables(Builder $query): Builder
    {
        return $query->whereIn('statut', StatutProfile::editableStatuses());
    }

    public function scopeSearch(Builder $query, string $search): Builder
    {
        return $query->where(function ($q) use ($search) {
            $q->where('nom', 'like', "%{$search}%")
              ->orWhere('prenom', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%");
        });
    }

    public function activer(): bool
    {
        $this->statut = StatutProfile::ACTIF;
        return $this->save();
    }

    public function desactiver(): bool
    {
        $this->statut = StatutProfile::INACTIF;
        return $this->save();
    }

    public function suspendre(): bool
    {
        $this->statut = StatutProfile::SUSPENDU;
        return $this->save();
    }

    public function archiver(): bool
    {
        $this->statut = StatutProfile::ARCHIVE;
        return $this->save();
    }

    public function estActif(): bool
    {
        return $this->statut === StatutProfile::ACTIF;
    }

    public function peutSeConnecter(): bool
    {
        return $this->statut->canLogin();
    }

    public function peutEtreEdite(): bool
    {
        return $this->statut->canEdit();
    }

    public function toBasicArray(): array
    {
        return [
            'id' => $this->id,
            'nom_complet' => $this->nom_complet,
            'email' => $this->email,
            'statut' => [
                'value' => $this->statut->value,
                'label' => $this->statut->label(),
                'color' => $this->statut->color(),
            ],
            'photo' => $this->photo,
            'initiales' => $this->initiales,
        ];
    }
}
