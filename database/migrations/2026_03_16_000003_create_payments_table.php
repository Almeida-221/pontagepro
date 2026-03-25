<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('worker_id');
            $table->unsignedBigInteger('paid_by_id');
            $table->decimal('amount', 12, 2);
            $table->string('note')->nullable();
            $table->timestamps();

            $table->foreign('worker_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('paid_by_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
