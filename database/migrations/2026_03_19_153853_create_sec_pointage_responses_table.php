<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sec_pointage_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pointage_id')->constrained('sec_pointages')->cascadeOnDelete();
            $table->foreignId('agent_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('zone_id')->nullable()->constrained('sec_zones')->nullOnDelete();
            $table->foreignId('poste_id')->nullable()->constrained('sec_postes')->nullOnDelete();
            // pending = notification envoyée, pas encore répondu
            // present = agent a confirmé sa présence
            // absent  = n'a pas répondu dans les 15 min
            $table->enum('status', ['pending', 'present', 'absent'])->default('pending');
            $table->timestamp('responded_at')->nullable();
            $table->timestamps();

            $table->unique(['pointage_id', 'agent_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sec_pointage_responses');
    }
};
