<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

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
     * Keep Japanese / Myanmar text searchable with LIKE (not \uXXXX escapes).
     *
     * @param  mixed  $value
     */
    protected function asJson($value, $flags = 0)
    {
        return parent::asJson($value, $flags | JSON_UNESCAPED_UNICODE);
    }

    /**
     * Master meanings for kanji that appear inside example words but are outside the N5 set.
     *
     * @var array<string, array{en: string, my: string}>
     */
    private const EXTRA_MEANINGS = [
        '不' => ['en' => 'not', 'my' => 'မဟုတ်'],
        '京' => ['en' => 'capital', 'my' => 'မြို့တော်'],
        '休' => ['en' => 'rest', 'my' => 'နား'],
        '供' => ['en' => 'accompany', 'my' => 'အဖော်'],
        '元' => ['en' => 'origin', 'my' => 'မူလ'],
        '六' => ['en' => 'six', 'my' => 'ခြောက်'],
        '切' => ['en' => 'cut', 'my' => 'ဖြတ်'],
        '去' => ['en' => 'past', 'my' => 'အတိတ်'],
        '協' => ['en' => 'cooperate', 'my' => 'ပူးပေါင်း'],
        '喫' => ['en' => 'consume', 'my' => 'သောက်စား'],
        '図' => ['en' => 'diagram', 'my' => 'ပုံ'],
        '大' => ['en' => 'big', 'my' => 'ကြီး'],
        '学' => ['en' => 'study', 'my' => 'ပညာ'],
        '改' => ['en' => 'revise', 'my' => 'ပြင်'],
        '父' => ['en' => 'father', 'my' => 'အဖေ'],
        '地' => ['en' => 'ground', 'my' => 'မြေ'],
        '堂' => ['en' => 'hall', 'my' => 'ခန်းမ'],
        '屋' => ['en' => 'shop', 'my' => 'ဆိုင်'],
        '紙' => ['en' => 'paper', 'my' => 'စက္ကူ'],
        '心' => ['en' => 'heart', 'my' => 'နှလုံး'],
        '意' => ['en' => 'mind', 'my' => 'စိတ်ကူး'],
        '握' => ['en' => 'grasp', 'my' => 'ဆွဲ'],
        '曜' => ['en' => 'weekday', 'my' => 'နေ့နံ'],
        '有' => ['en' => 'have', 'my' => 'ရှိ'],
        '服' => ['en' => 'clothes', 'my' => 'အဝတ်'],
        '泳' => ['en' => 'swim', 'my' => 'ကူး'],
        '海' => ['en' => 'sea', 'my' => 'ပင်လယ်'],
        '港' => ['en' => 'port', 'my' => 'ဆိပ်ကမ်း'],
        '現' => ['en' => 'present', 'my' => 'လက်ရှိ'],
        '生' => ['en' => 'life', 'my' => 'မွေး'],
        '産' => ['en' => 'birth', 'my' => 'မွေးဖွား'],
        '物' => ['en' => 'thing', 'my' => 'အရာ'],
        '的' => ['en' => 'target', 'my' => 'ပစ်မှတ်'],
        '神' => ['en' => 'god', 'my' => 'နတ်'],
        '科' => ['en' => 'department', 'my' => 'ဌာန'],
        '個' => ['en' => 'piece', 'my' => 'လုံး'],
        '紅' => ['en' => 'crimson', 'my' => 'နီ'],
        '計' => ['en' => 'measure', 'my' => 'တိုင်း'],
        '視' => ['en' => 'see', 'my' => 'ကြည့်'],
        '覚' => ['en' => 'awake', 'my' => 'နိုး'],
        '薬' => ['en' => 'medicine', 'my' => 'ဆေး'],
        '運' => ['en' => 'carry', 'my' => 'သယ်'],
        '転' => ['en' => 'roll', 'my' => 'လှည့်'],
        '札' => ['en' => 'ticket', 'my' => 'လက်မှတ်'],
        '料' => ['en' => 'fee', 'my' => 'ကြေး'],
        '英' => ['en' => 'English', 'my' => 'အင်္ဂလိပ်'],
        '茶' => ['en' => 'tea', 'my' => 'လက်ဖက်'],
        '葉' => ['en' => 'leaf', 'my' => 'အရွက်'],
        '達' => ['en' => 'plural', 'my' => 'အများ'],
        '鉄' => ['en' => 'iron', 'my' => 'သံ'],
        '銀' => ['en' => 'silver', 'my' => 'ငွေသတ္တု'],
        '香' => ['en' => 'fragrance', 'my' => 'မွှေး'],
        '館' => ['en' => 'building', 'my' => 'အဆောက်အအုံ'],
        '前' => ['en' => 'before', 'my' => 'ရှေ့'],
        '時' => ['en' => 'time', 'my' => 'အချိန်'],
        '天' => ['en' => 'sky', 'my' => 'ကောင်းကင်'],
        '宝' => ['en' => 'treasure', 'my' => 'ရတနာ'],
        '林' => ['en' => 'woods', 'my' => 'တော'],
        '瓶' => ['en' => 'bottle', 'my' => 'ပုလင်း'],
        '見' => ['en' => 'see', 'my' => 'ကြည့်'],
        '鼻' => ['en' => 'nose', 'my' => 'နှာခေါင်း'],
        '部' => ['en' => 'section', 'my' => 'ဌာန'],
        '仕' => ['en' => 'serve', 'my' => 'အလုပ်'],
        '事' => ['en' => 'matter', 'my' => 'ကိစ္စ'],
        '授' => ['en' => 'teach', 'my' => 'သင်ပေး'],
        '業' => ['en' => 'work', 'my' => 'အလုပ်'],
        '議' => ['en' => 'discuss', 'my' => 'ဆွေးနွေး'],
        '側' => ['en' => 'side', 'my' => 'ဘက်'],
        '折' => ['en' => 'turn', 'my' => 'ကွေ့'],
        '机' => ['en' => 'desk', 'my' => 'စားပွဲ'],
        '着' => ['en' => 'wear', 'my' => 'ဝတ်'],
        '式' => ['en' => 'ceremony', 'my' => 'အခမ်းအနား'],
        '進' => ['en' => 'advance', 'my' => 'တက်'],
        '活' => ['en' => 'active', 'my' => 'လှုပ်ရှား'],
        '誕' => ['en' => 'birth', 'my' => 'မွေး'],
        '漢' => ['en' => 'China', 'my' => 'ဟန်'],
        '文' => ['en' => 'writing', 'my' => 'စာ'],
        '習' => ['en' => 'learn', 'my' => 'လေ့ကျင့်'],
        '刺' => ['en' => 'pierce', 'my' => 'ထိုး'],
        '育' => ['en' => 'raise', 'my' => 'မွေးမြူ'],
        '重' => ['en' => 'heavy', 'my' => 'လေး'],
        '帳' => ['en' => 'notebook', 'my' => 'မှတ်တမ်း'],
        '性' => ['en' => 'nature', 'my' => 'သဘာဝ'],
        '彼' => ['en' => 'he', 'my' => 'သူ'],
        '優' => ['en' => 'actor', 'my' => 'မင်းသား'],
        '祖' => ['en' => 'ancestor', 'my' => 'ဘိုးဘေး'],
        '型' => ['en' => 'type', 'my' => 'အမျိုးအစား'],
        '盲' => ['en' => 'blind', 'my' => 'မျက်မမြင်'],
        '導' => ['en' => 'guide', 'my' => 'လမ်းပြ'],
        '馬' => ['en' => 'horse', 'my' => 'မြင်း'],
        '夏' => ['en' => 'summer', 'my' => 'နွေ'],
        '阪' => ['en' => 'slope', 'my' => 'ကုန်း'],
        '列' => ['en' => 'row', 'my' => 'တန်း'],
        '明' => ['en' => 'bright', 'my' => 'လင်း'],
        '旅' => ['en' => 'travel', 'my' => 'ခရီး'],
        '宅' => ['en' => 'home', 'my' => 'အိမ်'],
        '映' => ['en' => 'reflect', 'my' => 'ရိုက်'],
        '画' => ['en' => 'picture', 'my' => 'ပုံ'],
        '嫌' => ['en' => 'dislike', 'my' => 'မုန်း'],
        '銭' => ['en' => 'coin', 'my' => 'ကြွေ'],
        '席' => ['en' => 'seat', 'my' => 'နေရာ'],
        '正' => ['en' => 'correct', 'my' => 'မှန်'],
        '専' => ['en' => 'exclusive', 'my' => 'အထူး'],
        '親' => ['en' => 'parent', 'my' => 'မိဘ'],
    ];

    /**
     * Readings used inside compounds that are not stored on the kanji row.
     *
     * @var array<string, array<int, string>>
     */
    private const EXTRA_READINGS = [
        '時' => ['と'],
        '上' => ['うわ'],
    ];

    public function readingLine(string $field): string
    {
        $readings = $this->{$field} ?? [];

        return $readings === [] ? '—' : implode('、', $readings);
    }

    /**
     * Split a word so each N5 kanji can show its master meaning underneath.
     * Kanji outside the N5 set are written in hiragana from the word reading.
     *
     * @return array<int, array{char: string, kanji: bool, meaning_en: ?string, meaning_my: ?string}>
     */
    public static function charactersIn(string $word, ?string $reading = null): array
    {
        $masters = once(fn () => static::query()->get()->keyBy('character'));

        if ($reading !== null && $reading !== '') {
            $aligned = self::alignToReading($word, $reading, $masters);
            if ($aligned !== null) {
                return $aligned;
            }
        }

        $pieces = [];

        foreach (mb_str_split($word) as $char) {
            $pieces[] = self::pieceFor($char, $masters);
        }

        return $pieces;
    }

    /**
     * @param  Collection<string, self>  $masters
     * @return array<int, array{char: string, kanji: bool, meaning_en: ?string, meaning_my: ?string}>|null
     */
    private static function alignToReading(string $word, string $reading, $masters): ?array
    {
        $wordChars = mb_str_split($word);
        $readingChars = mb_str_split(self::toHiragana($reading));
        $pieces = [];
        $wordIndex = 0;
        $readingIndex = 0;
        $wordLength = count($wordChars);
        $readingLength = count($readingChars);

        while ($wordIndex < $wordLength) {
            if (! self::isKanjiCharacter($wordChars[$wordIndex])) {
                $chunk = '';
                while ($wordIndex < $wordLength && ! self::isKanjiCharacter($wordChars[$wordIndex])) {
                    $expected = self::toHiragana($wordChars[$wordIndex]);
                    if (! isset($readingChars[$readingIndex]) || $readingChars[$readingIndex] !== $expected) {
                        return null;
                    }
                    $chunk .= $wordChars[$wordIndex];
                    $wordIndex++;
                    $readingIndex++;
                }
                $pieces[] = self::kanaPiece($chunk);

                continue;
            }

            $run = [];
            while ($wordIndex < $wordLength && self::isKanjiCharacter($wordChars[$wordIndex])) {
                $run[] = $wordChars[$wordIndex];
                $wordIndex++;
            }

            $anchor = '';
            $look = $wordIndex;
            while ($look < $wordLength && ! self::isKanjiCharacter($wordChars[$look])) {
                $anchor .= self::toHiragana($wordChars[$look]);
                $look++;
            }

            $rest = implode('', array_slice($readingChars, $readingIndex));
            if ($anchor === '') {
                $slice = $rest;
                $readingIndex = $readingLength;
            } else {
                $position = mb_strpos($rest, $anchor);
                if ($position === false) {
                    return null;
                }
                $slice = mb_substr($rest, 0, $position);
                $readingIndex += $position;
            }

            foreach (self::splitKanjiRun($run, $slice, $masters) as $piece) {
                $pieces[] = $piece;
            }
        }

        if ($readingIndex !== $readingLength) {
            return null;
        }

        return self::mergeKanaPieces($pieces);
    }

    /**
     * @param  array<int, string>  $run
     * @param  Collection<string, self>  $masters
     * @return array<int, array{char: string, kanji: bool, meaning_en: ?string, meaning_my: ?string}>
     */
    private static function splitKanjiRun(array $run, string $slice, $masters): array
    {
        $sliceChars = $slice === '' ? [] : mb_str_split($slice);
        $pieces = [];
        $charIndex = 0;
        $sliceIndex = 0;
        $runLength = count($run);

        while ($charIndex < $runLength) {
            $matched = self::matchingReading($run[$charIndex], array_slice($sliceChars, $sliceIndex), $masters);
            if ($matched !== null && $masters->has($run[$charIndex])) {
                $pieces[] = self::pieceFor($run[$charIndex], $masters);
                $sliceIndex += mb_strlen($matched);
                $charIndex++;

                continue;
            }

            $foundLater = false;
            for ($later = $charIndex + 1; $later < $runLength; $later++) {
                if (! $masters->has($run[$later])) {
                    continue;
                }
                $laterReading = self::readingInside(array_slice($sliceChars, $sliceIndex), $run[$later], $masters);
                if ($laterReading === null) {
                    continue;
                }
                $hiragana = implode('', array_slice($sliceChars, $sliceIndex, $laterReading['offset']));
                if ($hiragana !== '') {
                    $pieces[] = self::kanaPiece($hiragana);
                }
                $pieces[] = self::pieceFor($run[$later], $masters);
                $sliceIndex += $laterReading['offset'] + mb_strlen($laterReading['reading']);
                $charIndex = $later + 1;
                $foundLater = true;
                break;
            }

            if ($foundLater) {
                continue;
            }

            $remainder = array_slice($run, $charIndex);
            $allInSet = true;
            foreach ($remainder as $character) {
                if (! $masters->has($character)) {
                    $allInSet = false;
                    break;
                }
            }
            if ($allInSet) {
                foreach ($remainder as $character) {
                    $pieces[] = self::pieceFor($character, $masters);
                }
            } else {
                $hiragana = implode('', array_slice($sliceChars, $sliceIndex));
                if ($hiragana !== '') {
                    $pieces[] = self::kanaPiece($hiragana);
                }
            }
            break;
        }

        return $pieces;
    }

    /**
     * @param  array<int, string>  $sliceChars
     * @param  Collection<string, self>  $masters
     */
    private static function matchingReading(string $character, array $sliceChars, $masters): ?string
    {
        if (! $masters->has($character)) {
            return null;
        }

        foreach (self::readingsOf($masters->get($character)) as $reading) {
            $readingChars = mb_str_split($reading);
            $length = count($readingChars);
            if ($length === 0 || $length > count($sliceChars)) {
                continue;
            }
            if (array_slice($sliceChars, 0, $length) === $readingChars) {
                return $reading;
            }
        }

        return null;
    }

    /**
     * @param  array<int, string>  $sliceChars
     * @param  Collection<string, self>  $masters
     * @return array{offset: int, reading: string}|null
     */
    private static function readingInside(array $sliceChars, string $character, $masters): ?array
    {
        $haystack = implode('', $sliceChars);
        foreach (self::readingsOf($masters->get($character)) as $reading) {
            $position = mb_strpos($haystack, $reading);
            if ($position === false) {
                continue;
            }

            return [
                'offset' => $position,
                'reading' => $reading,
            ];
        }

        return null;
    }

    /**
     * @return array<int, string>
     */
    private static function readingsOf(self $kanji): array
    {
        $readings = [];
        foreach (array_merge($kanji->onyomi ?? [], $kanji->kunyomi ?? [], self::EXTRA_READINGS[$kanji->character] ?? []) as $reading) {
            $stem = self::toHiragana((string) $reading);
            $stem = preg_replace('/\([^)]*\)/u', '', $stem) ?? $stem;
            $stem = str_replace(['-', '‐', '.', '・'], '', $stem);
            if ($stem === '') {
                continue;
            }
            $forms = [$stem];
            $chars = mb_str_split($stem);
            $first = $chars[0];
            $voiced = [
                'か' => 'が', 'き' => 'ぎ', 'く' => 'ぐ', 'け' => 'げ', 'こ' => 'ご',
                'さ' => 'ざ', 'し' => 'じ', 'す' => 'ず', 'せ' => 'ぜ', 'そ' => 'ぞ',
                'た' => 'だ', 'ち' => 'ぢ', 'つ' => 'づ', 'て' => 'で', 'と' => 'ど',
                'は' => 'ば', 'ひ' => 'び', 'ふ' => 'ぶ', 'へ' => 'べ', 'ほ' => 'ぼ',
            ];
            $half = ['は' => 'ぱ', 'ひ' => 'ぴ', 'ふ' => 'ぷ', 'へ' => 'ぺ', 'ほ' => 'ぽ'];
            if (isset($voiced[$first])) {
                $changed = $chars;
                $changed[0] = $voiced[$first];
                $forms[] = implode('', $changed);
            }
            if (isset($half[$first])) {
                $changed = $chars;
                $changed[0] = $half[$first];
                $forms[] = implode('', $changed);
            }
            foreach ($forms as $form) {
                $readings[] = $form;
                $formChars = mb_str_split($form);
                $last = $formChars[count($formChars) - 1] ?? '';
                if (count($formChars) > 1 && in_array($last, ['く', 'き', 'ち', 'つ'], true)) {
                    $formChars[count($formChars) - 1] = 'っ';
                    $readings[] = implode('', $formChars);
                }
            }
        }

        $readings = array_values(array_unique($readings));
        usort($readings, fn (string $left, string $right): int => mb_strlen($right) <=> mb_strlen($left));

        return $readings;
    }

    /**
     * @param  Collection<string, self>  $masters
     * @return array{char: string, kanji: bool, meaning_en: ?string, meaning_my: ?string}
     */
    private static function pieceFor(string $char, $masters): array
    {
        $known = $masters->get($char);
        if ($known === null) {
            return self::kanaPiece($char);
        }

        return [
            'char' => $char,
            'kanji' => true,
            'meaning_en' => $known->meaning_en,
            'meaning_my' => $known->meaning_my,
        ];
    }

    /**
     * @return array{char: string, kanji: bool, meaning_en: ?string, meaning_my: ?string}
     */
    private static function kanaPiece(string $text): array
    {
        return [
            'char' => $text,
            'kanji' => false,
            'meaning_en' => null,
            'meaning_my' => null,
        ];
    }

    /**
     * @param  array<int, array{char: string, kanji: bool, meaning_en: ?string, meaning_my: ?string}>  $pieces
     * @return array<int, array{char: string, kanji: bool, meaning_en: ?string, meaning_my: ?string}>
     */
    private static function mergeKanaPieces(array $pieces): array
    {
        $merged = [];
        foreach ($pieces as $piece) {
            $previous = $merged[array_key_last($merged)] ?? null;
            if ($previous !== null && ! $previous['kanji'] && ! $piece['kanji']) {
                $merged[array_key_last($merged)]['char'] .= $piece['char'];

                continue;
            }
            $merged[] = $piece;
        }

        return $merged;
    }

    private static function isKanjiCharacter(string $char): bool
    {
        return preg_match('/\p{Han}/u', $char) === 1;
    }

    private static function toHiragana(string $text): string
    {
        return mb_convert_kana($text, 'c');
    }
}
