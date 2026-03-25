<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Drop the unique index first, then make the column nullable
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['email']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('email')->nullable()->unique()->change();
        });
    }

    public function down(): void
    {
        // Revert: first remove nulls (if any) then restore NOT NULL unique
        DB::table('users')->whereNull('email')->update(['email' => '']);

        Schema::table('users', function (Blueprint $table) {
            $table->string('email')->nullable(false)->change();
        });
    }
};
