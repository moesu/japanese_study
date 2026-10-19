@extends('layouts.app')

@php
    $lessonCount = count($lessons);
    $lessonLabel = count($chapters) === $lessonCount
        ? 'အခန်းအားလုံး'
        : implode('၊', array_map(fn (int $lesson): string => \App\Support\N5Goi::lessonLabel($lesson), $chapters));
    $goiQuery = array_filter([
        'chapters' => count($chapters) === $lessonCount ? null : $chapters,
        'kind' => $kind === 'all' ? null : $kind,
        'learned' => $learned === 'all' ? null : $learned,
    ]);
    $chapterSummary = match (true) {
        count($chapters) === $lessonCount => 'အခန်းအားလုံး',
        count($chapters) > 4 => 'အခန်း '.count($chapters).' ခု',
        count($chapters) === 0 => 'အခန်း ရွေးပါ',
        default => $lessonLabel,
    };
@endphp

@section('title', $kind === 'all' ? $lessonLabel : $kinds[$kind])

@section('content')
    <div class="row">
        <div>
            <p class="meta goi-intro" style="margin: 0;">{{ $intro }}</p>
        </div>
    </div>

    <button type="button" class="filter-open">
        <span>စစ်ထုတ်မည်</span>
        <span class="meta">{{ $chapterSummary }} · {{ $kinds[$kind] }}</span>
    </button>
    <div class="goi-tools">
    <div class="filter-sheet">
    <form class="search goi-search" method="get" action="{{ route($routeName) }}">
        <button type="button" class="filter-close">ပိတ်မည်</button>
        @if ($show !== 'both')
            <input type="hidden" name="show" value="{{ $show }}">
        @endif
        @if ($kind !== 'all')
            <input type="hidden" name="kind" value="{{ $kind }}">
        @endif
        @if ($learned !== 'all')
            <input type="hidden" name="learned" value="{{ $learned }}">
        @endif
        <div class="multi">
            <button type="button" class="multi-toggle" aria-expanded="false" aria-controls="chapter-panel">
                <span class="multi-label">{{ $chapterSummary }}</span>
                <span class="multi-caret">▼</span>
            </button>
            <div class="multi-panel" id="chapter-panel" hidden>
                <div class="multi-actions">
                    <button type="button" data-chapters="all">အားလုံး</button>
                    <button type="button" data-chapters="none">ရှင်းမည်</button>
                </div>
                <div class="multi-grid">
                    <label class="chip">
                        <input type="checkbox" name="chapters[]" value="all" data-label="အခန်းအားလုံး" @checked(count($chapters) === $lessonCount)>
                        <span>အခန်းအားလုံး</span>
                    </label>
                    @foreach ($lessons as $lesson)
                        <label class="chip">
                            <input type="checkbox" name="chapters[]" value="{{ $lesson }}" data-label="{{ \App\Support\N5Goi::lessonLabel($lesson) }}" @checked(count($chapters) !== $lessonCount && in_array($lesson, $chapters, true))>
                            <span>{{ \App\Support\N5Goi::lessonLabel($lesson) }}</span>
                        </label>
                    @endforeach
                </div>
            </div>
        </div>
        <details class="type-drop">
            <summary>{{ $kinds[$kind] }} <span>{{ $counts[$kind] }}</span></summary>
            <div class="filters">
                @foreach ($kinds as $key => $label)
                    <a href="{{ route($routeName, array_filter([...$goiQuery, 'kind' => $key === 'all' ? null : $key, 'show' => $show === 'both' ? null : $show])) }}" @class(['active' => $kind === $key])>
                        {{ $label }}
                        <span>{{ $counts[$key] }}</span>
                    </a>
                @endforeach
            </div>
        </details>
        <button type="submit">ကြည့်မည်</button>
    </form>

    <div class="filters goi-filters">
        @foreach ($shows as $key => $label)
            <a href="{{ route($routeName, array_filter([...$goiQuery, 'show' => $key === 'both' ? null : $key])) }}" @class(['active' => $show === $key])>{{ $label }}</a>
        @endforeach
        @foreach ($learnedOptions as $key => $label)
            <a href="{{ route($routeName, array_filter([...$goiQuery, 'learned' => $key === 'all' ? null : $key])) }}" @class(['active' => $learned === $key]) data-learned-filter="{{ $key }}">
                {{ $label }}
                <span data-learned-count="{{ $key }}">{{ $learnedCounts[$key] }}</span>
            </a>
        @endforeach
    </div>
    </div>
    </div>

    <div class="vocab-grid">
        @foreach ($words as $word)
            <article class="vocab-card">
                <button type="button" @class(['mark', 'on' => $word['learned']]) data-word="{{ $word['word'] }}" data-reading="{{ $word['reading'] }}" title="{{ $word['learned'] ? 'ကျက်ပြီးပါပြီ' : 'မကျက်ရသေးပါ' }}" aria-label="{{ $word['learned'] ? 'ကျက်ပြီးပါပြီ' : 'မကျက်ရသေးပါ' }}">✓</button>
                @if (count($chapters) > 1)
                    <div class="meta">{{ \App\Support\N5Goi::lessonLabel($word['chapter']) }}</div>
                @endif
                @if ($show !== 'my')
                    <div @class(['word', 'hiragana' => preg_match('/[\x{3040}-\x{309F}]/u', $word['reading']) === 1])>{{ $word['reading'] }}</div>
                    @if ($word['word'] !== '')
                        <div class="meta">{{ $word['word'] }}@if ($word['group'] !== '') · {{ $word['group'] }}@endif</div>
                    @elseif ($word['group'] !== '')
                        <div class="meta">{{ $word['group'] }}</div>
                    @endif
                @endif
                @if ($show !== 'ja' && $word['my'] !== '')
                    <div class="my">{{ $word['my'] }}</div>
                @endif
            </article>
        @endforeach
    </div>

    @if ($words->lastPage() > 1)
        <div class="row pager" style="margin-top: 1.25rem;" data-next="{{ $words->nextPageUrl() }}">
            @if ($words->onFirstPage())
                <span></span>
            @else
                <a class="button ghost" href="{{ $words->previousPageUrl() }}">← အရင်</a>
            @endif
            <span class="meta">{{ $words->currentPage() }} / {{ $words->lastPage() }}</span>
            @if ($words->hasMorePages())
                <a class="button ghost" href="{{ $words->nextPageUrl() }}">နောက် →</a>
            @endif
        </div>
    @endif

    <script>
        const chapterPicker = document.querySelector('.multi');
        const chapterToggle = chapterPicker.querySelector('.multi-toggle');
        const chapterPanel = chapterPicker.querySelector('.multi-panel');
        const chapterBoxes = [...chapterPicker.querySelectorAll('input[type="checkbox"]')];
        const allChapters = chapterPicker.querySelector('input[value="all"]');
        const lessonBoxes = chapterBoxes.filter((box) => box !== allChapters);
        const chapterLabel = chapterPicker.querySelector('.multi-label');

        const chapterSummary = () => {
            const picked = lessonBoxes.filter((box) => box.checked).map((box) => box.dataset.label);
            if (allChapters.checked || picked.length === {{ $lessonCount }}) {
                chapterLabel.textContent = 'အခန်းအားလုံး';
            } else if (picked.length === 0) {
                chapterLabel.textContent = 'အခန်း ရွေးပါ';
            } else if (picked.length > 4) {
                chapterLabel.textContent = `အခန်း ${picked.length} ခု`;
            } else {
                chapterLabel.textContent = picked.join('၊');
            }
        };

        chapterToggle.addEventListener('click', () => {
            const open = chapterPanel.hidden;
            chapterPanel.hidden = !open;
            chapterToggle.setAttribute('aria-expanded', String(open));
        });

        document.addEventListener('click', (event) => {
            if (document.querySelector('.goi-tools.open')) {
                return;
            }
            if (!chapterPicker.contains(event.target)) {
                chapterPanel.hidden = true;
                chapterToggle.setAttribute('aria-expanded', 'false');
            }
        });

        allChapters.addEventListener('change', () => {
            if (allChapters.checked) {
                lessonBoxes.forEach((box) => { box.checked = false; });
            }
            chapterSummary();
        });
        lessonBoxes.forEach((box) => box.addEventListener('change', () => {
            if (box.checked) {
                allChapters.checked = false;
            }
            chapterSummary();
        }));
        chapterPicker.querySelector('[data-chapters="all"]').addEventListener('click', () => {
            allChapters.checked = true;
            lessonBoxes.forEach((box) => { box.checked = false; });
            chapterSummary();
        });
        chapterPicker.querySelector('[data-chapters="none"]').addEventListener('click', () => {
            chapterBoxes.forEach((box) => { box.checked = false; });
            chapterSummary();
        });

        const filterTools = document.querySelector('.goi-tools');
        const setFilterOpen = (open) => {
            filterTools.classList.toggle('open', open);
            document.body.classList.toggle('filter-lock', open);
            if (open) {
                chapterPanel.hidden = false;
                filterTools.querySelector('.type-drop')?.setAttribute('open', '');
            }
        };
        document.querySelector('.filter-open')?.addEventListener('click', () => setFilterOpen(true));
        document.querySelector('.filter-close')?.addEventListener('click', () => setFilterOpen(false));
        filterTools.addEventListener('click', (event) => {
            if (event.target === filterTools) {
                setFilterOpen(false);
            }
        });

        const learnedToken = document.querySelector('meta[name="csrf-token"]').content;
        const learnedFilter = document.querySelector('[data-learned-filter].active')?.dataset.learnedFilter ?? 'all';
        const bindMark = (button) => {
            button.addEventListener('click', async () => {
                const wasOn = button.classList.contains('on');
                button.disabled = true;
                try {
                    const response = await fetch('{{ route('kotoba.learned', [], false) }}', {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': learnedToken,
                        },
                        body: JSON.stringify({
                            word: button.dataset.word,
                            reading: button.dataset.reading,
                        }),
                    });
                    if (!response.ok) {
                        return;
                    }
                    const payload = await response.json();
                    button.classList.toggle('on', payload.learned);
                    const label = payload.learned ? 'ကျက်ပြီးပါပြီ' : 'မကျက်ရသေးပါ';
                    button.title = label;
                    button.setAttribute('aria-label', label);
                    const yes = document.querySelector('[data-learned-count="yes"]');
                    const no = document.querySelector('[data-learned-count="no"]');
                    if (yes && no && wasOn !== payload.learned) {
                        yes.textContent = String(Number(yes.textContent) + (payload.learned ? 1 : -1));
                        no.textContent = String(Number(no.textContent) + (payload.learned ? -1 : 1));
                    }
                    const card = button.closest('article');
                    if ((learnedFilter === 'yes' && !payload.learned) || (learnedFilter === 'no' && payload.learned)) {
                        card.remove();
                    }
                } finally {
                    button.disabled = false;
                }
            });
        };
        document.querySelectorAll('.vocab-card .mark').forEach(bindMark);

        const pager = document.querySelector('.pager');
        if (pager && window.matchMedia('(max-width: 640px)').matches && pager.dataset.next) {
            const grid = document.querySelector('.vocab-grid');
            const sentinel = document.createElement('div');
            let next = pager.dataset.next;
            let loading = false;
            pager.hidden = true;
            pager.after(sentinel);
            const loadNext = () => {
                if (!next || loading) {
                    return;
                }
                loading = true;
                const url = next;
                next = '';
                fetch(url)
                    .then((response) => response.ok ? response.text() : Promise.reject())
                    .then((html) => {
                        const doc = new DOMParser().parseFromString(html, 'text/html');
                        doc.querySelectorAll('.vocab-card').forEach((card) => {
                            grid.append(card);
                            const mark = card.querySelector('.mark');
                            if (mark) {
                                bindMark(mark);
                            }
                        });
                        next = doc.querySelector('.pager')?.dataset.next || '';
                        if (!next) {
                            sentinel.remove();
                        }
                    })
                    .catch(() => {
                        next = url;
                        pager.hidden = false;
                    })
                    .finally(() => {
                        loading = false;
                    });
            };
            new IntersectionObserver((entries) => {
                if (entries.some((entry) => entry.isIntersecting)) {
                    loadNext();
                }
            }, { rootMargin: '240px' }).observe(sentinel);
        }
    </script>
@endsection
