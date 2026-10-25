<?php

namespace App\Http\Controllers;

use App\Models\Kanji;
use App\Models\LearnedKotoba;
use App\Models\User;
use App\Support\BrowserDevice;
use App\Support\N5ChapterVocab;
use App\Support\N5KanjiVocab;
use App\Support\N5PersonVocab;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class KanjiController extends Controller
{
    public function index(Request $request): View
    {
        $q = trim((string) $request->query('q', ''));

        $kanji = Kanji::query()
            ->when($q !== '', function ($query) use ($q) {
                $like = '%'.$q.'%';
                $query->where(function ($inner) use ($like) {
                    $inner->where('character', 'like', $like)
                        ->orWhere('meaning_en', 'like', $like)
                        ->orWhere('meaning_my', 'like', $like)
                        ->orWhere('onyomi', 'like', $like)
                        ->orWhere('kunyomi', 'like', $like)
                        ->orWhere('meanings', 'like', $like)
                        ->orWhere('examples', 'like', $like);
                });
            })
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        return view('kanji.index', [
            'kanji' => $kanji,
            'q' => $q,
            'total' => Kanji::query()->count(),
        ]);
    }

    public function show(Kanji $kanji): View
    {
        $ordered = Kanji::query()->orderBy('sort_order')->orderBy('id');

        $vocabGroups = N5PersonVocab::groupsFor($kanji->character);
        $alreadyShown = collect($vocabGroups)
            ->flatMap(fn (array $group) => $group['words'])
            ->pluck('word');

        return view('kanji.show', [
            'kanji' => $kanji,
            'vocabGroups' => $vocabGroups,
            'chapterWords' => collect(N5ChapterVocab::wordsFor($kanji->character))
                ->reject(fn (array $word) => $alreadyShown->contains($word['word']))
                ->values()
                ->all(),
            'previous' => (clone $ordered)->where('sort_order', '<', $kanji->sort_order)->orderByDesc('sort_order')->first(),
            'next' => (clone $ordered)->where('sort_order', '>', $kanji->sort_order)->orderBy('sort_order')->first(),
        ]);
    }

    public function words(Request $request): View
    {
        ['q' => $q, 'char' => $char, 'show' => $show, 'learned' => $learned, 'all' => $all, 'searched' => $searched, 'filtered' => $filtered, 'options' => $options, 'learnedCounts' => $learnedCounts] = $this->vocabSelection($request);

        $perPage = 72;
        $page = LengthAwarePaginator::resolveCurrentPage();
        $words = new LengthAwarePaginator(
            $filtered->forPage($page, $perPage)->values(),
            $filtered->count(),
            $perPage,
            $page,
            [
                'path' => Paginator::resolveCurrentPath(),
                'query' => $request->query(),
            ],
        );

        $glyph = (string) $request->query('glyph', 'show');
        if (! in_array($glyph, ['show', 'hide'], true)) {
            $glyph = 'show';
        }

        $kana = (string) $request->query('kana', 'show');
        if (! in_array($kana, ['show', 'hide'], true)) {
            $kana = 'show';
        }

        return view('kanji.words', [
            'words' => $words,
            'q' => $q,
            'char' => $char,
            'options' => $options,
            'show' => $show,
            'glyph' => $glyph,
            'kana' => $kana,
            'shows' => [
                'both' => 'နှစ်မျိုး',
                'ja' => '日本語',
                'my' => 'မြန်မာ',
            ],
            'learned' => $learned,
            'learnedOptions' => [
                'all' => 'အားလုံး',
                'yes' => 'ကျက်ပြီးပါပြီ',
                'no' => 'မကျက်ရသေးပါ',
            ],
            'learnedCounts' => $learnedCounts,
            'kanjiIds' => Kanji::query()->pluck('id', 'character'),
            'total' => $all->count(),
            'matched' => $searched->count(),
        ]);
    }

    public function quiz(): View
    {
        return view('kanji.quiz', $this->question());
    }

    public function answer(Request $request): View
    {
        $data = $request->validate([
            'kanji_id' => ['required', 'integer', 'exists:kanjis,id'],
            'choice' => ['required', 'string'],
        ]);

        $kanji = Kanji::query()->findOrFail($data['kanji_id']);
        $correct = $data['choice'] === $kanji->meaning_my;
        $score = session('quiz_score', ['correct' => 0, 'answered' => 0]);
        $score['answered']++;
        if ($correct) {
            $score['correct']++;
        }
        session(['quiz_score' => $score]);

        return view('kanji.quiz', [
            ...$this->question($kanji->id),
            'result' => [
                'correct' => $correct,
                'kanji' => $kanji,
                'choice' => $data['choice'],
            ],
            'score' => $score,
        ]);
    }

    public function resetQuiz(): View
    {
        session()->forget('quiz_score');

        return view('kanji.quiz', $this->question());
    }

    /**
     * @return array{q: string, char: string, show: string, learned: string, all: Collection<int, array<string, mixed>>, searched: Collection<int, array<string, mixed>>, filtered: Collection<int, array<string, mixed>>, options: array<string, int>, learnedCounts: array<string, int>}
     */
    private function vocabSelection(Request $request): array
    {
        $shows = ['both' => true, 'ja' => true, 'my' => true];
        $learnedOptions = ['all' => true, 'yes' => true, 'no' => true];

        $q = trim((string) $request->query('q', ''));
        $show = (string) $request->query('show', 'both');
        if (! isset($shows[$show])) {
            $show = 'both';
        }

        $learned = (string) $request->query('learned', 'all');
        if (! isset($learnedOptions[$learned])) {
            $learned = 'all';
        }

        $all = collect(N5KanjiVocab::words());
        $searched = $all->when($q !== '', function (Collection $words) use ($q) {
            $needle = mb_strtolower($q);

            return $words->filter(function (array $word) use ($needle) {
                $haystack = mb_strtolower($word['word'].$word['reading'].$word['en'].$word['my'].implode('', $word['kanji']));

                return str_contains($haystack, $needle);
            })->values();
        });

        $options = ['all' => $searched->count()];
        foreach ($searched as $word) {
            foreach ($word['kanji'] as $character) {
                $options[$character] = ($options[$character] ?? 0) + 1;
            }
        }

        $char = (string) $request->query('kanji', 'all');
        if (! array_key_exists($char, $options)) {
            $char = 'all';
        }

        $filtered = $char === 'all'
            ? $searched
            : $searched->filter(fn (array $word) => in_array($char, $word['kanji'], true))->values();

        $learnedKeys = LearnedKotoba::learnedKeysFor(User::findForDevice(BrowserDevice::id($request)));
        $filtered = $filtered->map(function (array $word) use ($learnedKeys) {
            $word['learned'] = isset($learnedKeys[$word['word'].'|'.$word['reading']]);

            return $word;
        })->values();

        $learnedCounts = [
            'all' => $filtered->count(),
            'yes' => $filtered->where('learned', true)->count(),
            'no' => $filtered->where('learned', false)->count(),
        ];

        if ($learned === 'yes') {
            $filtered = $filtered->where('learned', true)->values();
        } elseif ($learned === 'no') {
            $filtered = $filtered->where('learned', false)->values();
        }

        return compact('q', 'char', 'show', 'learned', 'all', 'searched', 'filtered', 'options', 'learnedCounts');
    }

    /**
     * @return array{prompt: Kanji, choices: array<int, string>, score: array{correct: int, answered: int}}
     */
    private function question(?int $exclude = null): array
    {
        $prompt = Kanji::query()
            ->when($exclude, fn ($query) => $query->whereKeyNot($exclude))
            ->inRandomOrder()
            ->firstOrFail();

        $choices = Kanji::query()
            ->whereKeyNot($prompt->id)
            ->inRandomOrder()
            ->limit(3)
            ->pluck('meaning_my')
            ->all();
        $choices[] = $prompt->meaning_my;
        shuffle($choices);

        return [
            'prompt' => $prompt,
            'choices' => $choices,
            'score' => session('quiz_score', ['correct' => 0, 'answered' => 0]),
        ];
    }
}
