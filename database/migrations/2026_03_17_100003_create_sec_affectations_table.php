<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sec_affectations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agent_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('poste_id')->constrained('sec_postes')->cascadeOnDelete();
            $table->foreignId('assigned_by')->constrained('users');
            $table->timestamp('started_at')->useCurrent();
            $table->timestamp('ended_at')->nullable();
            $table->boolean('is_active')->default(true);
            // First arrival GPS validation
            $table->timestamp('validated_at')->nullable();
            $table->decimal('validation_latitude', 10, 7)->nullable();
            $table->decimal('validation_longitude', 10, 7)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sec_affectations');
    }
};
