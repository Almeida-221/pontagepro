<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        // Default values
        DB::table('site_settings')->insert([
            ['key' => 'site_name',        'value' => 'SB Pointage',          'created_at' => now(), 'updated_at' => now()],
            ['key' => 'site_address',     'value' => '',                      'created_at' => now(), 'updated_at' => now()],
            ['key' => 'site_email',       'value' => 'contact@sbpointage.com','created_at' => now(), 'updated_at' => now()],
            ['key' => 'site_phone',       'value' => '',                      'created_at' => now(), 'updated_at' => now()],
            ['key' => 'whatsapp_number',  'value' => '',                      'created_at' => now(), 'updated_at' => now()],
            ['key' => 'video_url',        'value' => '',                      'created_at' => now(), 'updated_at' => now()],
            ['key' => 'logo_path',        'value' => '',                      'created_at' => now(), 'updated_at' => now()],
            ['key' => 'slide1_path',      'value' => '',                      'created_at' => now(), 'updated_at' => now()],
            ['key' => 'slide2_path',      'value' => '',                      'created_at' => now(), 'updated_at' => now()],
            ['key' => 'slide3_path',      'value' => '',                      'created_at' => now(), 'updated_at' => now()],
            ['key' => 'slide4_path',      'value' => '',                      'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('site_settings');
    }
};
