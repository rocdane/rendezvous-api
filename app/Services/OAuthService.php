<?php

namespace App\Services;

use App\Models\User;
use App\Models\OAuthProvider;
use Illuminate\Support\Facades\DB;
use Laravel\Socialite\Facades\Socialite;

class OAuthService
{
    public function getRedirectUrl(string $provider): string
    {
        return Socialite::driver($provider)->redirect()->getTargetUrl();
    }

    public function handleCallback(string $provider)
    {
        try {
            $socialiteUser = Socialite::driver($provider)->user();
            
            return DB::transaction(function () use ($provider, $socialiteUser) {
                // Chercher un provider OAuth existant
                $oauthProvider = OAuthProvider::where('provider', $provider)
                    ->where('provider_id', $socialiteUser->getId())
                    ->first();

                if ($oauthProvider) {
                    // Utilisateur existant
                    $user = $oauthProvider->user;
                    $this->updateUserLoginInfo($user, $oauthProvider, $socialiteUser);
                    return $user;
                }

                // Chercher un utilisateur par email
                $user = User::where('email', $socialiteUser->getEmail())->first();

                if ($user) {
                    // Lier le nouveau provider à l'utilisateur existant
                    $this->createOAuthProvider($user, $provider, $socialiteUser);
                } else {
                    // Créer un nouvel utilisateur
                    $user = $this->createUser($socialiteUser);
                    $this->createOAuthProvider($user, $provider, $socialiteUser);
                }

                return $user;
            });
        } catch (\Exception $e) {
            throw new \Exception('Erreur lors de l\'authentification: ' . $e->getMessage());
        }
    }

    private function createUser($socialiteUser): User
    {
        return User::create([
            'name' => $socialiteUser->getName(),
            'email' => $socialiteUser->getEmail(),
            'avatar' => $socialiteUser->getAvatar(),
            'email_verified_at' => now(),
            'last_login_at' => now(),
        ]);
    }

    private function createOAuthProvider(User $user, string $provider, $socialiteUser): OAuthProvider
    {
        return OAuthProvider::create([
            'user_id' => $user->id,
            'provider' => $provider,
            'provider_id' => $socialiteUser->getId(),
            'access_token' => $socialiteUser->token,
            'refresh_token' => $socialiteUser->refreshToken,
            'provider_data' => [
                'name' => $socialiteUser->getName(),
                'email' => $socialiteUser->getEmail(),
                'avatar' => $socialiteUser->getAvatar(),
                'raw' => $socialiteUser->getRaw(),
            ],
        ]);
    }

    private function updateUserLoginInfo(User $user, OAuthProvider $oauthProvider, $socialiteUser): void
    {
        $user->update([
            'last_login_at' => now(),
            'avatar' => $socialiteUser->getAvatar() ?: $user->avatar,
        ]);

        $oauthProvider->update([
            'access_token' => $socialiteUser->token,
            'refresh_token' => $socialiteUser->refreshToken,
            'provider_data' => [
                'name' => $socialiteUser->getName(),
                'email' => $socialiteUser->getEmail(),
                'avatar' => $socialiteUser->getAvatar(),
                'raw' => $socialiteUser->getRaw(),
            ],
        ]);
    }
}