<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sec_affectations', function (Blueprint $table) {
            // Planning lié à cette affectation (historique complet par période)
            $table->json('rest_days')->nullable()->after('is_active'); // 1=Lun…7=Dim
            $table->json('off_days')->nullable()->after('rest_days');  // 1-31 congés ponctuels
            $table->json('tours')->nullable()->after('off_days');      // [{type,start,end}]
        });
    }

    public function down(): void
    {
        Schema::table('sec_affectations', function (Blueprint $table) {
            $table->dropColumn(['rest_days', 'off_days', 'tours']);
        });
    }
};
