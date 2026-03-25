<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Remplace l'ENUM figé par un VARCHAR nullable
        // pour supporter les noms de tours dynamiques (SecTour) et le pointage local sans tour
        DB::statement("ALTER TABLE sec_pointages MODIFY COLUMN tour VARCHAR(100) NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE sec_pointages MODIFY COLUMN tour ENUM('matin','soir','nuit') NULL");
    }
};
