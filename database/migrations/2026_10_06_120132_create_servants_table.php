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
        Schema::create('servants', function (Blueprint $table) {
            // Atlas AcademyのサーヴァントIDをそのまま主キーとして使用する（auto increment しない）
            $table->unsignedBigInteger('id')->primary();
            $table->unsignedInteger('collection_no');
            $table->string('name');
            $table->string('class_name');
            $table->unsignedTinyInteger('rarity');
            $table->string('attribute');
            $table->string('np_name');
            $table->string('np_type');
            $table->unsignedInteger('bond_ce_id')->nullable();
            $table->string('bond_ce_name')->nullable();
            $table->string('face_url');
            $table->json('ascension_image_urls');
            $table->string('bond_ce_image_url')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('servants');
    }
};
