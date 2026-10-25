<?php

namespace App\Http\Controllers;

use App\Models\LearnedKotoba;
use App\Models\User;
use App\Support\BrowserDevice;
use App\Support\N3Goi;
use App\Support\N3Kanji;
use App\Support\N4Goi;
use App\Support\N5Goi;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\View\View;

class GoiController extends Controller
{
    /**
     * @var array<string, string>
     */
    private const KINDS = [
        'all' => 'အားလုံး',
        'i-adj' => 'い-adj',
        'na-adj' => 'な-adj',
        'adverb' => 'adverb',
        'verb' => 'verb',
        'noun' => 'noun',
        'other' => 'other',
    ];

    /**
     * @var array<string, string>
     */
    private const SHOWS = [
        'both' => 'နှစ်မျိုး',
        'ja' => '日本語',
        'my' => 'မြန်မာ',
    ];

    /**
     * @var array<string, string>
     */
    private const LEARNED = [
        'all' => 'အားလုံး',
        'yes' => 'ကျက်ပြီးပါပြီ',
        'no' => 'မကျက်ရသေးပါ',
    ];

    public function show(Request $request): View
    {
        return $this->render($request, N5Goi::class, 'goi.show', 'Himawari N5 ဝေါဟာရ။ အခန်းများကို နှိပ်ပြီး ကြည့်မည် ကိုနှိပ်ပါ။');
    }

    public function n4(Request $request): View
    {
        return $this->render($request, N4Goi::class, 'n4.kotoba', 'Minna no Nihongo N4 ဝေါဟာရ။ အခန်းများကို နှိပ်ပြီး ကြည့်မည် ကိုနှိပ်ပါ။');
    }

    public function n3(Request $request): View
    {
        return $this->render($request, N3Goi::class, 'n3.kotoba', 'N3 Kotoba Standard 2400။ အခန်းများကို နှိပ်ပြီး ကြည့်မည် ကိုနှိပ်ပါ။');
    }

    public function n3Kanji(Request $request): View
    {
        return $this->render($request, N3Kanji::class, 'n3.kanji', 'N3 Kanji။ အခန်းများကို နှိပ်ပြီး ကြည့်မည် ကိုနှိပ်ပါ။', 'word');
    }

    /**
     * @param  class-string<N3Goi|N3Kanji|N4Goi|N5Goi>  $source
     */
    private function render(Request $request, string $source, string $routeName, string $intro, string $lead = 'reading'): View
    {
        $show = (string) $request->query('show', 'both');
        if (! array_key_exists($show, self::SHOWS)) {
            $show = 'both';
        }

        $kind = (string) $request->query('kind', 'all');
        if (! array_key_exists($kind, self::KINDS)) {
            $kind = 'all';
        }

        $learned = (string) $request->query('learned', 'all');
        if (! array_key_exists($learned, self::LEARNED)) {
            $learned = 'all';
        }

        $glyph = 'show';
        $kana = 'show';
        if ($lead === 'word') {
            $glyph = (string) $request->query('glyph', 'show');
            if (! in_array($glyph, ['show', 'hide'], true)) {
                $glyph = 'show';
            }

            $kana = (string) $request->query('kana', 'show');
            if (! in_array($kana, ['show', 'hide'], true)) {
                $kana = 'show';
            }
        }

        $lessons = $source::numbers();
        $chapters = $this->chapters($request, $kind, $source, $lessons);
        $inChapters = $source::wordsFor($chapters);
        $counts = array_fill_keys(array_keys(self::KINDS), 0);
        $counts['all'] = count($inChapters);
        foreach ($inChapters as $word) {
            $counts[$source::kindOf($word)]++;
        }
        $matched = $kind === 'all'
            ? $inChapters
            : array_values(array_filter($inChapters, fn (array $word): bool => $source::kindOf($word) === $kind));
        $learnedKeys = LearnedKotoba::learnedKeysFor(User::findForDevice(BrowserDevice::id($request)));
        $matched = array_map(function (array $word) use ($learnedKeys): array {
            $word['learned'] = isset($learnedKeys[$word['word'].'|'.$word['reading']]);

            return $word;
        }, $matched);
        $learnedCounts = [
            'all' => count($matched),
            'yes' => count(array_filter($matched, fn (array $word): bool => $word['learned'])),
            'no' => count(array_filter($matched, fn (array $word): bool => ! $word['learned'])),
        ];
        if ($learned === 'yes') {
            $matched = array_values(array_filter($matched, fn (array $word): bool => $word['learned']));
        } elseif ($learned === 'no') {
            $matched = array_values(array_filter($matched, fn (array $word): bool => ! $word['learned']));
        }
        $perPage = 50;
        $page = LengthAwarePaginator::resolveCurrentPage();
        $words = new LengthAwarePaginator(
            array_slice($matched, ($page - 1) * $perPage, $perPage),
            count($matched),
            $perPage,
            $page,
            [
                'path' => Paginator::resolveCurrentPath(),
                'query' => $request->query(),
            ],
        );

        return view('goi.show', [
            'chapter' => $request->route('chapter'),
            'chapters' => $chapters,
            'lessons' => $lessons,
            'routeName' => $routeName,
            'intro' => $intro,
            'words' => $words,
            'show' => $show,
            'shows' => self::SHOWS,
            'kind' => $kind,
            'kinds' => self::KINDS,
            'counts' => $counts,
            'learned' => $learned,
            'learnedOptions' => self::LEARNED,
            'learnedCounts' => $learnedCounts,
            'lessonLabels' => method_exists($source, 'labels') ? $source::labels() : [],
            'sections' => method_exists($source, 'sections') ? $source::sections() : [],
            'lead' => $lead,
            'glyph' => $glyph,
            'kana' => $kana,
        ]);
    }

    /**
     * @param  class-string<N3Goi|N3Kanji|N4Goi|N5Goi>  $source
     * @param  array<int, int>  $lessons
     * @return array<int, int>
     */
    private function chapters(Request $request, string $kind, string $source, array $lessons): array
    {
        if ($request->query('chapters') === null) {
            $routeChapter = $request->route('chapter');
            if ($routeChapter === null || $kind !== 'all') {
                return $lessons;
            }

            $number = (int) $routeChapter;
            abort_if($source::chapter($number) === null, 404);

            return [$number];
        }

        $raw = $request->query('chapters');
        if (! is_array($raw)) {
            $raw = [$raw];
        }

        if (in_array('all', array_map(strval(...), $raw), true)) {
            return $lessons;
        }

        $allowed = array_flip($lessons);
        $chapters = [];
        foreach ($raw as $value) {
            $number = (int) $value;
            if (isset($allowed[$number])) {
                $chapters[$number] = $number;
            }
        }

        $chapters = array_values($chapters);
        sort($chapters);
        abort_if($chapters === [], 404);

        return $chapters;
    }
}
