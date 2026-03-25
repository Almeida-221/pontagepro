<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('worker_categories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('profession_id');
            $table->string('name', 100);
            $table->decimal('daily_rate', 12, 2)->default(0);
            $table->timestamps();

            $table->foreign('profession_id')->references('id')->on('professions')->onDelete('cascade');
            $table->unique(['profession_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('worker_categories');
    }
};
