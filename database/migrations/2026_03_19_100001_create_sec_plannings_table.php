<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sec_plannings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agent_id')->constrained('users')->cascadeOnDelete();
            $table->json('off_days')->nullable()->comment('Array of day numbers (1-31) that are off');
            $table->timestamps();

            $table->unique('agent_id'); // one planning per agent
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sec_plannings');
    }
};
