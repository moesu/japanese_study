<?php

namespace App\Http\Controllers;

use App\Models\LearnedKotoba;
use App\Support\BrowserDevice;
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

    public function show(Request $request, int $chapter = 1): View
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

        $chapters = $this->chapters($request, $kind);
        $inChapters = N5Goi::wordsFor($chapters);
        $counts = array_fill_keys(array_keys(self::KINDS), 0);
        $counts['all'] = count($inChapters);
        foreach ($inChapters as $word) {
            $counts[N5Goi::kindOf($word)]++;
        }
        $matched = $kind === 'all'
            ? $inChapters
            : array_values(array_filter($inChapters, fn (array $word): bool => N5Goi::kindOf($word) === $kind));
        $learnedKeys = LearnedKotoba::query()
            ->where('device', BrowserDevice::id($request) ?? '')
            ->get(['word', 'reading'])
            ->mapWithKeys(fn (LearnedKotoba $row): array => [$row->word.'|'.$row->reading => true]);
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
            'chapter' => $chapter,
            'chapters' => $chapters,
            'words' => $words,
            'show' => $show,
            'shows' => self::SHOWS,
            'kind' => $kind,
            'kinds' => self::KINDS,
            'counts' => $counts,
            'learned' => $learned,
            'learnedOptions' => self::LEARNED,
            'learnedCounts' => $learnedCounts,
        ]);
    }

    /**
     * @return array<int, int>
     */
    private function chapters(Request $request, string $kind): array
    {
        if ($request->query('chapters') === null) {
            $routeChapter = $request->route('chapter');
            if ($routeChapter === null || $kind !== 'all') {
                return range(1, 25);
            }

            $number = (int) $routeChapter;
            abort_if(N5Goi::chapter($number) === null, 404);

            return [$number];
        }

        $raw = $request->query('chapters');
        if (! is_array($raw)) {
            $raw = [$raw];
        }

        if (in_array('all', array_map(strval(...), $raw), true)) {
            return range(1, 25);
        }

        $chapters = [];
        foreach ($raw as $value) {
            $number = (int) $value;
            if ($number >= 1 && $number <= 25) {
                $chapters[$number] = $number;
            }
        }

        $chapters = array_values($chapters);
        sort($chapters);
        abort_if($chapters === [], 404);

        return $chapters;
    }
}
