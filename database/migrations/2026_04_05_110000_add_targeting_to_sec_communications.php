<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sec_communications', function (Blueprint $table) {
            // null = envoyer à tout le monde ; array d'IDs = ciblage
            $table->json('poste_ids')->nullable()->after('audio_path');
            $table->json('zone_ids')->nullable()->after('poste_ids');
            // Noms des tours (ex: ["Matin","Soir"]) ou null = tous les tours
            $table->json('tour_ids')->nullable()->after('zone_ids');
        });
    }

    public function down(): void
    {
        Schema::table('sec_communications', function (Blueprint $table) {
            $table->dropColumn(['poste_ids', 'zone_ids', 'tour_ids']);
        });
    }
};
