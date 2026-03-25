<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Alter enum to add company_admin role
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('super_admin','client','company_admin') NOT NULL DEFAULT 'client'");

        Schema::table('users', function (Blueprint $table) {
            $table->string('phone', 20)->nullable()->after('email');
            $table->string('pin_code', 255)->nullable()->after('phone'); // hashed PIN
            $table->boolean('is_active')->default(true)->after('pin_code');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['phone', 'pin_code', 'is_active']);
        });
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('super_admin','client') NOT NULL DEFAULT 'client'");
    }
};
