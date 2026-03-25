<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sec_tours', function (Blueprint $table) {
            $table->time('heure_debut')->nullable()->after('emoji');
            $table->time('heure_fin')->nullable()->after('heure_debut');
        });
    }

    public function down(): void
    {
        Schema::table('sec_tours', function (Blueprint $table) {
            $table->dropColumn(['heure_debut', 'heure_fin']);
        });
    }
};
