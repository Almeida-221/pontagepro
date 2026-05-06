<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sec_communications', function (Blueprint $table) {
            // 'audio' = message vocal (existant), 'notification' = push texte uniquement
            $table->string('type', 20)->default('audio')->after('id');
        });
    }

    public function down(): void
    {
        Schema::table('sec_communications', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
};
