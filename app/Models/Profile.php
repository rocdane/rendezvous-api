<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use App\Enums\StatutProfile;
use App\Contracts\ProfileInterface;
use App\Contracts\ProfileBehavior;

class Profile extends Model implements ProfileInterface
{
    use HasFactory, SoftDeletes, ProfileBehavior;

    protected $table = 'profiles';

    protected $fillable = [
        'user_id',
        'type',
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
        'photo',
        'statut',
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

    public function getTypeProfile(): string
    {
        return $this->attributes['type'] ?? 'unknown';
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
            'modifier_profil',
        ];
    }
}

