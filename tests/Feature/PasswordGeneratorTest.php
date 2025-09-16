<?php

namespace Tests\Feature;

use Tests\TestCase;

class PasswordGeneratorTest extends TestCase
{
    /**
     * Test de l'endpoint de génération de mot de passe
     */
    public function test_password_generation_endpoint(): void
    {
        $response = $this->postJson('/api/user/password/generate', [
            'length' => 16,
            'type' => 'secure',
            'include_uppercase' => true,
            'include_lowercase' => true,
            'include_numbers' => true,
            'include_symbols' => true,
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'password',
                'length',
                'strength',
            ]);
    }

    /**
     * Test de l'endpoint de vérification de mot de passe
     */
    public function test_password_check_endpoint(): void
    {
        $generated = $this->postJson('/api/user/password/generate', [
            'length' => 16,
            'type' => 'secure',
            'include_uppercase' => true,
            'include_lowercase' => true,
            'include_numbers' => true,
            'include_symbols' => true,
        ]);

        $password = $generated->json('password');

        $response = $this->postJson('/api/user/password/check', [
            'password' => $password,
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'strength',
            ]);
    }
}
