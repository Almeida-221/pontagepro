<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The users.role column is a VARCHAR — no ENUM change needed.
     * New role values: 'gerant_securite', 'agent_securite'
     * This migration documents the intent and adds an index for faster lookups.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Add zone_id for gerant_securite (which zone they manage)
            $table->foreignId('zone_id')->nullable()->after('company_id')->constrained('sec_zones')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['zone_id']);
            $table->dropColumn('zone_id');
        });
    }
};
