<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Enums\StatutProfile;
use App\Models\Profile;
use App\Models\ProfileUtilisateur;
use App\Models\ProfileAdministrateur;
use App\Services\ProfileService;
use App\Services\ProfileFactory;

class ProfileController extends Controller
{
    public function __construct(
        private ProfileService $profileService
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $profiles = Profile::all();
        return response()->json($profiles->map(fn($p) => $p->toBasicArray()));
    }
    
    /*public function index(Request $request)
    {
        $criteres = $request->only(['type', 'statut', 'search', 'ville', 'sort_by', 'sort_order']);
        
        $profiles = $this->profileService->rechercherProfils($criteres);
        
        return response()->json([
            'profiles' => $profiles->map(fn($p) => $p->toBasicArray()),
            'total' => $profiles->count()
        ]);
    }*/

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
            'profile' => $utilisateur->toBasicArray()
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Profile $profile)
    {
        //
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
            'email' => 'required|email|unique:profiles,email,' . $profile->id,
            'telephone' => 'nullable|string',
            'date_naissance' => 'nullable|date',
        ]);

        $utilisateur = $this->profileService->updateProfile($profile, $validated);
        
        return response()->json([
            'message' => 'Profile mis à jour avec succès',
            'profile' => $utilisateur->toBasicArray()
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Profile $profile)
    {
        //
    }
}
