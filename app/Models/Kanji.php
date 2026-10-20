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
