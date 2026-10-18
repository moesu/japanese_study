<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'character',
    'strokes',
    'radical',
    'onyomi',
    'kunyomi',
    'meaning_en',
    'meaning_my',
    'meanings',
    'examples',
    'sort_order',
])]
class Kanji extends Model
{
    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'onyomi' => 'array',
            'kunyomi' => 'array',
            'meanings' => 'array',
            'examples' => 'array',
        ];
    }

    /**
     * Master meanings for kanji that appear inside example words but are outside the N5 set.
     *
     * @var array<string, array{en: string, my: string}>
     */
    private const EXTRA_MEANINGS = [
        '不' => ['en' => 'not', 'my' => 'မဟုတ်'],
        '京' => ['en' => 'capital', 'my' => 'မြို့တော်'],
        '供' => ['en' => 'accompany', 'my' => 'အဖော်'],
        '元' => ['en' => 'origin', 'my' => 'မူလ'],
        '去' => ['en' => 'past', 'my' => 'အတိတ်'],
        '喫' => ['en' => 'consume', 'my' => 'သောက်စား'],
        '図' => ['en' => 'diagram', 'my' => 'ပုံ'],
        '地' => ['en' => 'ground', 'my' => 'မြေ'],
        '堂' => ['en' => 'hall', 'my' => 'ခန်းမ'],
        '屋' => ['en' => 'shop', 'my' => 'ဆိုင်'],
        '心' => ['en' => 'heart', 'my' => 'နှလုံး'],
        '意' => ['en' => 'mind', 'my' => 'စိတ်ကူး'],
        '曜' => ['en' => 'weekday', 'my' => 'နေ့နံ'],
        '有' => ['en' => 'have', 'my' => 'ရှိ'],
        '海' => ['en' => 'sea', 'my' => 'ပင်လယ်'],
        '港' => ['en' => 'port', 'my' => 'ဆိပ်ကမ်း'],
        '物' => ['en' => 'thing', 'my' => 'အရာ'],
        '的' => ['en' => 'target', 'my' => 'ပစ်မှတ်'],
        '神' => ['en' => 'god', 'my' => 'နတ်'],
        '科' => ['en' => 'department', 'my' => 'ဌာန'],
        '英' => ['en' => 'English', 'my' => 'အင်္ဂလိပ်'],
        '茶' => ['en' => 'tea', 'my' => 'လက်ဖက်'],
        '葉' => ['en' => 'leaf', 'my' => 'အရွက်'],
        '達' => ['en' => 'plural', 'my' => 'အများ'],
        '鉄' => ['en' => 'iron', 'my' => 'သံ'],
        '銀' => ['en' => 'silver', 'my' => 'ငွေသတ္တု'],
        '館' => ['en' => 'building', 'my' => 'အဆောက်အအုံ'],
        '鼻' => ['en' => 'nose', 'my' => 'နှာခေါင်း'],
    ];

    public function readingLine(string $field): string
    {
        $readings = $this->{$field} ?? [];

        return $readings === [] ? '—' : implode('、', $readings);
    }

    /**
     * Split a word so each kanji can show its master meaning underneath.
     *
     * @return array<int, array{char: string, kanji: bool, meaning_en: ?string, meaning_my: ?string}>
     */
    public static function charactersIn(string $word): array
    {
        $masters = once(fn () => static::query()->get()->keyBy('character'));
        $pieces = [];

        foreach (mb_str_split($word) as $char) {
            $known = $masters->get($char);
            $extra = self::EXTRA_MEANINGS[$char] ?? null;
            $isKanji = $known !== null || $extra !== null || preg_match('/\p{Han}/u', $char) === 1;

            $pieces[] = [
                'char' => $char,
                'kanji' => $isKanji,
                'meaning_en' => $known->meaning_en ?? $extra['en'] ?? null,
                'meaning_my' => $known->meaning_my ?? $extra['my'] ?? null,
            ];
        }

        return $pieces;
    }
}
