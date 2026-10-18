<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kanjis', function (Blueprint $table) {
            $table->id();
            $table->string('character', 8)->unique();
            $table->unsignedTinyInteger('strokes');
            $table->string('radical', 8);
            $table->json('onyomi');
            $table->json('kunyomi');
            $table->string('meaning_en');
            $table->string('meaning_my');
            $table->json('meanings');
            $table->json('examples');
            $table->unsignedSmallInteger('sort_order');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kanjis');
    }
};
