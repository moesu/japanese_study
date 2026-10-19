<?php

namespace App\Support;

use Illuminate\Support\Facades\File;

class N4Goi
{
    /**
     * @return array<int, array{chapter: int, words: array<int, array{reading: string, word: string, my: string, group: string}>}>
     */
    public static function chapters(): array
    {
        return once(fn () => json_decode(
            File::get(database_path('data/n4_goi.json')),
            true,
            flags: JSON_THROW_ON_ERROR,
        ));
    }

    /**
     * @return array<int, int>
     */
    public static function numbers(): array
    {
        return array_map(
            fn (array $chapter): int => (int) $chapter['chapter'],
            self::chapters(),
        );
    }

    /**
     * @return array{chapter: int, words: array<int, array{reading: string, word: string, my: string, group: string}>}|null
     */
    public static function chapter(int $chapter): ?array
    {
        foreach (self::chapters() as $group) {
            if ((int) $group['chapter'] === $chapter) {
                return $group;
            }
        }

        return null;
    }

    /**
     * @param  array<int, int>  $chapters
     * @return array<int, array{reading: string, word: string, my: string, group: string, chapter: int}>
     */
    public static function wordsFor(array $chapters, string $kind = 'all'): array
    {
        $wanted = array_flip($chapters);
        $words = [];

        foreach (self::chapters() as $chapter) {
            $number = (int) $chapter['chapter'];
            if (! isset($wanted[$number])) {
                continue;
            }

            foreach ($chapter['words'] as $word) {
                if ($kind !== 'all' && self::kindOf($word) !== $kind) {
                    continue;
                }

                $word['chapter'] = $number;
                $words[] = $word;
            }
        }

        return $words;
    }

    /**
     * @param  array{reading: string, word: string, my: string, group: string}  $word
     */
    public static function kindOf(array $word): string
    {
        return N5Goi::kindOf($word);
    }
}
