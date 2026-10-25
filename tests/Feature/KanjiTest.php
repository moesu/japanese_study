<?php

namespace Tests\Feature;

use App\Models\Kanji;
use App\Models\LearnedKotoba;
use App\Models\User;
use Database\Seeders\KanjiSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KanjiTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_lists_n5_kanji_with_myanmar_meanings(): void
    {
        $this->seed(KanjiSeeder::class);

        $this->assertSame(121, Kanji::query()->count());

        $this->get('/')
            ->assertOk()
            ->assertSee('N5')
            ->assertSee('N4')
            ->assertSee('N3')
            ->assertSee('N2')
            ->assertSee('N1')
            ->assertSee('/n3/kotoba', false)
            ->assertDontSee('id="main-menu"', false);

        $this->get('/n4/kotoba')
            ->assertOk()
            ->assertSee('みます')
            ->assertSee('見ます、診ます')
            ->assertSee('ကြည့်သည်')
            ->assertSee('အခန်း ၂၆')
            ->assertSee('Kotoba')
            ->assertSee('စစ်ထုတ်မည်')
            ->assertDontSee('>N5 Kanji<', false)
            ->assertDontSee('わたし');

        $this->get(route('n4.kotoba', 26))
            ->assertOk()
            ->assertSee('みます')
            ->assertDontSee('ミュンヘン');

        $this->get(route('n4.kotoba', 25))->assertNotFound();
        $this->get(route('n4.kotoba', 51))->assertNotFound();

        $this->get(route('n3.kotoba'))
            ->assertOk()
            ->assertSeeInOrder(['さくじつ', '昨日', 'မနေ့က', '一さくじつ', '一昨日', 'တနေ့က', 'さくねん', '昨年', 'မနှစ်က', 'しあさって', 'ဖိန်းနွှဲခါ', 'せんせんしゅう', '先々週', 'လွန်ခဲ့တဲ့အပတ် (၂ ပတ်မြောက်)', 'せんじつ', '先日', 'လွန်ခဲ့တဲ့နေ့'])
            ->assertSee('အခန်း ၁')
            ->assertSee('စစ်ထုတ်မည်')
            ->assertSee('Kotoba')
            ->assertDontSee('>N5 Kanji<', false)
            ->assertDontSee('Hide Kanji');

        $this->get(route('n3.kotoba', ['show' => 'my']))
            ->assertOk()
            ->assertSee('မနေ့က')
            ->assertDontSee('>さくじつ<', false);

        $this->get(route('n3.kotoba', ['kind' => 'verb']))
            ->assertOk()
            ->assertDontSee('さくじつ');

        $this->get(route('n3.kotoba', 1))
            ->assertOk()
            ->assertSee('さくじつ')
            ->assertDontSee('いき');

        $this->get(route('n3.kotoba', ['chapters' => [101]]))
            ->assertOk()
            ->assertSee('အပိုင်း ၂')
            ->assertSeeInOrder(['いき', '息', 'ထွက်သက်ဝင်သက်/အသက်ရှူခြင်း', 'いきをすう', '息を吸う', 'たましい', '魂', 'ဝိညာဉ်', 'いのち', '命', 'せいめい', '生命', 'ほね', '骨', 'かんဆာ', 'がん', 'ကင်ဆာရောဂါ', 'けつあつ', '血圧', 'しんぞう', '心臓', 'နှလုံး'])
            ->assertDontSee('さくじつ');

        $this->get(route('n3.kotoba', ['chapters' => [110]]))
            ->assertOk()
            ->assertSee('အပိုင်း ၂ အခန်း ၁၀')
            ->assertSee('たたく')
            ->assertSee('叩く')
            ->assertSee('ပုတ်သည်/ရိုက်သည်');

        $this->get(route('n3.kotoba', 99))->assertNotFound();

        $this->get(route('n3.kanji'))
            ->assertOk()
            ->assertSeeInOrder(['飛ぶ', 'とぶ', 'ပျံသည်', 'to fly'])
            ->assertSee('Show Kanji')
            ->assertSee('Hide Kanji')
            ->assertSee('Show hiragana')
            ->assertSee('Hide hiragana')
            ->assertSee('အခန်း ၁')
            ->assertDontSee('飛・機・空・港・預・込・荷・検・査')
            ->assertSee('Kanji')
            ->assertSee('Kotoba')
            ->assertSee('စစ်ထုတ်မည်')
            ->assertDontSee('>N5 Kanji<', false);

        $this->get(route('n3.kanji', ['show' => 'my']))
            ->assertOk()
            ->assertSee('ပျံသည်')
            ->assertDontSee('>とぶ<', false)
            ->assertDontSee('>飛ぶ<', false);

        $this->get(route('n3.kanji', ['chapters' => [1], 'kind' => 'verb']))
            ->assertOk()
            ->assertSee('飛ぶ')
            ->assertDontSee('飛行機');

        $this->get(route('n3.kanji', 1))
            ->assertOk()
            ->assertSee('飛ぶ')
            ->assertDontSee('満席');

        $this->get(route('n3.kanji', 99))->assertNotFound();

        $this->get(route('n3.kanji', ['chapters' => [1], 'glyph' => 'hide']))
            ->assertOk()
            ->assertSee('<div class="word hiragana">とぶ</div>', false)
            ->assertDontSee('<div class="word hiragana">飛ぶ</div>', false)
            ->assertDontSee('>飛ぶ<', false);

        $this->get(route('n3.kanji', ['chapters' => [1], 'kana' => 'hide']))
            ->assertOk()
            ->assertSee('<div class="word">飛</div>', false)
            ->assertDontSee('<div class="word hiragana">飛ぶ</div>', false)
            ->assertDontSee('>とぶ<', false);

        $this->get(route('n3.kanji', ['chapters' => [1], 'glyph' => 'hide', 'kana' => 'hide']))
            ->assertOk()
            ->assertSee('ပျံသည်')
            ->assertDontSee('<div class="word', false);

        $this->get(route('kanji.index'))
            ->assertOk()
            ->assertSeeInOrder(['一', '二', '三', '四', '五', '六', '七', '八', '九', '十', '百', '千', '万', '円', '金', '日', '月', '火', '水', '木', '土', '山', '川', '竹', '目', '耳', '口', '手', '足', '体', '人', '上', '下', '左', '右', '内', '外', '中', '学', '校', '先', '生', '名', '字', '本', '大', '小', '高', '友', '入', '出', '門', '父', '母', '子', '男', '女', '犬', '鳥', '立', '見', '聞', '行', '来', '帰', '米', '茶', '牛', '肉', '魚', '貝', '好', '物'])
            ->assertSee('日')
            ->assertSee('နေ့')
            ->assertSee('駅')
            ->assertSee('ဘူတာ')
            ->assertDontSee('JLPT N5 Kanji')
            ->assertSee('N5 Kanji')
            ->assertSee('Kotoba')
            ->assertDontSee('အခန်း ရွေးပါ')
            ->assertDontSee('>言葉<', false);
    }

    public function test_goi_chapter_lists_lesson_meanings(): void
    {
        $this->get('/n5/kotoba')->assertOk();
        $this->get('/gio_n5')->assertRedirect('/n5/kotoba');
        $this->get('/goi')->assertRedirect('/n5/kotoba');

        $this->get(route('goi.show'))
            ->assertOk()
            ->assertSee('အပေါ်သို့')
            ->assertSee('စစ်ထုတ်မည်')
            ->assertSee('အခန်းအားလုံး')
            ->assertSee('わたし')
            ->assertSee('これ')
            ->assertDontSee('>ベトナム<', false)
            ->assertSee('နောက်');

        $this->get(route('goi.show', 1))
            ->assertOk()
            ->assertSee('わたし')
            ->assertSee('ကျွန်တော်၊ကျွန်မ')
            ->assertSee('အခန်း ၂')
            ->assertSee('はじめまして');

        $this->get(route('goi.show', 2))
            ->assertOk()
            ->assertSee('これ')
            ->assertSee('ဒီဟာ');

        $this->get(route('goi.show', ['chapter' => 1, 'show' => 'ja']))
            ->assertOk()
            ->assertSee('わたし')
            ->assertDontSee('ကျွန်တော်၊ကျွန်မ');

        $this->get(route('goi.show', ['chapter' => 1, 'show' => 'my']))
            ->assertOk()
            ->assertSee('ကျွန်တော်၊ကျွန်မ')
            ->assertDontSee('>わたし<', false);

        $this->get(route('goi.show', ['kind' => 'i-adj']))
            ->assertOk()
            ->assertSee('おおきい')
            ->assertSee('あたらしい')
            ->assertSee('အခန်း ၈')
            ->assertDontSee('たべます');

        $this->get(route('goi.show', ['kind' => 'na-adj']))
            ->assertOk()
            ->assertSee('しずか')
            ->assertDontSee('おおきい');

        $this->get(route('goi.show', ['kind' => 'adverb']))
            ->assertOk()
            ->assertSee('とても');

        $this->get(route('goi.show', ['kind' => 'verb']))
            ->assertOk()
            ->assertSee('たべます')
            ->assertSee('အခန်း ၆')
            ->assertDontSee('>わたし<', false);

        $this->get(route('goi.show', ['chapter' => 1, 'kind' => 'noun']))
            ->assertOk()
            ->assertSee('せんせい')
            ->assertDontSee('たべます');

        $this->get(route('goi.show', ['chapters' => [1, 2]]))
            ->assertOk()
            ->assertSee('わたし')
            ->assertSee('これ')
            ->assertSee('အခန်း ၁')
            ->assertDontSee('たべます');

        $this->get(route('goi.show', ['chapters' => [1, 2], 'page' => 2]))
            ->assertOk()
            ->assertSee('その～')
            ->assertSee('အခန်း ၂');

        $this->get(route('goi.show', ['chapters' => [6, 8], 'kind' => 'verb']))
            ->assertOk()
            ->assertSee('たべます')
            ->assertDontSee('おおきい');

        $this->withCredentials()
            ->withUnencryptedCookie('n5_device', '11111111-1111-4111-8111-111111111111')
            ->postJson(route('kotoba.learned'), [
                'word' => '私',
                'reading' => 'わたし',
            ])->assertOk()->assertJson(['learned' => true]);

        $this->withUnencryptedCookie('n5_device', '11111111-1111-4111-8111-111111111111')
            ->get(route('goi.show', ['chapter' => 1, 'learned' => 'yes']))
            ->assertOk()
            ->assertSee('ကျက်ပြီးပါပြီ')
            ->assertSee('わたし')
            ->assertDontSee('>あなた<', false);

        $this->withUnencryptedCookie('n5_device', '22222222-2222-4222-8222-222222222222')
            ->get(route('goi.show', ['chapter' => 1, 'learned' => 'yes']))
            ->assertOk()
            ->assertDontSee('>わたし<', false);

        $this->withUnencryptedCookie('n5_device', '11111111-1111-4111-8111-111111111111')
            ->get(route('goi.show', ['chapter' => 1, 'learned' => 'no']))
            ->assertOk()
            ->assertSee('မကျက်ရသေးပါ')
            ->assertSee('あなた')
            ->assertDontSee('>わたし<', false);

        $learner = User::query()->where('device', '11111111-1111-4111-8111-111111111111')->first();
        $this->assertNotNull($learner);
        $this->assertTrue(
            LearnedKotoba::query()
                ->where('user_id', $learner->id)
                ->where('word', '私')
                ->where('reading', 'わたし')
                ->where('learned', true)
                ->exists(),
        );

        $this->get(route('goi.show', ['chapters' => [99]]))->assertNotFound();

        $this->get(route('goi.show', 26))->assertNotFound();
    }

    public function test_learned_mark_works_without_a_device_cookie(): void
    {
        $response = $this->postJson(route('kotoba.learned'), [
            'word' => '私',
            'reading' => 'わたし',
        ]);

        $response->assertOk()
            ->assertJson(['learned' => true])
            ->assertCookie('n5_device');

        $device = $response->getCookie('n5_device', false);
        $this->assertNotNull($device);

        $this->withCredentials()
            ->withUnencryptedCookie('n5_device', $device->getValue())
            ->get(route('goi.show', ['chapter' => 1, 'learned' => 'yes']))
            ->assertOk()
            ->assertSee('わたし');
    }

    public function test_search_finds_kanji_by_myanmar_meaning(): void
    {
        $this->seed(KanjiSeeder::class);

        $this->get(route('kanji.index', ['q' => 'ကျောင်း']))
            ->assertOk()
            ->assertSee('校')
            ->assertDontSee('雨');
    }

    public function test_search_finds_kanji_by_japanese_reading_ordered_by_sort_order(): void
    {
        $this->seed(KanjiSeeder::class);

        $this->get(route('kanji.index', ['q' => 'ひと']))
            ->assertOk()
            ->assertSee('人')
            ->assertDontSee('雨');

        $this->get(route('kanji.index', ['q' => 'すこし']))
            ->assertOk()
            ->assertSee('少');

        $this->get(route('kanji.index', ['q' => 'じぜん']))
            ->assertOk()
            ->assertSee('前');

        $this->get(route('kanji.index', ['q' => 'ばしゃ']))
            ->assertOk()
            ->assertSee('車');

        $this->get(route('kanji.index', ['q' => 'School']))
            ->assertOk()
            ->assertSeeInOrder(['学', '校']);
    }

    public function test_kanji_detail_lives_under_n5_path_and_old_url_redirects(): void
    {
        $this->seed(KanjiSeeder::class);
        $kanji = Kanji::query()->where('character', '日')->firstOrFail();

        $this->get('/n5/kanji/'.$kanji->id)
            ->assertOk()
            ->assertSee('နေ့');

        $this->get('/kanji/'.$kanji->id)
            ->assertRedirect('/n5/kanji/'.$kanji->id);

        $this->get(route('kanji.index'))
            ->assertOk()
            ->assertSee('href="/n5/kanji/'.$kanji->id.'"', false);
    }

    public function test_number_kanji_show_textbook_words_with_myanmar_meanings(): void
    {
        $this->seed(KanjiSeeder::class);

        $this->assertSame(
            [
                '一', '二', '三', '四', '五', '六', '七', '八', '九', '十',
                '百', '千', '万', '円', '金',
                '日', '月', '火', '水', '木', '土', '山', '川', '田', '石',
                '天', '空', '雨', '花', '竹',
                '目', '耳', '口', '手', '足', '体',
                '人',
                '上', '下', '左', '右', '内', '外', '中',
                '学', '校', '先', '生', '名', '字', '本',
                '大', '小', '高', '友', '入', '出', '門',
                '父', '母', '子', '男', '女', '犬', '鳥',
                '立', '見', '聞', '行', '来', '帰',
                '米', '茶', '牛', '肉', '魚', '貝', '好', '物',
            ],
            Kanji::query()->orderBy('sort_order')->limit(79)->pluck('character')->all(),
        );

        $one = Kanji::query()->where('character', '一')->firstOrFail();
        $this->get(route('kanji.show', $one))
            ->assertOk()
            ->assertSee('တစ်')
            ->assertSeeInOrder(['ひとつ', '၁ ခု'])
            ->assertSeeInOrder(['ひとり', '၁ ယောက်'])
            ->assertSeeInOrder(['いちがつ', 'ဇန်နဝါရီလ'])
            ->assertSeeInOrder(['いちにち', '၁ ရက်'])
            ->assertSeeInOrder(['いちえん', '၁ ယန်း']);

        $two = Kanji::query()->where('character', '二')->firstOrFail();
        $this->get(route('kanji.show', $two))
            ->assertOk()
            ->assertSee('နှစ်')
            ->assertSeeInOrder(['ふたつ', '၂ ခု'])
            ->assertSeeInOrder(['ふたり', '၂ ယောက်'])
            ->assertSeeInOrder(['にがつ', 'ဖေဖော်ဝါရီလ'])
            ->assertSeeInOrder(['にひゃくえん', '၂၀၀ ယန်း']);

        $yen = Kanji::query()->where('character', '円')->firstOrFail();
        $this->get(route('kanji.show', $yen))
            ->assertOk()
            ->assertSeeInOrder(['えん', 'ယန်း'])
            ->assertSeeInOrder(['ひゃくえん', '၁၀၀ ယန်း'])
            ->assertSeeInOrder(['せんえん', '၁၀၀၀ ယန်း'])
            ->assertSeeInOrder(['いちまんえん', '၁ သောင်းယန်း']);

        $hundred = Kanji::query()->where('character', '百')->firstOrFail();
        $this->get(route('kanji.show', $hundred))
            ->assertOk()
            ->assertSeeInOrder(['ひゃくえん', '၁၀၀ ယန်း']);

        $thousand = Kanji::query()->where('character', '千')->firstOrFail();
        $this->get(route('kanji.show', $thousand))
            ->assertOk()
            ->assertSeeInOrder(['せんえん', '၁၀၀၀ ယန်း']);

        $man = Kanji::query()->where('character', '万')->firstOrFail();
        $this->get(route('kanji.show', $man))
            ->assertOk()
            ->assertSeeInOrder(['いちまんえん', '၁ သောင်းယန်း']);

        $moon = Kanji::query()->where('character', '月')->firstOrFail();
        $this->get(route('kanji.show', $moon))
            ->assertOk()
            ->assertSee('လ')
            ->assertSeeInOrder(['つき', 'လ'])
            ->assertSeeInOrder(['げつようび', 'တနင်္လာနေ့'])
            ->assertSeeInOrder(['こんげつ', 'ဒီလ'])
            ->assertSeeInOrder(['くがつ', 'စက်တင်ဘာလ'])
            ->assertSeeInOrder(['いっかげつ', '၁ လတာ'])
            ->assertSeeInOrder(['じゅういちがつ', 'နိုဝင်ဘာလ']);

        $fire = Kanji::query()->where('character', '火')->firstOrFail();
        $this->get(route('kanji.show', $fire))
            ->assertOk()
            ->assertSee('မီး')
            ->assertSeeInOrder(['ひ', 'မီး'])
            ->assertSeeInOrder(['たばこのひ', 'စီးကရက်မီး'])
            ->assertSeeInOrder(['かようび', 'အင်္ဂါနေ့']);

        $water = Kanji::query()->where('character', '水')->firstOrFail();
        $this->get(route('kanji.show', $water))
            ->assertOk()
            ->assertSee('ရေ')
            ->assertSeeInOrder(['みず', 'ရေ'])
            ->assertSeeInOrder(['すいようび', 'ဗုဒ္ဓဟူးနေ့'])
            ->assertSeeInOrder(['すいえい', 'ရေကူးခြင်း'])
            ->assertSeeInOrder(['こうすい', 'ရေမွှေး']);

        $tree = Kanji::query()->where('character', '木')->firstOrFail();
        $this->get(route('kanji.show', $tree))
            ->assertOk()
            ->assertSee('သစ်ပင်')
            ->assertSeeInOrder(['き', 'သစ်ပင်'])
            ->assertSeeInOrder(['さくらのき', 'ချယ်ရီပင်'])
            ->assertSeeInOrder(['たいぼく', 'သစ်ပင်ကြီး'])
            ->assertSeeInOrder(['もくようび', 'ကြာသပတေးနေ့']);

        $gold = Kanji::query()->where('character', '金')->firstOrFail();
        $this->get(route('kanji.show', $gold))
            ->assertOk()
            ->assertSeeInOrder(['おかね', 'ပိုက်ဆံ'])
            ->assertSeeInOrder(['きんようび', 'သောကြာနေ့'])
            ->assertSeeInOrder(['げんきん', 'ငွေသား'])
            ->assertSeeInOrder(['りょうきん', 'အခကြေးငွေ']);

        $earth = Kanji::query()->where('character', '土')->firstOrFail();
        $this->get(route('kanji.show', $earth))
            ->assertOk()
            ->assertSeeInOrder(['つち', 'မြေကြီး'])
            ->assertSeeInOrder(['どようび', 'စနေနေ့']);

        $day = Kanji::query()->where('character', '日')->firstOrFail();
        $this->get(route('kanji.show', $day))
            ->assertOk()
            ->assertSeeInOrder(['にちようび', 'တနင်္ဂနွေနေ့'])
            ->assertSeeInOrder(['ちちのひ', 'အဖေနေ့'])
            ->assertSeeInOrder(['たんじょうび', 'မွေးနေ့'])
            ->assertSeeInOrder(['せいねんがっぴ', 'မွေးသက္ကရာဇ်'])
            ->assertSeeInOrder(['むいか', '၆ ရက်နေ့'])
            ->assertSeeInOrder(['きゅうじつ', 'အားလပ်ရက်']);

        $year = Kanji::query()->where('character', '年')->firstOrFail();
        $this->get(route('kanji.show', $year))
            ->assertOk()
            ->assertSeeInOrder(['とし', 'နှစ်'])
            ->assertSeeInOrder(['まいねん', 'နှစ်စဉ်'])
            ->assertSeeInOrder(['にねんまえ', '၂ နှစ်က'])
            ->assertSeeInOrder(['きょねん', 'မနှစ်က']);

        $eye = Kanji::query()->where('character', '目')->firstOrFail();
        $this->get(route('kanji.show', $eye))
            ->assertOk()
            ->assertSeeInOrder(['めざましどけい', 'နှိုးစက်နာရီ'])
            ->assertSeeInOrder(['めぐすり', 'မျက်စဉ်းဆေး'])
            ->assertSeeInOrder(['もくてき', 'ရည်ရွယ်ချက်']);

        $ear = Kanji::query()->where('character', '耳')->firstOrFail();
        $this->get(route('kanji.show', $ear))
            ->assertOk()
            ->assertSeeInOrder(['みみ', 'နား'])
            ->assertSeeInOrder(['じびか', 'နားနှာခေါင်းလည်ချောင်းဌာန']);

        $hand = Kanji::query()->where('character', '手')->firstOrFail();
        $this->get(route('kanji.show', $hand))
            ->assertOk()
            ->assertSeeInOrder(['てがみ', 'စာ'])
            ->assertSeeInOrder(['きって', 'စာတံဆိပ်'])
            ->assertSeeInOrder(['あくしゅ', 'လက်ဆွဲနှုတ်ဆက်ခြင်း'])
            ->assertSeeInOrder(['うんてんしゅ', 'ယာဉ်မောင်း']);

        $foot = Kanji::query()->where('character', '足')->firstOrFail();
        $this->get(route('kanji.show', $foot))
            ->assertOk()
            ->assertSeeInOrder(['たす', 'ပေါင်းထည့်သည်'])
            ->assertSeeInOrder(['いっそく', '၁ စုံ'])
            ->assertSeeInOrder(['さんぞく', '၃ စုံ']);

        $power = Kanji::query()->where('character', '力')->firstOrFail();
        $this->get(route('kanji.show', $power))
            ->assertOk()
            ->assertSeeInOrder(['ちから', 'အား'])
            ->assertSeeInOrder(['かりょく', 'မီးအား'])
            ->assertSeeInOrder(['きょうりょく', 'ပူးပေါင်းဆောင်ရွက်ခြင်း'])
            ->assertSeeInOrder(['しりょく', 'အမြင်အာရုံ'])
            ->assertSeeInOrder(['がくりょく', 'ပညာရေးစွမ်းရည်']);

        $person = Kanji::query()->where('character', '人')->firstOrFail();
        $this->get(route('kanji.show', $person))
            ->assertOk()
            ->assertSee('あめりかじん')
            ->assertSee('အမေရိကန်လူမျိုး')
            ->assertSee('さんにん');

        $mouth = Kanji::query()->where('character', '口')->firstOrFail();
        $this->get(route('kanji.show', $mouth))
            ->assertOk()
            ->assertSeeInOrder(['くちべに', 'နှုတ်ခမ်းနီ'])
            ->assertSeeInOrder(['かいさつぐち', 'လက်မှတ်ဂိတ်'])
            ->assertSeeInOrder(['じんこう', 'လူဦးရေ']);

        $field = Kanji::query()->where('character', '田')->firstOrFail();
        $this->get(route('kanji.show', $field))
            ->assertOk()
            ->assertSeeInOrder(['たんぼ', 'လယ်ကွင်း'])
            ->assertSeeInOrder(['すいでん', 'ရေသွင်းလယ်']);

        $stone = Kanji::query()->where('character', '石')->firstOrFail();
        $this->get(route('kanji.show', $stone))
            ->assertOk()
            ->assertSeeInOrder(['こいし', 'ကျောက်စရစ်'])
            ->assertSeeInOrder(['ほうせき', 'ရတနာကျောက်']);

        $mountain = Kanji::query()->where('character', '山')->firstOrFail();
        $this->get(route('kanji.show', $mountain))
            ->assertOk()
            ->assertSeeInOrder(['ふじさん', 'ဖူဂျီတောင်'])
            ->assertSeeInOrder(['かざん', 'မီးတောင်']);

        $river = Kanji::query()->where('character', '川')->firstOrFail();
        $this->get(route('kanji.show', $river))
            ->assertOk()
            ->assertSeeInOrder(['ナイルがわ', 'နိုင်လ်မြစ်'])
            ->assertSeeInOrder(['アマゾンがわ', 'အမေဇုံမြစ်']);

        $rain = Kanji::query()->where('character', '雨')->firstOrFail();
        $this->get(route('kanji.show', $rain))
            ->assertOk()
            ->assertSeeInOrder(['あめ', 'မိုး'])
            ->assertSeeInOrder(['おおあめ', 'မိုးသည်း'])
            ->assertSeeInOrder(['うてん', 'မိုးရွာသောရာသီဥတု']);

        $flower = Kanji::query()->where('character', '花')->firstOrFail();
        $this->get(route('kanji.show', $flower))
            ->assertOk()
            ->assertSeeInOrder(['かびん', 'ပန်းအိုး'])
            ->assertSeeInOrder(['はなみ', 'ပန်းကြည့်ခြင်း'])
            ->assertSeeInOrder(['はなび', 'မီးပန်း'])
            ->assertSeeInOrder(['はなたば', 'ပန်းစည်း']);

        $bamboo = Kanji::query()->where('character', '竹')->firstOrFail();
        $this->get(route('kanji.show', $bamboo))
            ->assertOk()
            ->assertSeeInOrder(['たけのこ', 'ဝါးမျှစ်'])
            ->assertSeeInOrder(['ちくりん', 'ဝါးတော']);

        $middle = Kanji::query()->where('character', '中')->firstOrFail();
        $this->get(route('kanji.show', $middle))
            ->assertOk()
            ->assertSeeInOrder(['なか', 'အတွင်း'])
            ->assertSeeInOrder(['すいちゅう', 'ရေအောက်'])
            ->assertSeeInOrder(['しごとちゅう', 'အလုပ်လုပ်နေစဉ်'])
            ->assertSeeInOrder(['じゅぎょうちゅう', 'စာသင်နေစဉ်'])
            ->assertSeeInOrder(['かいぎちゅう', 'အစည်းအဝေးနေစဉ်'])
            ->assertSeeInOrder(['いちにちじゅう', 'တစ်နေ့လုံး']);

        $outside = Kanji::query()->where('character', '外')->firstOrFail();
        $this->get(route('kanji.show', $outside))
            ->assertOk()
            ->assertSeeInOrder(['そと', 'အပြင်'])
            ->assertSeeInOrder(['そとがわ', 'အပြင်ဘက်'])
            ->assertSeeInOrder(['がいしゅつ', 'အပြင်ထွက်ခြင်း'])
            ->assertSeeInOrder(['がいこく', 'နိုင်ငံခြား']);

        $inside = Kanji::query()->where('character', '内')->firstOrFail();
        $this->get(route('kanji.show', $inside))
            ->assertOk()
            ->assertSeeInOrder(['うちがわ', 'အတွင်းဘက်'])
            ->assertSeeInOrder(['こうない', 'ကျောင်းဝင်းအတွင်း'])
            ->assertSeeInOrder(['こくない', 'ပြည်တွင်း'])
            ->assertSeeInOrder(['ないか', 'အတွင်းရောဂါဌာန']);

        $left = Kanji::query()->where('character', '左')->firstOrFail();
        $this->get(route('kanji.show', $left))
            ->assertOk()
            ->assertSeeInOrder(['ひだり', 'ဘယ်'])
            ->assertSeeInOrder(['ひだりあし', 'ဘယ်ခြေ'])
            ->assertSeeInOrder(['させつ', 'ဘယ်ကွေ့'])
            ->assertSeeInOrder(['さゆう', 'ဘယ်ညာ']);

        $right = Kanji::query()->where('character', '右')->firstOrFail();
        $this->get(route('kanji.show', $right))
            ->assertOk()
            ->assertSeeInOrder(['みぎ', 'ညာ'])
            ->assertSeeInOrder(['みぎて', 'ညာလက်'])
            ->assertSeeInOrder(['みぎがわ', 'ညာဘက်'])
            ->assertSeeInOrder(['うせつ', 'ညာကွေ့'])
            ->assertSeeInOrder(['つくえのみぎ', 'စားပွဲညာဘက်']);

        $up = Kanji::query()->where('character', '上')->firstOrFail();
        $this->get(route('kanji.show', $up))
            ->assertOk()
            ->assertSeeInOrder(['うえ', 'အပေါ်'])
            ->assertSeeInOrder(['あがる', 'တက်သည်'])
            ->assertSeeInOrder(['としうえ', 'အသက်ကြီးသူ'])
            ->assertSeeInOrder(['じょうげ', 'အပေါ်အောက်'])
            ->assertSeeInOrder(['うわぎ', 'အပေါ်အင်္ကျီ'])
            ->assertSeeInOrder(['すいじょう', 'ရေပေါ်']);

        $down = Kanji::query()->where('character', '下')->firstOrFail();
        $this->get(route('kanji.show', $down))
            ->assertOk()
            ->assertSeeInOrder(['した', 'အောက်'])
            ->assertSeeInOrder(['したぎ', 'အတွင်းခံ'])
            ->assertSeeInOrder(['さがる', 'ဆင်းသည် · ကျသည်'])
            ->assertSeeInOrder(['くだり', 'ဆင်းလမ်း']);

        $ahead = Kanji::query()->where('character', '先')->firstOrFail();
        $this->get(route('kanji.show', $ahead))
            ->assertOk()
            ->assertSeeInOrder(['さき', 'ရှေ့ · အရင်'])
            ->assertSeeInOrder(['せんせい', 'ဆရာ'])
            ->assertSeeInOrder(['せんげつ', 'ပြီးခဲ့သောလ'])
            ->assertSeeInOrder(['せんしゅう', 'ပြီးခဲ့သောအပတ်'])
            ->assertSeeInOrder(['せんじつ', 'ပြီးခဲ့သောရက်က']);

        $life = Kanji::query()->where('character', '生')->firstOrFail();
        $this->get(route('kanji.show', $life))
            ->assertOk()
            ->assertSeeInOrder(['がくせい', 'ကျောင်းသား'])
            ->assertSeeInOrder(['いきる', 'အသက်ရှင်သည်'])
            ->assertSeeInOrder(['うまれる', 'မွေးသည်'])
            ->assertSeeInOrder(['せいかつ', 'နေထိုင်မှုဘဝ'])
            ->assertSeeInOrder(['じんせい', 'လူ့ဘဝ'])
            ->assertSeeInOrder(['いっしょう', 'တစ်သက်လုံး'])
            ->assertSeeInOrder(['たんじょうび', 'မွေးနေ့'])
            ->assertSeeInOrder(['なまビール', 'လတ်ဆတ်ဘီယာ']);

        $study = Kanji::query()->where('character', '学')->firstOrFail();
        $this->get(route('kanji.show', $study))
            ->assertOk()
            ->assertSeeInOrder(['まなぶ', 'လေ့လာသင်ယူသည်'])
            ->assertSeeInOrder(['がっこう', 'ကျောင်း'])
            ->assertSeeInOrder(['だいがく', 'တက္ကသိုလ်'])
            ->assertSeeInOrder(['にゅうがく', 'ကျောင်းဝင်သည်'])
            ->assertSeeInOrder(['にゅうがくしき', 'ကျောင်းဝင်ပွဲ'])
            ->assertSeeInOrder(['けんがく', 'လေ့လာရေးခရီး'])
            ->assertSeeInOrder(['しんがく', 'ပညာဆက်သင်သည်']);

        $school = Kanji::query()->where('character', '校')->firstOrFail();
        $this->get(route('kanji.show', $school))
            ->assertOk()
            ->assertSeeInOrder(['がっこう', 'ကျောင်း'])
            ->assertSeeInOrder(['こうちょう', 'ကျောင်းအုပ်'])
            ->assertSeeInOrder(['きゅうこう', 'ကျောင်းပိတ်ခြင်း'])
            ->assertSeeInOrder(['てんこう', 'ကျောင်းပြောင်းခြင်း']);

        $book = Kanji::query()->where('character', '本')->firstOrFail();
        $this->get(route('kanji.show', $book))
            ->assertOk()
            ->assertSeeInOrder(['ほん', 'စာအုပ်'])
            ->assertSeeInOrder(['にほん', 'ဂျပန်'])
            ->assertSeeInOrder(['にほんじん', 'ဂျပန်လူမျိုး'])
            ->assertSeeInOrder(['いっぽん', '၁ ချောင်း'])
            ->assertSeeInOrder(['さんぼん', '၃ ချောင်း'])
            ->assertSeeInOrder(['ごほん', '၅ ချောင်း']);

        $body = Kanji::query()->where('character', '体')->firstOrFail();
        $this->get(route('kanji.show', $body))
            ->assertOk()
            ->assertSeeInOrder(['からだ', 'ခန္ဓာကိုယ်'])
            ->assertSeeInOrder(['たいいく', 'ကာယပညာ'])
            ->assertSeeInOrder(['たいいくかん', 'အားကစားရုံ'])
            ->assertSeeInOrder(['たいりょく', 'ကိုယ်ခန္ဓာအား'])
            ->assertSeeInOrder(['たいじゅう', 'ကိုယ်အလေးချိန်']);

        $name = Kanji::query()->where('character', '名')->firstOrFail();
        $this->get(route('kanji.show', $name))
            ->assertOk()
            ->assertSeeInOrder(['なまえ', 'နာမည်'])
            ->assertSeeInOrder(['みょうじ', 'မျိုးရိုးအမည်'])
            ->assertSeeInOrder(['ゆうめい', 'နာမည်ကြီး'])
            ->assertSeeInOrder(['めいし', 'လိပ်စာကတ်'])
            ->assertSeeInOrder(['ちめい', 'ဒေသအမည်']);

        $letter = Kanji::query()->where('character', '字')->firstOrFail();
        $this->get(route('kanji.show', $letter))
            ->assertOk()
            ->assertSeeInOrder(['じ', 'စာလုံး'])
            ->assertSeeInOrder(['かんじ', 'ခန်ဂျီ'])
            ->assertSeeInOrder(['もじ', 'စာလုံး'])
            ->assertSeeInOrder(['しゅうじ', 'လက်ရေးလှ']);

        $child = Kanji::query()->where('character', '子')->firstOrFail();
        $this->get(route('kanji.show', $child))
            ->assertOk()
            ->assertSeeInOrder(['こども', 'ကလေး'])
            ->assertSeeInOrder(['おこさん', 'ကလေး (ယဉ်ကျေး)'])
            ->assertSeeInOrder(['ぼしてちょう', 'မိခင်နှင့်ကလေးကျန်းမာရေးမှတ်တမ်း']);

        $man = Kanji::query()->where('character', '男')->firstOrFail();
        $this->get(route('kanji.show', $man))
            ->assertOk()
            ->assertSeeInOrder(['おとこ', 'အမျိုးသား'])
            ->assertSeeInOrder(['おとこのこ', 'ယောက်ျားလေး'])
            ->assertSeeInOrder(['おとこのひと', 'အမျိုးသား'])
            ->assertSeeInOrder(['だんしがくせい', 'ကျောင်းသား'])
            ->assertSeeInOrder(['だんせい', 'အမျိုးသား']);

        $woman = Kanji::query()->where('character', '女')->firstOrFail();
        $this->get(route('kanji.show', $woman))
            ->assertOk()
            ->assertSeeInOrder(['おんな', 'မိန်းမ'])
            ->assertSeeInOrder(['おんなのこ', 'မိန်းကလေး'])
            ->assertSeeInOrder(['おんなのひと', 'အမျိုးသမီး'])
            ->assertSeeInOrder(['じょし', 'အမျိုးသမီး'])
            ->assertSeeInOrder(['じょせい', 'အမျိုးသမီး'])
            ->assertSeeInOrder(['だんじょ', 'ကျားမ'])
            ->assertSeeInOrder(['かのじょ', 'သူမ · ရည်းစား'])
            ->assertSeeInOrder(['じょゆう', 'မင်းသမီး']);

        $father = Kanji::query()->where('character', '父')->firstOrFail();
        $this->get(route('kanji.show', $father))
            ->assertOk()
            ->assertSeeInOrder(['ちち', 'အဖေ'])
            ->assertSeeInOrder(['ちちのひ', 'အဖေနေ့'])
            ->assertSeeInOrder(['そふ', 'အဘိုး'])
            ->assertSeeInOrder(['ふぼ', 'မိဘ']);

        $mother = Kanji::query()->where('character', '母')->firstOrFail();
        $this->get(route('kanji.show', $mother))
            ->assertOk()
            ->assertSeeInOrder(['はは', 'အမေ'])
            ->assertSeeInOrder(['ははのひ', 'အမေနေ့'])
            ->assertSeeInOrder(['そぼ', 'အဘွား'])
            ->assertSeeInOrder(['ぼご', 'မိခင်ဘာသာစကား'])
            ->assertSeeInOrder(['ぼこう', 'မိခင်ကျောင်း']);

        $dog = Kanji::query()->where('character', '犬')->firstOrFail();
        $this->get(route('kanji.show', $dog))
            ->assertOk()
            ->assertSeeInOrder(['いぬ', 'ခွေး'])
            ->assertSeeInOrder(['こいぬ', 'ခွေးကလေး'])
            ->assertSeeInOrder(['こがたけん', 'ခွေးမျိုးအသေး'])
            ->assertSeeInOrder(['もうどうけん', 'မျက်မမြင်လမ်းပြခွေး']);

        $bird = Kanji::query()->where('character', '鳥')->firstOrFail();
        $this->get(route('kanji.show', $bird))
            ->assertOk()
            ->assertSeeInOrder(['とり', 'ငှက်'])
            ->assertSeeInOrder(['ことり', 'ငှက်ကလေး'])
            ->assertSeeInOrder(['はくちょう', 'ငန်း']);

        $stand = Kanji::query()->where('character', '立')->firstOrFail();
        $this->get(route('kanji.show', $stand))
            ->assertOk()
            ->assertSeeInOrder(['たつ', 'ရပ်သည်'])
            ->assertSeeInOrder(['こくりつだいがく', 'နိုင်ငံတော်တက္ကသိုလ်']);

        $rest = Kanji::query()->where('character', '休')->firstOrFail();
        $this->get(route('kanji.show', $rest))
            ->assertOk()
            ->assertSeeInOrder(['やすむ', 'နားသည်'])
            ->assertSeeInOrder(['やすみ', 'အားလပ်ရက်'])
            ->assertSeeInOrder(['なつやすみ', 'နွေရာသီအားလပ်ရက်'])
            ->assertSeeInOrder(['なつ', '休'])
            ->assertSeeInOrder(['きゅうじつ', 'ပိတ်ရက်'])
            ->assertSeeInOrder(['しゅうきゅうふつか', 'တစ်ပတ် ၂ ရက်နား']);

        $go = Kanji::query()->where('character', '行')->firstOrFail();
        $this->get(route('kanji.show', $go))
            ->assertOk()
            ->assertSeeInOrder(['いく', 'သွားသည်'])
            ->assertSeeInOrder(['おおさか', 'အိုဆာကာ'])
            ->assertSeeInOrder(['ぎょうれつ', 'တန်းစီ'])
            ->assertSeeInOrder(['おこなう', 'ပြုလုပ်သည်'])
            ->assertSeeInOrder(['ぎんこう', 'ဘဏ်'])
            ->assertSeeInOrder(['ぎん', '行'])
            ->assertSeeInOrder(['りょこう', 'ခရီး']);

        $come = Kanji::query()->where('character', '来')->firstOrFail();
        $this->get(route('kanji.show', $come))
            ->assertOk()
            ->assertSeeInOrder(['くる', 'လာသည်'])
            ->assertSeeInOrder(['こない', 'မလာ'])
            ->assertSeeInOrder(['らいねん', 'နောက်နှစ်'])
            ->assertSeeInOrder(['らいにち', 'ဂျပန်ရောက်လာခြင်း']);

        $return = Kanji::query()->where('character', '帰')->firstOrFail();
        $this->get(route('kanji.show', $return))
            ->assertOk()
            ->assertSeeInOrder(['かえる', 'ပြန်သည်'])
            ->assertSeeInOrder(['かえり', 'အပြန်'])
            ->assertSeeInOrder(['きこく', 'နိုင်ငံပြန်ခြင်း'])
            ->assertSeeInOrder(['きたく', 'အိမ်ပြန်ခြင်း']);

        $see = Kanji::query()->where('character', '見')->firstOrFail();
        $this->get(route('kanji.show', $see))
            ->assertOk()
            ->assertSeeInOrder(['みる', 'ကြည့်သည်'])
            ->assertSeeInOrder(['みせる', 'ပြသည်'])
            ->assertSeeInOrder(['はなみ', 'ပန်းကြည့်ခြင်း'])
            ->assertSeeInOrder(['つきみ', 'လကြည့်ခြင်း'])
            ->assertSeeInOrder(['けんがく', 'လေ့လာရေးခရီး'])
            ->assertSeeInOrder(['いけん', 'ထင်မြင်ချက်']);

        $hear = Kanji::query()->where('character', '聞')->firstOrFail();
        $this->get(route('kanji.show', $hear))
            ->assertOk()
            ->assertSeeInOrder(['きく', 'ကြားသည် · မေးသည်'])
            ->assertSeeInOrder(['しんぶん', 'သတင်းစာ'])
            ->assertSeeInOrder(['にほんご', 'ဂျပန်စာ']);

        $big = Kanji::query()->where('character', '大')->firstOrFail();
        $this->get(route('kanji.show', $big))
            ->assertOk()
            ->assertSeeInOrder(['おおきい', 'ကြီးသည်'])
            ->assertSeeInOrder(['だいがく', 'တက္ကသိုလ်'])
            ->assertSeeInOrder(['だいすき', 'အရမ်းကြိုက်သည်'])
            ->assertSeeInOrder(['だいきらい', 'အရမ်းမုန်းသည်'])
            ->assertSeeInOrder(['たいせつ', 'အရေးကြီးသော'])
            ->assertSeeInOrder(['おとな', 'လူကြီး']);

        $small = Kanji::query()->where('character', '小')->firstOrFail();
        $this->get(route('kanji.show', $small))
            ->assertOk()
            ->assertSeeInOrder(['ちいさい', 'သေးသည်'])
            ->assertSeeInOrder(['しょうがくせい', 'မူလတန်းကျောင်းသား'])
            ->assertSeeInOrder(['しょうがっこう', 'မူလတန်းကျောင်း'])
            ->assertSeeInOrder(['こぜに', 'အကြွေငွေ']);

        $enter = Kanji::query()->where('character', '入')->firstOrFail();
        $this->get(route('kanji.show', $enter))
            ->assertOk()
            ->assertSeeInOrder(['はいる', 'ဝင်သည်'])
            ->assertSeeInOrder(['いれる', 'ထည့်သည်'])
            ->assertSeeInOrder(['いりぐち', 'ဝင်ပေါက်'])
            ->assertSeeInOrder(['にゅうがく', 'ကျောင်းဝင်သည်'])
            ->assertSeeInOrder(['にゅうがくしき', 'ကျောင်းဝင်ပွဲ']);

        $leave = Kanji::query()->where('character', '出')->firstOrFail();
        $this->get(route('kanji.show', $leave))
            ->assertOk()
            ->assertSeeInOrder(['でる', 'ထွက်သည်'])
            ->assertSeeInOrder(['だす', 'ထုတ်သည်'])
            ->assertSeeInOrder(['でぐち', 'ထွက်ပေါက်'])
            ->assertSeeInOrder(['でかける', 'အပြင်ထွက်သည်'])
            ->assertSeeInOrder(['しゅっせき', 'တက်ရောက်ခြင်း'])
            ->assertSeeInOrder(['がいしゅつ', 'အပြင်ထွက်ခြင်း']);

        $gate = Kanji::query()->where('character', '門')->firstOrFail();
        $this->get(route('kanji.show', $gate))
            ->assertOk()
            ->assertSeeInOrder(['もん', 'တံခါး'])
            ->assertSeeInOrder(['こうもん', 'ကျောင်းတံခါး'])
            ->assertSeeInOrder(['せいもん', 'ရှေ့မုခ်'])
            ->assertSeeInOrder(['せんもん', 'အထူးပြု'])
            ->assertSeeInOrder(['にゅうもん', 'အခြေခံသင်ခန်းစာ']);

        $tall = Kanji::query()->where('character', '高')->firstOrFail();
        $this->get(route('kanji.show', $tall))
            ->assertOk()
            ->assertSeeInOrder(['たかい', 'မြင့်သည် · ဈေးကြီးသည်'])
            ->assertSeeInOrder(['こうこう', 'အထက်တန်းကျောင်း'])
            ->assertSeeInOrder(['こうこうせい', 'အထက်တန်းကျောင်းသား']);

        $friend = Kanji::query()->where('character', '友')->firstOrFail();
        $this->get(route('kanji.show', $friend))
            ->assertOk()
            ->assertSeeInOrder(['ともだち', 'သူငယ်ချင်း'])
            ->assertSeeInOrder(['しんゆう', 'ရင်းနှီးသောသူငယ်ချင်း']);
    }

    public function test_person_kanji_lists_n5_person_vocabulary(): void
    {
        $this->seed(KanjiSeeder::class);
        $person = Kanji::query()->where('character', '人')->firstOrFail();

        $this->get(route('kanji.show', $person))
            ->assertOk()
            ->assertSee('N5 ဝေါဟာရ')
            ->assertSeeInOrder(['かぞく', 'မိသားစု'])
            ->assertDontSee('>家<', false)
            ->assertDontSee('>族<', false)
            ->assertSeeInOrder(['外', '国', '人', 'がいこくじん', 'နိုင်ငံခြားသား'])
            ->assertSee('だれ')
            ->assertSee('おとうさん');

        $day = Kanji::query()->where('character', '日')->firstOrFail();
        $this->get(route('kanji.show', $day))
            ->assertOk()
            ->assertSee('にちようび')
            ->assertSee('တနင်္ဂနွေနေ့')
            ->assertDontSee('N5 ဝေါဟာရ');
    }

    public function test_example_word_shows_each_kanji_master_meaning(): void
    {
        $this->seed(KanjiSeeder::class);
        $kanji = Kanji::query()->where('character', '校')->firstOrFail();

        $this->get(route('kanji.show', $kanji))
            ->assertOk()
            ->assertSee('がっこう')
            ->assertSeeInOrder(['学', 'ပညာသင်သည်', '校', 'ကျောင်း']);
    }

    public function test_kotoba_section_lists_n5_vocabulary(): void
    {
        $this->get(route('kotoba.index'))
            ->assertOk()
            ->assertSee('N5 言葉')
            ->assertSee('702')
            ->assertSee('<div class="word hiragana">あいます</div>', false)
            ->assertDontSee('>あう<', false)
            ->assertSee('<div class="word">アパート</div>', false)
            ->assertDontSee('>会う<', false)
            ->assertSee('vocab-grid')
            ->assertDontSee('class="breakdown"', false);

        $this->get(route('kotoba.index', ['q' => 'ကျောင်း']))
            ->assertOk()
            ->assertSee('がっこう')
            ->assertSee('ကျောင်း')
            ->assertDontSee('あさって');
    }

    public function test_kotoba_filter_shows_only_i_adjectives(): void
    {
        $this->get(route('kotoba.index', ['type' => 'i-adj']))
            ->assertOk()
            ->assertSee('あたらしい')
            ->assertSee('60')
            ->assertDontSee('>新しい<', false);

        $this->get(route('kotoba.index', ['type' => 'na-adj']))
            ->assertOk()
            ->assertSee('19')
            ->assertSee('しずか')
            ->assertSee('ひま')
            ->assertSee('しんせつ')
            ->assertSee('ふべん')
            ->assertSee('かんたん')
            ->assertSee('ざんねん')
            ->assertSee('まじめ')
            ->assertSee('すてき')
            ->assertSee('နှစ်သက်သော')
            ->assertDontSee('あたらしい')
            ->assertDontSee('>いや<', false)
            ->assertDontSee('けっこう')
            ->assertDontSee('>じょうぶ<', false)
            ->assertDontSee('だいすき')
            ->assertDontSee('りっぱ');

        $this->get(route('kotoba.index', ['show' => 'ja']))
            ->assertOk()
            ->assertSee('あいます')
            ->assertDontSee('တွေ့သည်');

        $this->get(route('kotoba.index', ['show' => 'my']))
            ->assertOk()
            ->assertSee('တွေ့သည်')
            ->assertDontSee('あいます');

        $this->withUnencryptedCookie('n5_device', '11111111-1111-4111-8111-111111111111')
            ->post(route('kotoba.learned'), [
                'word' => '会う',
                'reading' => 'あう',
            ])->assertRedirect();

        $this->withUnencryptedCookie('n5_device', '11111111-1111-4111-8111-111111111111')
            ->get(route('kotoba.index', ['learned' => 'yes']))
            ->assertOk()
            ->assertSee('あいます')
            ->assertSee('ကျက်ပြီးပါပြီ')
            ->assertDontSee('アパート');

        $this->withUnencryptedCookie('n5_device', '11111111-1111-4111-8111-111111111111')
            ->get(route('kotoba.index', ['learned' => 'no']))
            ->assertOk()
            ->assertSee('アパート')
            ->assertDontSee('>あいます<', false);

        $this->withCredentials()
            ->withUnencryptedCookie('n5_device', '11111111-1111-4111-8111-111111111111')
            ->postJson(route('kotoba.learned'), [
                'word' => '会う',
                'reading' => 'あう',
            ])->assertOk()->assertJson(['learned' => false]);

        $this->assertDatabaseHas('learned_kotobas', [
            'user_id' => User::query()->where('device', '11111111-1111-4111-8111-111111111111')->value('id'),
            'word' => '会う',
            'reading' => 'あう',
            'learned' => 0,
        ]);

        $pdf = $this->get(route('kotoba.pdf', ['type' => 'adverb', 'show' => 'ja']));
        $pdf->assertOk();
        $pdf->assertHeader('content-type', 'application/pdf');
        $this->assertStringStartsWith('%PDF', $pdf->getContent());

        $this->get(route('kotoba.index', ['type' => 'adverb']))
            ->assertOk()
            ->assertSee('とても')
            ->assertSee('あまり')
            ->assertSee('きょう')
            ->assertSee('ゆっくり')
            ->assertSee('たいてい')
            ->assertSee('56')
            ->assertDontSee('がっこう')
            ->assertDontSee('ちょうど')
            ->assertDontSee('ゆっくりと');

        $this->get(route('kotoba.index', ['type' => 'verb']))
            ->assertOk()
            ->assertSee('あいます')
            ->assertSee('たべます')
            ->assertDontSee('がっこう')
            ->assertDontSee('>あう<', false);

        $this->get(route('kotoba.index', ['type' => 'noun', 'q' => '学校']))
            ->assertOk()
            ->assertSee('がっこう')
            ->assertDontSee('>学校<', false);
    }

    public function test_quiz_accepts_the_myanmar_meaning(): void
    {
        $this->seed(KanjiSeeder::class);
        $kanji = Kanji::query()->where('character', '人')->firstOrFail();

        $this->post('/quiz', [
            'kanji_id' => $kanji->id,
            'choice' => 'လူ',
        ])->assertOk()->assertSee('မှန်ပါသည်');

        $this->assertSame(1, session('quiz_score')['correct']);
    }

    public function test_kanji_vocab_page_lists_words_and_filters_by_kanji(): void
    {
        $this->seed(KanjiSeeder::class);

        $this->get(route('kanji.words'))
            ->assertOk()
            ->assertSee('N5 漢字言葉')
            ->assertSee('vocab-grid')
            ->assertSee('<div class="word hiragana">一つ</div>', false)
            ->assertSee('၁ ခု')
            ->assertSee('漢字言葉');

        $this->get(route('kanji.words', ['kanji' => '門']))
            ->assertOk()
            ->assertSee('校門')
            ->assertSee('正門')
            ->assertDontSee('一つ');

        $this->get(route('kanji.words', ['q' => 'ဘဏ်']))
            ->assertOk()
            ->assertSee('銀行')
            ->assertSee('ဘဏ်')
            ->assertDontSee('校門');

        $this->get(route('kanji.words', ['kanji' => '人']))
            ->assertOk()
            ->assertSee('<div class="word hiragana">かぞく</div>', false)
            ->assertSee('မိသားစု')
            ->assertDontSee('>家<', false)
            ->assertDontSee('>族<', false);

        $this->get(route('kanji.words', ['show' => 'my', 'q' => '一つ']))
            ->assertOk()
            ->assertSee('၁ ခု')
            ->assertDontSee('<div class="word hiragana">一つ</div>', false);
    }

    public function test_kanji_vocab_page_can_hide_kanji_or_hiragana(): void
    {
        $this->seed(KanjiSeeder::class);

        $this->get(route('kanji.words', ['q' => '一つ']))
            ->assertOk()
            ->assertSee('Show Kanji')
            ->assertSee('Hide Kanji')
            ->assertSee('Show hiragana')
            ->assertSee('Hide hiragana')
            ->assertSee('<div class="word hiragana">一つ</div>', false)
            ->assertSee('<span>ひとつ</span>', false);

        $this->get(route('kanji.words', ['q' => '一つ', 'glyph' => 'hide']))
            ->assertOk()
            ->assertSee('<div class="word hiragana">ひとつ</div>', false)
            ->assertSee('name="glyph" value="hide"', false)
            ->assertDontSee('<div class="word hiragana">一つ</div>', false)
            ->assertDontSee('<span>ひとつ</span>', false);

        $this->get(route('kanji.words', ['q' => '一つ', 'kana' => 'hide']))
            ->assertOk()
            ->assertSee('<div class="word">一</div>', false)
            ->assertDontSee('<div class="word hiragana">一つ</div>', false)
            ->assertDontSee('<span>ひとつ</span>', false);

        $this->get(route('kanji.words', ['q' => '一つ', 'glyph' => 'hide', 'kana' => 'hide']))
            ->assertOk()
            ->assertSee('၁ ခု')
            ->assertDontSee('<div class="word', false)
            ->assertDontSee('<span>ひとつ</span>', false);
    }
}
