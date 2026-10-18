<?php

namespace App\Support;

use Illuminate\Support\Facades\File;

class N5PersonVocab
{
    /**
     * N5 vocabulary for the person kanji 人.
     *
     * @return array<int, array{label: string, words: array<int, array{word: string, reading: string, en: string, my: string}>}>
     */
    public static function groupsFor(string $character): array
    {
        if ($character !== '人') {
            return [];
        }

        return json_decode(
            File::get(database_path('data/n5_person_vocab.json')),
            true,
            flags: JSON_THROW_ON_ERROR,
        );
    }
}
