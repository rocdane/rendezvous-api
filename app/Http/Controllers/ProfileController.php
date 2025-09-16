<?php

namespace App\Http\Controllers;

use App\Enums\StatutProfile;
use App\Models\Profile;
use App\Services\ProfileService;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function __construct(
        private ProfileService $profileService
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $criteres = $request->only(['type', 'statut', 'ville', 'sort_by', 'sort_order']);

        $criteresAvecDefauts = array_merge([
            'type' => null,
            'statut' => StatutProfile::ACTIF,
            'sort_by' => 'created_at',
            'sort_order' => 'desc',
        ], array_filter($criteres));

        $profiles = $this->profileService->rechercherProfils($criteresAvecDefauts);

        return response()->json([
            'profiles' => $profiles->map(fn ($p) => $p->toBasicArray()),
            'total' => $profiles->count(),
            'filters' => $criteresAvecDefauts,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'email' => 'required|email|unique:profiles',
            'telephone' => 'nullable|string',
            'date_naissance' => 'nullable|date',
        ]);

        $utilisateur = $this->profileService->creerProfil('utilisateur', $validated);

        return response()->json([
            'message' => 'Profile créé avec succès',
            'profile' => $utilisateur->toBasicArray(),
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Profile $profile)
    {
        $utilisateur = $this->profileService->obtenirProfile($profile);

        return response()->json([
            'message' => 'Profile existe bien',
            'profile' => $utilisateur->toBasicArray(),
        ], 200);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Profile $profile)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Profile $profile)
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'email' => 'required|email|unique:profiles,email,'.$profile->id,
            'telephone' => 'nullable|string',
            'date_naissance' => 'nullable|date',
        ]);

        $utilisateur = $this->profileService->mettreAJourProfil($profile, $validated);

        return response()->json([
            'message' => 'Profile mis à jour avec succès',
            'profile' => $utilisateur->toBasicArray(),
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Profile $profile)
    {
        $utilisateur = $this->profileService->changerStatut($profile, StatutProfile::ARCHIVE, 'Archivé via API');

        return response()->json([
            'message' => 'Profile archivé avec succès',
        ], 200);
    }
}
