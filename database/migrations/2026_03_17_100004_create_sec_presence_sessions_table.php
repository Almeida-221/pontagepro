<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sec_presence_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('zone_id')->nullable()->constrained('sec_zones')->nullOnDelete();
            $table->foreignId('launched_by')->constrained('users');
            $table->timestamp('launched_at')->useCurrent();
            $table->timestamp('deadline_at')->nullable(); // launched_at + 30 min
            // pending = waiting for confirmations, completed = all responded, expired = deadline passed
            $table->enum('status', ['pending', 'completed', 'expired'])->default('pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sec_presence_sessions');
    }
};
