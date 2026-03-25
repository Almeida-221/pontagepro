<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sec_pointages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('initiated_by')->constrained('users')->cascadeOnDelete();
            // null = toutes les zones / tous les postes
            $table->foreignId('zone_id')->nullable()->constrained('sec_zones')->nullOnDelete();
            $table->foreignId('poste_id')->nullable()->constrained('sec_postes')->nullOnDelete();
            $table->enum('tour', ['matin', 'soir', 'nuit']);
            $table->enum('type', ['remote', 'local'])->default('remote');
            $table->enum('status', ['pending', 'completed', 'cancelled'])->default('pending');
            $table->date('date');
            $table->timestamp('expires_at'); // created_at + 15 min
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sec_pointages');
    }
};
