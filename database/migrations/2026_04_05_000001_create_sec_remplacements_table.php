<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sec_remplacements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('agent_sortant_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('agent_entrant_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('poste_id')->nullable()->constrained('sec_postes')->nullOnDelete();
            $table->foreignId('zone_id')->nullable()->constrained('sec_zones')->nullOnDelete();
            $table->date('date');
            $table->time('heure_entree');
            $table->time('heure_sortie');
            $table->enum('statut', ['confirme', 'annule'])->default('confirme');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sec_remplacements');
    }
};
