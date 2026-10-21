<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('page_visits');
    }

    public function down(): void
    {
        Schema::create('page_visits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('path', 500);
            $table->string('method', 10)->default('GET');
            $table->timestamp('visited_at')->index();
            $table->timestamps();

            $table->index(['user_id', 'visited_at']);
        });
    }
};
