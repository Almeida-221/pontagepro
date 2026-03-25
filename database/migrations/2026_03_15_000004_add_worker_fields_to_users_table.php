<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('address', 255)->nullable()->after('phone');
            $table->unsignedBigInteger('profession_id')->nullable()->after('address');
            $table->unsignedBigInteger('category_id')->nullable()->after('profession_id');
            $table->string('id_photo_front', 255)->nullable()->after('category_id');
            $table->string('id_photo_back', 255)->nullable()->after('id_photo_front');

            $table->foreign('profession_id')->references('id')->on('professions')->onDelete('set null');
            $table->foreign('category_id')->references('id')->on('worker_categories')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['profession_id']);
            $table->dropForeign(['category_id']);
            $table->dropColumn(['address', 'profession_id', 'category_id', 'id_photo_front', 'id_photo_back']);
        });
    }
};
