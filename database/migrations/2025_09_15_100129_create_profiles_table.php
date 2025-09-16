<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enums\StatutProfile;
use App\Enums\NiveauAcces;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('profiles', function (Blueprint $table) {
            $table->id();
            $table->string('type')->index();
            $table->string('nom');
            $table->string('prenom');
            $table->string('email')->unique();
            $table->string('telephone')->nullable();
            $table->enum('statut', StatutProfile::values())->default(StatutProfile::EN_ATTENTE->value);
            $table->string('photo')->nullable();
            $table->text('description')->nullable();
            $table->date('date_naissance')->nullable();
            $table->text('adresse')->nullable();
            $table->string('ville')->nullable();
            $table->string('code_postal')->nullable();
            $table->string('pays')->default('France');
            $table->json('metadata')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('preference_id')->nullable()->constrained()->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();
            $table->index(['type', 'statut']);
            $table->index(['nom', 'prenom']);
            $table->index('email');
        });

        Schema::create('profile_administrateurs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profile_id')->constrained()->cascadeOnDelete();
            $table->enum('niveau_acces', NiveauAcces::values())->default(NiveauAcces::MODERATEUR->value);
            $table->json('services_gerees')->nullable();
            $table->timestamps();
        });

        Schema::create('profile_utilisateurs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profile_id')->constrained()->cascadeOnDelete();
            $table->json('specialites')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('profiles');
        Schema::dropIfExists('profile_administrateurs');
        Schema::dropIfExists('profile_utilisateurs');
    }
};
