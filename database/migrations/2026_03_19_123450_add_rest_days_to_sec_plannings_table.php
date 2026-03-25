<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('sec_plannings', function (Blueprint $table) {
            // Weekday numbers 1=Lundi … 7=Dimanche (recurring weekly rest days)
            $table->json('rest_days')->nullable()->comment('Recurring weekly rest days: 1=Lun … 7=Dim')->after('off_days');
        });
    }

    public function down(): void
    {
        Schema::table('sec_plannings', function (Blueprint $table) {
            $table->dropColumn('rest_days');
        });
    }
};
