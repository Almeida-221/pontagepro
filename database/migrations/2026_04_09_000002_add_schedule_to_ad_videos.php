<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ad_videos', function (Blueprint $table) {
            $table->timestamp('published_at')->nullable()->after('is_active')
                  ->comment('Date de début de diffusion (null = immédiat si is_active)');
            $table->timestamp('expires_at')->nullable()->after('published_at')
                  ->comment('Date d\'expiration (null = pas de limite)');
        });
    }

    public function down(): void
    {
        Schema::table('ad_videos', function (Blueprint $table) {
            $table->dropColumn(['published_at', 'expires_at']);
        });
    }
};
