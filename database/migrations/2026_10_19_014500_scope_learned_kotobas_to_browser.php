<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('learned_kotobas', function (Blueprint $table) {
            $table->string('device', 64)->default('');
            $table->dropUnique(['word', 'reading']);
            $table->unique(['device', 'word', 'reading']);
        });
    }

    public function down(): void
    {
        Schema::table('learned_kotobas', function (Blueprint $table) {
            $table->dropUnique(['device', 'word', 'reading']);
            $table->dropColumn('device');
            $table->unique(['word', 'reading']);
        });
    }
};
