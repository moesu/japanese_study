@extends('layouts.app')

@section('title', $kanji->character)

@section('content')
    <p class="back-link"><a href="{{ route('kanji.index', [], false) }}">← စာရင်းသို့</a></p>

    <article class="kanji-detail">
        <header class="kanji-hero">
            <p class="seal">{{ $kanji->strokes }} strokes · radical {{ $kanji->radical }}</p>
            <p class="hero-kanji">{{ $kanji->character }}</p>
            <p class="kanji-my">{{ $kanji->meaning_my }}</p>
            <p class="kanji-en">{{ $kanji->meaning_en }}</p>

            <div class="reading-block">
                <div class="reading-row">
                    <span class="reading-label">On</span>
                    <div class="reading-chips">
                        @forelse ($kanji->onyomi ?? [] as $reading)
                            <span class="chip">{{ $reading }}</span>
                        @empty
                            <span class="chip muted">—</span>
                        @endforelse
                    </div>
                </div>
                <div class="reading-row">
                    <span class="reading-label">Kun</span>
                    <div class="reading-chips">
                        @forelse ($kanji->kunyomi ?? [] as $reading)
                            <span class="chip">{{ $reading }}</span>
                        @empty
                            <span class="chip muted">—</span>
                        @endforelse
                    </div>
                </div>
            </div>

            @if (count($kanji->meanings ?? []) > 1)
                <ul class="sense-list">
                    @foreach ($kanji->meanings as $meaning)
                        <li>
                            <strong>{{ $meaning['my'] }}</strong>
                            <span class="en">{{ $meaning['en'] }}</span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </header>

        @if ($vocabGroups !== [])
            <section class="vocab-section">
                <h2>N5 ဝေါဟာရ · လူ</h2>
                <p class="meta">ခန်ဂျိတစ်လုံးချင်းစီအောက်တွင် အဓိကအဓိပ္ပာယ်ကို ပြထားသည်။</p>
                @foreach ($vocabGroups as $group)
                    <h3>{{ $group['label'] }}</h3>
                    <div class="vocab-list">
                        @foreach ($group['words'] as $example)
                            @include('kanji.word', ['example' => $example, 'focus' => $kanji->character])
                        @endforeach
                    </div>
                @endforeach
            </section>
        @endif

        @if ($chapterWords !== [])
            <section class="vocab-section">
                <h2>ဝေါဟာရ</h2>
                <p class="meta">ယခုခန်ဂျိပါသော စကားလုံးများ — အသားပေးအရောင်ဖြင့် ပြထားသည်။</p>
                <div class="vocab-list">
                    @foreach ($chapterWords as $example)
                        @include('kanji.word', ['example' => $example, 'focus' => $kanji->character])
                    @endforeach
                </div>
            </section>
        @elseif ($vocabGroups === [])
            <section class="vocab-section">
                <h2>ဥပမာစကားလုံး</h2>
                <p class="meta">ယခုခန်ဂျိပါသော စကားလုံးများ — အသားပေးအရောင်ဖြင့် ပြထားသည်။</p>
                <div class="vocab-list">
                    @foreach ($kanji->examples as $example)
                        @include('kanji.word', ['example' => $example, 'focus' => $kanji->character])
                    @endforeach
                </div>
            </section>
        @endif
    </article>

    <nav class="kanji-pager" aria-label="Previous and next kanji">
        @if ($previous)
            <a class="button ghost pager-btn" href="{{ route('kanji.show', $previous, false) }}">← {{ $previous->character }}</a>
        @else
            <span></span>
        @endif
        @if ($next)
            <a class="button ghost pager-btn" href="{{ route('kanji.show', $next, false) }}">{{ $next->character }} →</a>
        @endif
    </nav>
@endsection
