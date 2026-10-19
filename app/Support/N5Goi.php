<?php

namespace App\Support;

use Illuminate\Support\Facades\File;

class N5Goi
{
    /**
     * @return array<int, array{chapter: int, words: array<int, array{reading: string, word: string, my: string, group: string}>}>
     */
    public static function chapters(): array
    {
        return once(fn () => json_decode(
            File::get(database_path('data/n5_goi.json')),
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

    public static function lessonLabel(int $chapter): string
    {
        $number = strtr((string) $chapter, [
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

        return 'အခန်း '.$number;
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
        if (in_array($word['group'], ['Ⅰ', 'Ⅱ', 'Ⅲ'], true)) {
            return 'verb';
        }

        if (str_contains($word['reading'].$word['word'], '[な]')) {
            return 'na-adj';
        }

        $reading = self::keys($word['reading'])[0] ?? $word['reading'];
        if (preg_match('/(ます|ました|ません|ましょう|ください)$/u', $reading) === 1) {
            return 'verb';
        }

        foreach (array_merge(self::keys($word['reading']), self::keys($word['word'])) as $key) {
            $matched = self::kotobaKind($key);
            if ($matched !== null) {
                return $matched;
            }
        }

        if (str_starts_with($word['reading'], '～')
            || preg_match('/[。？?]/u', $word['reading']) === 1) {
            return 'other';
        }

        if (preg_match('/い$/u', $reading) === 1 && str_contains($word['my'], 'သော')) {
            return 'i-adj';
        }

        if (preg_match('/[\x{3040}-\x{30FF}\x{4E00}-\x{9FFF}]/u', $reading) === 1) {
            return 'noun';
        }

        return 'other';
    }

    /**
     * @return array<string, list<array{kind: string, verb_form: bool}>>
     */
    private static function kotobaIndex(): array
    {
        return once(function () {
            $entries = json_decode(
                File::get(database_path('data/n5_kotoba.json')),
                true,
                flags: JSON_THROW_ON_ERROR,
            );
            $index = [];

            foreach ($entries as $entry) {
                $masu = (string) ($entry['masu'] ?? '');
                foreach (['reading', 'word', 'masu'] as $field) {
                    $value = trim((string) ($entry[$field] ?? ''));
                    if ($value === '') {
                        continue;
                    }

                    $index[$value][] = [
                        'kind' => (string) $entry['kind'],
                        'verb_form' => $field === 'masu' || ($masu !== '' && $value === $masu),
                    ];
                }
            }

            return $index;
        });
    }

    private static function kotobaKind(string $key): ?string
    {
        foreach (self::kotobaIndex()[$key] ?? [] as $match) {
            if ($match['kind'] === 'verb' && ! $match['verb_form']) {
                continue;
            }

            return $match['kind'];
        }

        return null;
    }

    /**
     * @return array<int, string>
     */
    private static function keys(string $value): array
    {
        $value = trim($value);
        if ($value === '') {
            return [];
        }

        $plain = preg_replace('/[。、？?！!・]/u', '', $value) ?? $value;
        $withoutNotes = preg_replace('/\[[^\]]*\]/u', '', $plain) ?? $plain;
        $withoutBrackets = preg_replace('/[\[\]]/u', '', $plain) ?? $plain;
        $keys = [];

        foreach ([$value, $plain, $withoutNotes, $withoutBrackets] as $candidate) {
            $candidate = trim($candidate);
            if ($candidate !== '') {
                $keys[] = $candidate;
            }

            $head = trim(preg_split('/[（(\/]/u', $candidate)[0] ?? '');
            if ($head !== '') {
                $keys[] = $head;
            }
        }

        return array_values(array_unique($keys));
    }
}
