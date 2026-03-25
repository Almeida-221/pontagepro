<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('sec_plannings', function (Blueprint $table) {
            // Array of {type: matin|soir|nuit, start: "HH:MM", end: "HH:MM"}
            $table->json('tours')->nullable()->after('rest_days');
        });
    }

    public function down(): void
    {
        Schema::table('sec_plannings', function (Blueprint $table) {
            $table->dropColumn('tours');
        });
    }
};
