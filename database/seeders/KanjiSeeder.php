<?php

namespace Database\Seeders;

use App\Models\Kanji;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class KanjiSeeder extends Seeder
{
    public function run(): void
    {
        $rows = json_decode(File::get(database_path('data/n5_kanji.json')), true, flags: JSON_THROW_ON_ERROR);

        foreach ($rows as $row) {
            $kanji = Kanji::query()->updateOrCreate(
                ['character' => $row['character']],
                $row,
            );

            // Rewrite JSON as real Unicode so reading search LIKE works (not \uXXXX).
            DB::table('kanjis')->where('id', $kanji->id)->update([
                'onyomi' => json_encode($row['onyomi'] ?? [], JSON_UNESCAPED_UNICODE),
                'kunyomi' => json_encode($row['kunyomi'] ?? [], JSON_UNESCAPED_UNICODE),
                'meanings' => json_encode($row['meanings'] ?? [], JSON_UNESCAPED_UNICODE),
                'examples' => json_encode($row['examples'] ?? [], JSON_UNESCAPED_UNICODE),
            ]);
        }
    }
}
