<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ad_videos', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('app_target', ['pointage', 'securite', 'both'])->default('both');
            $table->string('video_url')->nullable()->comment('URL YouTube/externe (embed)');
            $table->string('video_path')->nullable()->comment('Fichier MP4 uploadé');
            $table->string('thumbnail_path')->nullable();
            $table->unsignedSmallInteger('duration_seconds')->default(30)
                  ->comment('Durée d\'affichage avant fermeture auto');
            $table->boolean('is_active')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ad_videos');
    }
};
