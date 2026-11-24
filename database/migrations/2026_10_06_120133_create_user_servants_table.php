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
        Schema::create('user_servants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('servant_id')->constrained('servants')->cascadeOnDelete();
            $table->unsignedTinyInteger('level')->default(1);
            $table->unsignedTinyInteger('skill_1')->default(1);
            $table->unsignedTinyInteger('skill_2')->default(1);
            $table->unsignedTinyInteger('skill_3')->default(1);
            $table->unsignedTinyInteger('bond_level')->default(0);
            $table->unsignedTinyInteger('np_level')->default(1);
            $table->unsignedTinyInteger('ascension')->default(0);
            $table->timestamps();

            $table->unique(['user_id', 'servant_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_servants');
    }
};
