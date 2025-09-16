<?php
namespace App\Services;

use App\Models\Profile;
use App\Enums\StatutProfile;
use Illuminate\Support\Facades\DB;

class ProfileService
{
    /**
     * Créer un nouveau profil
     */
    public function creerProfil(string $type, array $data): Profile
    {
        return DB::transaction(function () use ($type, $data) {
            $profile = ProfileFactory::create($type, $data);
            
            // Actions post-création selon le type
            $this->onProfileCreated($profile);
            
            return $profile;
        });
    }

    /**
     * Mettre à jour un profil
     */
    public function mettreAJourProfil(Profile $profile, array $data): Profile
    {
        return DB::transaction(function () use ($profile, $data) {
            $profile->update($data);
            
            // Actions post-mise à jour
            $this->onProfileUpdated($profile);
            
            return $profile->refresh();
        });
    }

    /**
     * Changer le statut d'un profil
     */
    public function changerStatut(Profile $profile, StatutProfile $nouveauStatut, string $raison = null): bool
    {
        $ancienStatut = $profile->statut;
        
        return DB::transaction(function () use ($profile, $nouveauStatut, $ancienStatut, $raison) {
            $profile->statut = $nouveauStatut;
            $success = $profile->save();
            
            if ($success) {
                // Logger le changement de statut
                $this->loggerChangementStatut($profile, $ancienStatut, $nouveauStatut, $raison);
                
                // Actions spécifiques selon le nouveau statut
                $this->onStatutChanged($profile, $nouveauStatut);
            }
            
            return $success;
        });
    }

    /**
     * Rechercher des profils
     */
    public function rechercherProfils(array $criteres): \Illuminate\Database\Eloquent\Collection
    {
        $query = Profile::query();
        
        // Filtres
        if (!empty($criteres['type'])) {
            $query->where('type', $criteres['type']);
        }
        
        if (!empty($criteres['statut'])) {
            $query->where('statut', $criteres['statut']);
        }
        
        if (!empty($criteres['search'])) {
            $query->search($criteres['search']);
        }
        
        if (!empty($criteres['ville'])) {
            $query->where('ville', 'like', "%{$criteres['ville']}%");
        }
        
        // Tri
        $sortBy = $criteres['sort_by'] ?? 'nom';
        $sortOrder = $criteres['sort_order'] ?? 'asc';
        $query->orderBy($sortBy, $sortOrder);
        
        return $query->get();
    }

    /**
     * Obtenir les statistiques des profils
     */
    public function getStatistiques(): array
    {
        return [
            'total' => Profile::count(),
            'par_type' => Profile::select('type', DB::raw('count(*) as count'))
                               ->groupBy('type')
                               ->pluck('count', 'type')
                               ->toArray(),
            'par_statut' => Profile::select('statut', DB::raw('count(*) as count'))
                                 ->groupBy('statut')
                                 ->get()
                                 ->mapWithKeys(fn($item) => [$item->statut->value => $item->count])
                                 ->toArray(),
            'actifs' => Profile::actifs()->count(),
            'inactifs' => Profile::inactifs()->count(),
            'nouveaux_ce_mois' => Profile::whereMonth('created_at', now()->month)
                                       ->whereYear('created_at', now()->year)
                                       ->count(),
        ];
    }

    /**
     * Actions après création d'un profil
     */
    private function onProfileCreated(Profile $profile): void
    {
        // Envoyer email de bienvenue, créer dossiers, etc.
        match ($profile->getTypeProfile()) {
            'utilisateur' => $this->onUtilisateurCreated($profile),
            'administrateur' => $this->onAdministrateurCreated($profile),
        };
    }

    /**
     * Actions après mise à jour d'un profil
     */
    private function onProfileUpdated(Profile $profile): void
    {
        // Synchroniser avec services externes, etc.
    }

    /**
     * Actions lors d'un changement de statut
     */
    private function onStatutChanged(Profile $profile, StatutProfile $nouveauStatut): void
    {
        match ($nouveauStatut) {
            StatutProfile::ACTIF => $this->onProfileActivated($profile),
            StatutProfile::SUSPENDU => $this->onProfileSuspended($profile),
            StatutProfile::ARCHIVE => $this->onProfileArchived($profile),
            default => null,
        };
    }

    /**
     * Logger les changements de statut
     */
    private function loggerChangementStatut(Profile $profile, StatutProfile $ancien, StatutProfile $nouveau, ?string $raison): void
    {
        // Log dans la base de données ou fichiers
        \Log::info("Changement de statut du profil {$profile->id}: {$ancien->value} -> {$nouveau->value}", [
            'profile_id' => $profile->id,
            'ancien_statut' => $ancien->value,
            'nouveau_statut' => $nouveau->value,
            'raison' => $raison,
            'user_id' => auth()->id(),
        ]);
    }

    // Méthodes spécifiques par type de profil
    private function onUtilisateurCreated(Profile $client): void
    {
        // Logique spécifique aux utilisateurs
    }

    private function onAdministrateurCreated(Profile $admin): void
    {
        // Notifier les super admins, etc.
    }

    private function onProfileActivated(Profile $profile): void
    {
        // Envoyer notification d'activation
    }

    private function onProfileSuspended(Profile $profile): void
    {
        // Annuler les rendez-vous futurs, notifier, etc.
    }

    private function onProfileArchived(Profile $profile): void
    {
        // Archiver les données associées
    }
}