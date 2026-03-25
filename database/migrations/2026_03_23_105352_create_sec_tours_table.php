<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sec_tours', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->string('nom', 100);
            $table->string('emoji', 10)->default('🕐');
            $table->unsignedTinyInteger('ordre')->default(1);
            $table->timestamps();
        });

        // Change sec_pointages.tour from ENUM to VARCHAR to support custom tour names
        \DB::statement("SET sql_mode = ''");
        \DB::statement("ALTER TABLE sec_pointages MODIFY COLUMN tour VARCHAR(100) NOT NULL DEFAULT ''");
        \DB::statement("SET sql_mode = 'STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION'");
    }

    public function down(): void
    {
        Schema::dropIfExists('sec_tours');
        \DB::statement("ALTER TABLE sec_pointages MODIFY COLUMN tour ENUM('matin','soir','nuit') NOT NULL");
    }
};
