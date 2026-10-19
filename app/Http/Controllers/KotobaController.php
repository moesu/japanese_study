<?php

namespace App\Http\Controllers;

use App\Models\LearnedKotoba;
use App\Models\User;
use App\Support\BrowserDevice;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\View\View;
use Mpdf\Config\ConfigVariables;
use Mpdf\Config\FontVariables;
use Mpdf\Mpdf;

class KotobaController extends Controller
{
    /**
     * @var array<string, string>
     */
    private const TYPES = [
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

    public function index(Request $request): View
    {
        ['q' => $q, 'type' => $type, 'show' => $show, 'learned' => $learned, 'all' => $all, 'searched' => $searched, 'filtered' => $filtered, 'learnedCounts' => $learnedCounts] = $this->selection($request);

        $counts = ['all' => $searched->count()];
        foreach (array_keys(self::TYPES) as $kind) {
            if ($kind === 'all') {
                continue;
            }
            $counts[$kind] = $searched->where('kind', $kind)->count();
        }

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

        return view('kotoba.index', [
            'words' => $words,
            'q' => $q,
            'type' => $type,
            'types' => self::TYPES,
            'counts' => $counts,
            'show' => $show,
            'shows' => self::SHOWS,
            'learned' => $learned,
            'learnedOptions' => self::LEARNED,
            'learnedCounts' => $learnedCounts,
            'total' => $all->count(),
        ]);
    }

    public function toggleLearned(Request $request): JsonResponse|RedirectResponse
    {
        $data = $request->validate([
            'word' => ['nullable', 'string', 'max:80'],
            'reading' => ['required', 'string', 'max:80'],
        ]);
        $device = BrowserDevice::id($request);
        $learned = false;

        if ($device !== null) {
            $learned = LearnedKotoba::toggle(User::forDevice($device), $data['word'] ?? '', $data['reading']);
        }

        if ($request->expectsJson()) {
            return response()->json(['learned' => $learned]);
        }

        return back();
    }

    public function pdf(Request $request): Response
    {
        ['type' => $type, 'show' => $show, 'learned' => $learned, 'filtered' => $filtered] = $this->selection($request);

        $html = view('kotoba.pdf', [
            'words' => $filtered->all(),
            'show' => $show,
            'heading' => 'N5 Kotoba'.($type === 'all' ? '' : ' · '.self::TYPES[$type]),
        ])->render();

        $defaultConfig = (new ConfigVariables)->getDefaults();
        $fontConfig = (new FontVariables)->getDefaults();
        File::ensureDirectoryExists(storage_path('app/mpdf'));

        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'tempDir' => storage_path('app/mpdf'),
            'margin_left' => 10,
            'margin_right' => 10,
            'margin_top' => 12,
            'margin_bottom' => 12,
            'fontDir' => array_merge($defaultConfig['fontDir'], [resource_path('fonts')]),
            'fontdata' => $fontConfig['fontdata'] + [
                'notojp' => ['R' => 'NotoSansJP-Regular.ttf'],
                'notomy' => ['R' => 'NotoSansMyanmar-Regular.ttf'],
            ],
            'default_font' => 'notojp',
        ]);
        $mpdf->WriteHTML($html);

        $name = 'n5-kotoba'.($type === 'all' ? '' : '-'.$type).($show === 'both' ? '' : '-'.$show).($learned === 'all' ? '' : ($learned === 'yes' ? '-learned' : '-not-learned')).'.pdf';

        return response($mpdf->Output('', 'S'), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$name.'"',
        ]);
    }

    /**
     * @return array{q: string, type: string, show: string, learned: string, all: Collection<int, array<string, mixed>>, searched: Collection<int, array<string, mixed>>, filtered: Collection<int, array<string, mixed>>, learnedCounts: array<string, int>}
     */
    private function selection(Request $request): array
    {
        $q = trim((string) $request->query('q', ''));
        $type = (string) $request->query('type', 'all');
        if (! array_key_exists($type, self::TYPES)) {
            $type = 'all';
        }

        $show = (string) $request->query('show', 'both');
        if (! array_key_exists($show, self::SHOWS)) {
            $show = 'both';
        }

        $learned = (string) $request->query('learned', 'all');
        if (! array_key_exists($learned, self::LEARNED)) {
            $learned = 'all';
        }

        $all = collect(json_decode(
            File::get(database_path('data/n5_kotoba.json')),
            true,
            flags: JSON_THROW_ON_ERROR,
        ));

        $searched = $all->when($q !== '', function ($words) use ($q) {
            $needle = mb_strtolower($q);

            return $words->filter(function (array $word) use ($needle) {
                $haystack = mb_strtolower($word['word'].$word['reading'].($word['masu'] ?? '').$word['en'].$word['my']);

                return str_contains($haystack, $needle);
            })->values();
        });

        $filtered = $type === 'all'
            ? $searched
            : $searched->where('kind', $type)->values();

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

        return compact('q', 'type', 'show', 'learned', 'all', 'searched', 'filtered', 'learnedCounts');
    }
}
