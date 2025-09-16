<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\OAuthService;
use App\Services\PasswordGenerator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function __construct(private OAuthService $oauthService)
    {
    }

    public function register(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'timezone' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Erreur de validation',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'timezone' => $request->timezone ?? 'Europe/Paris',
            'email_verified_at' => now(),
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Compte créé avec succès',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'avatar' => $user->avatar,
            ],
            'token' => $token,
        ], 201);
    }

    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Erreur de validation',
                'errors' => $validator->errors()
            ], 422);
        }

        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'message' => 'Identifiants incorrects'
            ], 401);
        }

        $user = Auth::user();
        $user->update(['last_login_at' => now()]);
        
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Connexion réussie',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'avatar' => $user->avatar,
            ],
            'token' => $token,
        ]);
    }

    public function resetPassword(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Erreur de validation',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['message' => 'Utilisateur non trouvé'], 404);
        }

        $newPassword = PasswordGenerator::generateSecure(8);
        $user->password = Hash::make($newPassword);
        $user->save();

        $token = $user->createToken('auth_token')->plainTextToken;

        //todo: envoyer email de réinitialisation event : Password.Reset

        return response()->json([
            'message' => 'Mot de passe réinitialisé avec succès',
            'user' => [
                'name' => $user->name,
                'email' => $user->email,
            ],
            'secret' => $newPassword,
            'token' => $token,
        ], 201);
    }

    public function redirectToProvider(string $provider): JsonResponse
    {
        $allowedProviders = ['google', 'microsoft', 'github'];
        
        if (!in_array($provider, $allowedProviders)) {
            return response()->json(['error' => 'Provider non supporté'], 400);
        }

        try {
            $redirectUrl = $this->oauthService->getRedirectUrl($provider);
            return response()->json(['redirect_url' => $redirectUrl]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function handleCallback(string $provider, Request $request): JsonResponse
    {
        $allowedProviders = ['google', 'microsoft', 'github'];
        
        if (!in_array($provider, $allowedProviders)) {
            return response()->json(['error' => 'Provider non supporté'], 400);
        }

        try {
            $user = $this->oauthService->handleCallback($provider);
            
            // Créer un token Sanctum
            $token = $user->createToken('auth_token')->plainTextToken;
            
            // Rediriger vers le frontend avec le token
            $frontendUrl = config('app.frontend_url', 'http://localhost:3000');
            $redirectUrl = $frontendUrl . '/auth/callback?token=' . urlencode($token);
            
            return redirect($redirectUrl);
        } catch (\Exception $e) {
            $frontendUrl = config('app.frontend_url', 'http://localhost:3000');
            $redirectUrl = $frontendUrl . '/auth/callback?error=' . urlencode($e->getMessage());
            return redirect($redirectUrl);
        }
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->load('oauthProviders:id,user_id,provider');
        
        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'avatar' => $user->avatar,
                'providers' => $user->oauthProviders->pluck('provider'),
                'last_login_at' => $user->last_login_at,
            ]
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();
        
        return response()->json(['message' => 'Déconnecté avec succès']);
    }
}