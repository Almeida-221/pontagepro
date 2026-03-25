<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            // Link each company back to its web owner (role=client)
            $table->foreignId('owner_user_id')
                  ->nullable()
                  ->after('id')
                  ->constrained('users')
                  ->nullOnDelete();
        });

        // Backfill: link existing companies to their web user via owner_email
        \DB::statement("
            UPDATE companies c
            JOIN users u ON u.email = c.owner_email AND u.role = 'client'
            SET c.owner_user_id = u.id
            WHERE c.owner_user_id IS NULL
        ");
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropForeign(['owner_user_id']);
            $table->dropColumn('owner_user_id');
        });
    }
};
