<?php

namespace App\Services;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class PasswordGenerator
{
    /**
     * Générer un mot de passe aléatoire simple
     */
    public static function generate(int $length = 12): string
    {
        return Str::random($length);
    }

    /**
     * Générer un mot de passe avec des règles de sécurité
     */
    public static function generateSecure(
        int $length = 12,
        bool $includeUppercase = true,
        bool $includeLowercase = true,
        bool $includeNumbers = true,
        bool $includeSymbols = true,
        string $customSymbols = '!@#$%^&*()_+-=[]{}|;:,.<>?'
    ): string {
        $characters = '';
        $password = '';

        // Construction du jeu de caractères
        if ($includeLowercase) {
            $characters .= 'abcdefghijklmnopqrstuvwxyz';
        }
        if ($includeUppercase) {
            $characters .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        }
        if ($includeNumbers) {
            $characters .= '0123456789';
        }
        if ($includeSymbols) {
            $characters .= $customSymbols;
        }

        if (empty($characters)) {
            throw new \InvalidArgumentException('Au moins un type de caractère doit être inclus');
        }

        // Assurer qu'au moins un caractère de chaque type requis soit présent
        if ($includeLowercase) {
            $password .= substr(str_shuffle('abcdefghijklmnopqrstuvwxyz'), 0, 1);
        }
        if ($includeUppercase) {
            $password .= substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 1);
        }
        if ($includeNumbers) {
            $password .= substr(str_shuffle('0123456789'), 0, 1);
        }
        if ($includeSymbols) {
            $password .= substr(str_shuffle($customSymbols), 0, 1);
        }

        // Compléter avec des caractères aléatoires
        $remainingLength = $length - strlen($password);
        for ($i = 0; $i < $remainingLength; $i++) {
            $password .= $characters[random_int(0, strlen($characters) - 1)];
        }

        // Mélanger le mot de passe final
        return str_shuffle($password);
    }

    /**
     * Générer un mot de passe lisible (sans caractères ambigus)
     */
    public static function generateReadable(int $length = 12): string
    {
        // Exclure les caractères ambigus : 0, O, l, I, 1
        $characters = 'abcdefghijkmnopqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        $password = '';

        for ($i = 0; $i < $length; $i++) {
            $password .= $characters[random_int(0, strlen($characters) - 1)];
        }

        return $password;
    }

    /**
     * Générer un mot de passe basé sur des mots
     */
    public static function generateWordBased(int $wordCount = 4, string $separator = '-'): string
    {
        $words = [
            'able', 'acid', 'aged', 'also', 'area', 'army', 'away', 'baby', 'back', 'ball',
            'band', 'bank', 'base', 'bath', 'bear', 'beat', 'been', 'beer', 'bell', 'belt',
            'best', 'bike', 'bird', 'blow', 'blue', 'boat', 'body', 'bomb', 'bond', 'bone',
            'book', 'boom', 'boot', 'born', 'boss', 'both', 'bowl', 'bulk', 'burn', 'bush',
            'busy', 'call', 'calm', 'came', 'camp', 'card', 'care', 'cart', 'case', 'cash',
            'cast', 'cell', 'chat', 'chip', 'city', 'club', 'coal', 'coat', 'code', 'cold',
            'come', 'cook', 'cool', 'copy', 'cord', 'core', 'corn', 'cost', 'crew', 'crop',
            'dark', 'data', 'date', 'dawn', 'dead', 'deal', 'dean', 'dear', 'debt', 'deep',
            'deny', 'desk', 'dial', 'diet', 'disc', 'dock', 'door', 'dose', 'down', 'draw',
            'drew', 'drop', 'drug', 'dual', 'duck', 'dump', 'dust', 'duty', 'each', 'earn',
            'east', 'easy', 'edge', 'else', 'even', 'ever', 'exit', 'face', 'fact', 'fail',
            'fair', 'fall', 'farm', 'fast', 'fate', 'fear', 'feed', 'feel', 'feet', 'fell',
            'felt', 'file', 'fill', 'film', 'find', 'fine', 'fire', 'firm', 'fish', 'five',
            'flag', 'flat', 'flow', 'food', 'foot', 'ford', 'form', 'fort', 'four', 'free',
            'from', 'fuel', 'full', 'fund', 'gain', 'game', 'gate', 'gave', 'gear', 'gift',
            'girl', 'give', 'glad', 'glen', 'goal', 'goat', 'gold', 'golf', 'gone', 'good',
            'grab', 'gray', 'grew', 'grid', 'grow', 'gulf', 'hair', 'half', 'hall', 'hand',
            'hang', 'hard', 'harm', 'hate', 'have', 'head', 'hear', 'heat', 'held', 'hell',
            'help', 'here', 'hero', 'hide', 'high', 'hill', 'hint', 'hire', 'hold', 'hole',
            'holy', 'home', 'hope', 'host', 'hour', 'huge', 'hung', 'hunt', 'hurt', 'idea',
        ];

        $selectedWords = [];
        for ($i = 0; $i < $wordCount; $i++) {
            $selectedWords[] = $words[array_rand($words)];
        }

        // Capitaliser aléatoirement certains mots
        $selectedWords = array_map(function ($word) {
            return random_int(0, 1) ? ucfirst($word) : $word;
        }, $selectedWords);

        // Ajouter un nombre à la fin
        $password = implode($separator, $selectedWords);
        $password .= random_int(10, 999);

        return $password;
    }

    /**
     * Générer un mot de passe avec pattern personnalisé
     * Pattern: L = lettre majuscule, l = lettre minuscule, d = chiffre, s = symbole
     */
    public static function generateFromPattern(string $pattern): string
    {
        $password = '';
        $lowercase = 'abcdefghijklmnopqrstuvwxyz';
        $uppercase = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $numbers = '0123456789';
        $symbols = '!@#$%^&*()_+-=[]{}|;:,.<>?';

        for ($i = 0; $i < strlen($pattern); $i++) {
            switch ($pattern[$i]) {
                case 'L':
                    $password .= $uppercase[random_int(0, strlen($uppercase) - 1)];
                    break;
                case 'l':
                    $password .= $lowercase[random_int(0, strlen($lowercase) - 1)];
                    break;
                case 'd':
                    $password .= $numbers[random_int(0, strlen($numbers) - 1)];
                    break;
                case 's':
                    $password .= $symbols[random_int(0, strlen($symbols) - 1)];
                    break;
                default:
                    $password .= $pattern[$i]; // Caractère littéral
                    break;
            }
        }

        return $password;
    }

    /**
     * Vérifier la force d'un mot de passe
     */
    public static function checkStrength(string $password): array
    {
        $score = 0;
        $feedback = [];

        // Longueur
        $length = strlen($password);
        if ($length >= 12) {
            $score += 2;
        } elseif ($length >= 8) {
            $score += 1;
        } else {
            $feedback[] = 'Le mot de passe devrait contenir au moins 8 caractères';
        }

        // Minuscules
        if (preg_match('/[a-z]/', $password)) {
            $score += 1;
        } else {
            $feedback[] = 'Ajouter des lettres minuscules';
        }

        // Majuscules
        if (preg_match('/[A-Z]/', $password)) {
            $score += 1;
        } else {
            $feedback[] = 'Ajouter des lettres majuscules';
        }

        // Chiffres
        if (preg_match('/[0-9]/', $password)) {
            $score += 1;
        } else {
            $feedback[] = 'Ajouter des chiffres';
        }

        // Symboles
        if (preg_match('/[^a-zA-Z0-9]/', $password)) {
            $score += 1;
        } else {
            $feedback[] = 'Ajouter des caractères spéciaux';
        }

        // Variété
        if ($length > 0) {
            $uniqueChars = count(array_unique(str_split($password)));
            if ($uniqueChars / $length > 0.7) {
                $score += 1;
            }
        }

        // Déterminer le niveau
        if ($score >= 6) {
            $level = 'Très fort';
        } elseif ($score >= 4) {
            $level = 'Fort';
        } elseif ($score >= 3) {
            $level = 'Moyen';
        } else {
            $level = 'Faible';
        }

        return [
            'score' => $score,
            'level' => $level,
            'feedback' => $feedback,
        ];
    }

    /**
     * Générer et hasher un mot de passe
     */
    public static function generateAndHash(int $length = 12): array
    {
        $password = self::generateSecure($length);
        $hash = Hash::make($password);

        return [
            'password' => $password,
            'hash' => $hash,
        ];
    }
}
