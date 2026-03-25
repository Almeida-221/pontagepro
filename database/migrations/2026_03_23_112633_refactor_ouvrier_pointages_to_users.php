<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Supprimer les tables qui référencent ouvriers
        Schema::dropIfExists('ouvrier_paiements');
        Schema::dropIfExists('ouvrier_pointages');
        Schema::dropIfExists('ouvriers');

        // Recréer en référençant users directement
        Schema::create('ouvrier_pointages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->date('date');
            $table->enum('statut', ['present', 'absent', 'demi'])->default('present');
            $table->unsignedBigInteger('initiated_by')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'date']);
        });

        Schema::create('ouvrier_paiements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->decimal('montant', 12, 2);
            $table->date('date');
            $table->string('note', 255)->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
        });

        // Ajouter taux_journalier aux users si pas déjà présent
        if (!Schema::hasColumn('users', 'taux_journalier')) {
            Schema::table('users', function (Blueprint $table) {
                $table->decimal('taux_journalier', 10, 2)->default(0)->after('salary');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('ouvrier_paiements');
        Schema::dropIfExists('ouvrier_pointages');
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('taux_journalier');
        });
    }
};
