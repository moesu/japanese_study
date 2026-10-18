<?php

namespace Database\Seeders;

use App\Models\Kanji;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class KanjiSeeder extends Seeder
{
    public function run(): void
    {
        $rows = json_decode(File::get(database_path('data/n5_kanji.json')), true, flags: JSON_THROW_ON_ERROR);

        foreach ($rows as $row) {
            Kanji::query()->updateOrCreate(
                ['character' => $row['character']],
                $row,
            );
        }
    }
}
