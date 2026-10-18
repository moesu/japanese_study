<?php

namespace App\Http\Controllers;

use App\Models\Kanji;
use App\Support\N5ChapterVocab;
use App\Support\N5PersonVocab;
use Illuminate\Http\Request;
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
