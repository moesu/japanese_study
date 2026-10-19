<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('device', 64)->nullable()->unique();
        });

        Schema::table('learned_kotobas', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('id')->constrained()->cascadeOnDelete();
            $table->boolean('learned')->default(true)->after('reading');
        });

        $now = now();
        $devices = DB::table('learned_kotobas')->distinct()->pluck('device');

        foreach ($devices as $device) {
            $device = (string) $device;
            $userId = $device === ''
                ? null
                : DB::table('users')->where('device', $device)->value('id');

            if ($userId === null) {
                $userId = DB::table('users')->insertGetId([
                    'name' => $device === '' ? 'Earlier marks' : 'Learner',
                    'email' => ($device === '' ? 'earlier-marks' : $device).'@learners.local',
                    'password' => Hash::make(Str::random(40)),
                    'device' => $device === '' ? null : $device,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            DB::table('learned_kotobas')->where('device', $device)->update([
                'user_id' => $userId,
                'learned' => true,
            ]);
        }

        Schema::table('learned_kotobas', function (Blueprint $table) {
            $table->dropUnique(['device', 'word', 'reading']);
            $table->dropColumn('device');
            $table->unique(['user_id', 'word', 'reading']);
        });
    }

    public function down(): void
    {
        Schema::table('learned_kotobas', function (Blueprint $table) {
            $table->dropUnique(['user_id', 'word', 'reading']);
            $table->string('device', 64)->default('');
        });

        $devices = DB::table('users')->whereNotNull('device')->pluck('device', 'id');
        foreach ($devices as $userId => $device) {
            DB::table('learned_kotobas')->where('user_id', $userId)->update(['device' => $device]);
        }

        Schema::table('learned_kotobas', function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_id');
            $table->dropColumn('learned');
            $table->unique(['device', 'word', 'reading']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('device');
        });
    }
};
