<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use App\Enums\StatutProfile;

abstract class Profile extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'profiles';

    protected $fillable = [
        'user_id',
        'nom',
        'prenom',
        'email',
        'telephone',
        'date_naissance',
        'rue',
        'adresse',
        'ville',
        'code_postal',
        'pays',
        'statut',
        'photo',
        'metadata',
        'preference_id'
    ];

    protected $attributes = [
        'statut' => StatutProfile::EN_ATTENTE,
    ];
    
    protected function casts(): array
    {
        return [
            'date_naissance' => 'date',
            'statut' => StatutProfile::class,
            'metadata' => 'array',
            'derniere_connexion' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    public function newFromBuilder($attributes = [], $connection = null)
    {
        $attributes = (array) $attributes;
        
        if (!empty($attributes['type'])) {
            switch ($attributes['type']) {
                case 'administrateur':
                    $model = new ProfileAdministrateur();
                    break;
                case 'utilisateur':
                    $model = new ProfileUtilisateur();
                    break;
                default:
                    $model = new static();
            }
            
            $model->exists = true;
            $model->setRawAttributes($attributes, true);
            $model->setConnection($connection ?: $this->getConnectionName());
            
            return $model;
        }
        
        return parent::newFromBuilder($attributes, $connection);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function preference()
    {
        return $this->belongsTo(Preference::class);
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

    // ===== ACCESSORS & MUTATORS =====

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

    // ===== MÉTHODES MÉTIER =====
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

    // ===== MÉTHODES ABSTRAITES =====
    abstract public function getTypeProfile(): string;
    abstract public function getValidationRules(): array;
    abstract public function getPermissions(): array;
}

