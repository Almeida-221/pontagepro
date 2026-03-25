<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Ouvriers et gérants de chantier
        Schema::create('ouvriers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->string('name', 150);
            $table->string('phone', 20)->nullable();
            $table->enum('role', ['ouvrier', 'gerant_ouvrier'])->default('ouvrier');
            $table->decimal('taux_journalier', 10, 2)->default(0); // salaire par jour
            $table->boolean('is_active')->default(true);
            $table->string('poste', 100)->nullable(); // ex: Maçon, Ferrailleur…
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
        });

        // Pointages journaliers des ouvriers
        Schema::create('ouvrier_pointages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->foreignId('ouvrier_id')->constrained('ouvriers')->onDelete('cascade');
            $table->date('date');
            $table->enum('statut', ['present', 'absent', 'demi'])->default('present');
            // demi = demi-journée (compte pour 0.5 jour)
            $table->unsignedBigInteger('initiated_by')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();
            $table->unique(['ouvrier_id', 'date']); // un seul pointage par jour par ouvrier
        });

        // Paiements effectués (pour calculer le solde restant)
        Schema::create('ouvrier_paiements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->foreignId('ouvrier_id')->constrained('ouvriers')->onDelete('cascade');
            $table->decimal('montant', 12, 2);
            $table->date('date');
            $table->string('note', 255)->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ouvrier_paiements');
        Schema::dropIfExists('ouvrier_pointages');
        Schema::dropIfExists('ouvriers');
    }
};
