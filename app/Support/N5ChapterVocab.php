<?php

namespace App\Support;

use Illuminate\Support\Facades\File;

class N5ChapterVocab
{
    /**
     * Chapter 1–10 words filed under this kanji.
     *
     * @return array<int, array{word: string, reading: string, en: string, my: string}>
     */
    public static function wordsFor(string $character): array
    {
        $groups = once(fn () => json_decode(
            File::get(database_path('data/n5_chapter_vocab.json')),
            true,
            flags: JSON_THROW_ON_ERROR,
        ));

        foreach ($groups as $group) {
            if ($group['character'] === $character) {
                return $group['words'];
            }
        }

        return [];
    }
}
