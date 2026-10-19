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

        $this->get(route('kanji.index'))
            ->assertOk()
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

    public function test_person_kanji_lists_n5_person_vocabulary(): void
    {
        $this->seed(KanjiSeeder::class);
        $person = Kanji::query()->where('character', '人')->firstOrFail();

        $this->get(route('kanji.show', $person))
            ->assertOk()
            ->assertSee('N5 ဝေါဟာရ')
            ->assertSeeInOrder(['家', '族', 'かぞく', 'မိသားစု'])
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
}
