<?php

namespace App\Http\Controllers;

use App\Services\PasswordGenerator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PasswordController extends Controller
{
    /**
     * Générer un mot de passe via API
     */
    public function generate(Request $request): JsonResponse
    {
        $request->validate([
            'length' => 'integer|min:4|max:128',
            'type' => 'string|in:simple,secure,readable,word',
            'pattern' => 'string|max:50',
            'include_uppercase' => 'boolean',
            'include_lowercase' => 'boolean',
            'include_numbers' => 'boolean',
            'include_symbols' => 'boolean',
        ]);

        $length = $request->input('length', 12);
        $type = $request->input('type', 'secure');
        $pattern = $request->input('pattern');

        try {
            if ($pattern) {
                $password = PasswordGenerator::generateFromPattern($pattern);
            } else {
                switch ($type) {
                    case 'simple':
                        $password = PasswordGenerator::generate($length);
                        break;
                    case 'readable':
                        $password = PasswordGenerator::generateReadable($length);
                        break;
                    case 'word':
                        $password = PasswordGenerator::generateWordBased();
                        break;
                    default:
                        $password = PasswordGenerator::generateSecure(
                            $length,
                            $request->input('include_uppercase', true),
                            $request->input('include_lowercase', true),
                            $request->input('include_numbers', true),
                            $request->input('include_symbols', true)
                        );
                        break;
                }
            }

            $strength = PasswordGenerator::checkStrength($password);

            return response()->json([
                'success' => true,
                'password' => $password,
                'length' => strlen($password),
                'strength' => $strength,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Vérifier la force d'un mot de passe
     */
    public function check(Request $request): JsonResponse
    {
        $request->validate([
            'password' => 'required|string',
        ]);

        $password = $request->input('password');
        $strength = PasswordGenerator::checkStrength($password);

        return response()->json([
            'success' => true,
            'password_length' => strlen($password),
            'strength' => $strength,
        ], 200);
    }
}
