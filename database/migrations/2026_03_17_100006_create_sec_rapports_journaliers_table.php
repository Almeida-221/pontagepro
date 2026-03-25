<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sec_rapports_journaliers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('zone_id')->constrained('sec_zones')->cascadeOnDelete();
            $table->date('date');
            $table->foreignId('generated_by')->constrained('users');
            $table->integer('total_agents')->default(0);
            $table->integer('agents_presents')->default(0);
            $table->integer('agents_absents')->default(0);
            $table->text('anomalies')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('validated_at')->nullable();
            $table->timestamp('notified_at')->nullable(); // when admin was notified
            $table->timestamps();

            $table->unique(['zone_id', 'date']); // one report per zone per day
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sec_rapports_journaliers');
    }
};
