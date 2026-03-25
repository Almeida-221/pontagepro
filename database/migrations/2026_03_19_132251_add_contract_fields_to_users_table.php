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
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_employed')->default(false)->after('is_active');
            $table->decimal('salary', 10, 2)->nullable()->after('is_employed');
            // CDI | CDD | prestataire (non embauché, 1 an révocable)
            $table->enum('contract_type', ['CDI', 'CDD', 'prestataire'])->nullable()->after('salary');
            $table->date('contract_start')->nullable()->after('contract_type');
            $table->date('contract_end')->nullable()->after('contract_start');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['is_employed', 'salary', 'contract_type', 'contract_start', 'contract_end']);
        });
    }
};
