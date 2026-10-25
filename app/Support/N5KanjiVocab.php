<?php

namespace App\Support;

use Illuminate\Support\Facades\File;

class N5KanjiVocab
{
    /**
     * Textbook words from every N5 kanji, in teaching order.
     *
     * A word filed under more than one kanji is listed once. `kanji` keeps
     * each character it belongs to, earliest in the teaching order first.
     *
     * @return array<int, array{word: string, reading: string, en: string, my: string, kanji: array<int, string>}>
     */
    public static function words(): array
    {
        return once(function () {
            $kanji = json_decode(
                File::get(database_path('data/n5_kanji.json')),
                true,
                flags: JSON_THROW_ON_ERROR,
            );
            usort($kanji, fn (array $left, array $right) => $left['sort_order'] <=> $right['sort_order']);

            $words = [];
            $index = [];

            foreach ($kanji as $row) {
                foreach (self::entriesFor($row) as $entry) {
                    $key = $entry['word'].'|'.$entry['reading'];
                    if (isset($index[$key])) {
                        $slot = $index[$key];
                        if (! in_array($row['character'], $words[$slot]['kanji'], true)) {
                            $words[$slot]['kanji'][] = $row['character'];
                        }

                        continue;
                    }

                    $index[$key] = count($words);
                    $words[] = [
                        'word' => $entry['word'],
                        'reading' => $entry['reading'],
                        'en' => $entry['en'] ?? '',
                        'my' => $entry['my'] ?? '',
                        'kanji' => [$row['character']],
                    ];
                }
            }

            return $words;
        });
    }

    /**
     * @param  array{character: string, examples?: array<int, array{word: string, reading: string, en?: string, my?: string}>}  $row
     * @return array<int, array{word: string, reading: string, en?: string, my?: string}>
     */
    private static function entriesFor(array $row): array
    {
        $groups = N5PersonVocab::groupsFor($row['character']);
        $shown = [];
        foreach ($groups as $group) {
            foreach ($group['words'] as $word) {
                $shown[$word['word']] = true;
            }
        }

        $entries = collect($groups)->flatMap(fn (array $group) => $group['words'])->all();
        $chapter = array_values(array_filter(
            N5ChapterVocab::wordsFor($row['character']),
            fn (array $word) => ! isset($shown[$word['word']]),
        ));

        if ($entries !== [] || $chapter !== []) {
            return [...$entries, ...$chapter];
        }

        return $row['examples'] ?? [];
    }
}
