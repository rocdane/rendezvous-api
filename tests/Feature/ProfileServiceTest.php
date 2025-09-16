<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ProfileServiceTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_profile_endpoint(): void
    {
        $response = $this->get('/api/user/profiles');

        $response->assertStatus(200);
    }

    public function test_profile_creation()
    {
        $response = $this->postJson('/api/user/profiles', [
            'nom' => 'Sabi',
            'prenom' => 'Rochdane',
            'email' => 'rochdane.sabi@rdv.com',
            'telephone' => '1234567890',
            'date_naissance' => '1990-01-01',
        ]);

        $response->assertStatus(201);
    }

    public function test_profile_update()
    {
        // Créer un profil pour le test
        $response = $this->postJson('/api/user/profiles', [
            'nom' => 'Sabi',
            'prenom' => 'Rochdane',
            'email' => 'rochdane.sabi@rdv.com',
            'telephone' => '1234567890',
            'date_naissance' => '1990-01-01',
        ]);

        $profileId = $response->json('profile.id');

        // Mettre à jour le profil
        $updateResponse = $this->putJson("/api/user/profiles/{$profileId}", [
            'telephone' => '2250704054843',
            'date_naissance' => '1994-03-10',
        ]);
    
        $updateResponse->assertStatus(200);
    }
}
