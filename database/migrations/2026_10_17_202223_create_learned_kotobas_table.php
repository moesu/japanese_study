<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('learned_kotobas', function (Blueprint $table) {
            $table->id();
            $table->string('word');
            $table->string('reading');
            $table->timestamps();

            $table->unique(['word', 'reading']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('learned_kotobas');
    }
};
