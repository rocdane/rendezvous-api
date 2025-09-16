<?php

namespace App\Console\Commands;

use App\Services\PasswordGenerator;
use Illuminate\Console\Command;

class GeneratePasswordCommand extends Command
{
    protected $signature = 'password:generate 
                          {--length=12 : Longueur du mot de passe}
                          {--type=secure : Type de mot de passe (simple|secure|readable|word)}
                          {--pattern= : Pattern personnalisé (ex: Lllddd)}
                          {--count=1 : Nombre de mots de passe à générer}';

    protected $description = 'Générer des mots de passe aléatoires';

    public function handle()
    {
        $length = $this->option('length');
        $type = $this->option('type');
        $pattern = $this->option('pattern');
        $count = $this->option('count');

        $this->info("Génération de {$count} mot(s) de passe:");
        $this->newLine();

        for ($i = 0; $i < $count; $i++) {
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
                        $password = PasswordGenerator::generateSecure($length);
                        break;
                }
            }

            $strength = PasswordGenerator::checkStrength($password);
            
            $this->line("Mot de passe: <fg=green>{$password}</>");
            $this->line("Force: <fg=yellow>{$strength['level']}</> (Score: {$strength['score']}/7)");
            
            if (!empty($strength['feedback'])) {
                $this->warn('Suggestions: ' . implode(', ', $strength['feedback']));
            }
            
            $this->newLine();
        }
    }
}
