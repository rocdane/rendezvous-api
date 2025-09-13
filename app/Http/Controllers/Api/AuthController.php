<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\OAuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(private OAuthService $oauthService)
    {
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