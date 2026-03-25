<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sec_presence_confirmations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')->constrained('sec_presence_sessions')->cascadeOnDelete();
            $table->foreignId('agent_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('poste_id')->constrained('sec_postes');
            // pending = waiting, confirmed = responded, absent = deadline passed without response
            $table->enum('status', ['pending', 'confirmed', 'absent'])->default('pending');
            $table->timestamp('confirmed_at')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->boolean('is_on_post')->nullable();   // null = not yet confirmed
            $table->integer('distance_meters')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sec_presence_confirmations');
    }
};
