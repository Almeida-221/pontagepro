<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $exists = DB::table('site_settings')->where('key', 'video_path')->exists();
        if (!$exists) {
            DB::table('site_settings')->insert([
                'key'        => 'video_path',
                'value'      => '',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        DB::table('site_settings')->where('key', 'video_path')->delete();
    }
};
