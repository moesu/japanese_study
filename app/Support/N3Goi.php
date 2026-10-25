<?php

namespace App\Support;

use Illuminate\Support\Facades\File;

class N3Goi
{
    /**
     * @return array<int, array{chapter: int, words: array<int, array{reading: string, word: string, my: string, group: string}>}>
     */
    public static function chapters(): array
    {
        return once(fn () => json_decode(
            File::get(database_path('data/n3_goi.json')),
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
     * Part headings for the chapter filter. Part 1 keeps the usual lesson numbers.
     *
     * @return array<string, array<int, int>>
     */
    public static function sections(): array
    {
        $sections = [];

        foreach (self::chapters() as $chapter) {
            $part = (int) ($chapter['part'] ?? 1);
            $sections['အပိုင်း '.self::digits($part)][] = (int) $chapter['chapter'];
        }

        return $sections;
    }

    /**
     * Labels for chapters that are not in Part 1.
     *
     * @return array<int, string>
     */
    public static function labels(): array
    {
        $labels = [];

        foreach (self::chapters() as $chapter) {
            $part = (int) ($chapter['part'] ?? 1);
            if ($part === 1) {
                continue;
            }

            $number = (int) $chapter['chapter'];
            $unit = (int) ($chapter['unit'] ?? ($number % 100));
            $labels[$number] = 'အပိုင်း '.self::digits($part).' အခန်း '.self::digits($unit);
        }

        return $labels;
    }

    private static function digits(int $number): string
    {
        return strtr((string) $number, [
            '0' => '၀',
            '1' => '၁',
            '2' => '၂',
            '3' => '၃',
            '4' => '၄',
            '5' => '၅',
            '6' => '၆',
            '7' => '၇',
            '8' => '၈',
            '9' => '၉',
        ]);
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
