<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            // Remove the global unique index on slug
            $table->dropUnique(['slug']);
            // Add a composite unique: same slug can exist but only once per module
            $table->unique(['module_id', 'slug']);
        });
    }

    public function down(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->dropUnique(['module_id', 'slug']);
            $table->unique(['slug']);
        });
    }
};
