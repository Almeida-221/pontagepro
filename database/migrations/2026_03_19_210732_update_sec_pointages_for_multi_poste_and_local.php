<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sec_pointages', function (Blueprint $table) {
            // Liste des postes sélectionnés (null = tous les postes)
            $table->json('poste_ids')->nullable()->after('poste_id');
        });

        // Rendre le tour nullable pour le pointage local (sans tour défini)
        DB::statement("ALTER TABLE sec_pointages MODIFY COLUMN tour ENUM('matin','soir','nuit') NULL");
    }

    public function down(): void
    {
        Schema::table('sec_pointages', function (Blueprint $table) {
            $table->dropColumn('poste_ids');
        });
        DB::statement("ALTER TABLE sec_pointages MODIFY COLUMN tour ENUM('matin','soir','nuit') NOT NULL");
    }
};
