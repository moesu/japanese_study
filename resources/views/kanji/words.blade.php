@extends('layouts.app')

@section('title', 'N5 漢字言葉')

@section('content')
    @php
        $base = array_filter([
            'q' => $q !== '' ? $q : null,
            'kanji' => $char === 'all' ? null : $char,
            'show' => $show === 'both' ? null : $show,
            'learned' => $learned === 'all' ? null : $learned,
            'glyph' => $glyph === 'show' ? null : 'hide',
            'kana' => $kana === 'show' ? null : 'hide',
        ], fn ($value) => $value !== null);
        $link = function (array $overrides) use ($base) {
            return route('kanji.words', array_filter(array_merge($base, $overrides), fn ($value) => $value !== null), false);
        };
    @endphp

    <div class="row">
        <div>
            <h1 style="margin: 0;">N5 漢字言葉</h1>
            <p class="meta">N5 ခန်ဂျိဝေါဟာရ {{ $total }} လုံး။ ခန်ဂျိရွေးပြီး ရှာနိုင်သည်။</p>
        </div>
        <span class="seal">{{ $words->total() }} / {{ $total }}</span>
    </div>

    <form class="search" method="get" action="{{ route('kanji.words', [], false) }}">
        @foreach (collect($base)->except('q') as $name => $value)
            <input type="hidden" name="{{ $name }}" value="{{ $value }}">
        @endforeach
        <input type="search" name="q" value="{{ $q }}" placeholder="言葉၊ reading၊ school၊ ကျောင်း" autofocus>
        <button type="submit">ရှာမည်</button>
    </form>

    <details class="type-drop wide">
        <summary>{{ $char === 'all' ? 'အားလုံး' : $char }} <span>{{ $char === 'all' ? $matched : $options[$char] }}</span></summary>
        <div class="filters">
            @foreach ($options as $key => $count)
                <a href="{{ $link(['kanji' => $key === 'all' ? null : $key]) }}" @class(['active' => $char === $key])>
                    {{ $key === 'all' ? 'အားလုံး' : $key }}
                    <span>{{ $count }}</span>
                </a>
            @endforeach
        </div>
    </details>

    <div class="filters">
        @foreach ($shows as $key => $label)
            <a href="{{ $link(['show' => $key === 'both' ? null : $key]) }}" @class(['active' => $show === $key])>
                {{ $label }}
            </a>
        @endforeach
    </div>

    <div class="filters">
        <a href="{{ $link(['glyph' => null]) }}" @class(['active' => $glyph === 'show'])>Show Kanji</a>
        <a href="{{ $link(['glyph' => 'hide']) }}" @class(['active' => $glyph === 'hide'])>Hide Kanji</a>
        <a href="{{ $link(['kana' => null]) }}" @class(['active' => $kana === 'show'])>Show hiragana</a>
        <a href="{{ $link(['kana' => 'hide']) }}" @class(['active' => $kana === 'hide'])>Hide hiragana</a>
    </div>

    <div class="filters">
        @foreach ($learnedOptions as $key => $label)
            <a href="{{ $link(['learned' => $key === 'all' ? null : $key]) }}" @class(['active' => $learned === $key])>
                {{ $label }}
                <span>{{ $learnedCounts[$key] }}</span>
            </a>
        @endforeach
    </div>

    @if ($words->isEmpty())
        <p>မတွေ့ပါ။ စကားလုံး၊ အသံထွက်၊ အင်္ဂလိပ် သို့မဟုတ် မြန်မာအဓိပ္ပာယ်ဖြင့် ပြန်ရှာပါ။</p>
    @else
        <div class="vocab-grid">
            @foreach ($words as $word)
                @php
                    $pieces = collect(\App\Models\Kanji::charactersIn($word['word'], $word['reading']));
                    $shown = match (true) {
                        $glyph === 'hide' && $kana === 'show' => $word['reading'],
                        $glyph === 'show' && $kana === 'hide' => $pieces->where('kanji', true)->pluck('char')->implode(''),
                        $glyph === 'hide' && $kana === 'hide' => '',
                        default => $pieces->pluck('char')->implode(''),
                    };
                    $showReading = $show !== 'my' && $kana === 'show' && $glyph === 'show' && $shown !== $word['reading'];
                @endphp
                <article class="vocab-card">
                    @if ($show !== 'my' && $shown !== '')
                        <div @class(['word', 'hiragana' => preg_match('/[\x{3040}-\x{309F}]/u', $shown) === 1])>{{ $shown }}</div>
                    @endif
                    @if ($show !== 'ja')
                        <div class="my">{{ $word['my'] }}</div>
                    @endif
                    @if ($show === 'both' && $word['en'] !== '')
                        <div class="en">{{ $word['en'] }}</div>
                    @endif
                    @if ($showReading || $glyph === 'show')
                        <div class="meta">
                            @if ($showReading)
                                <span>{{ $word['reading'] }}</span>
                            @endif
                            @foreach ($word['kanji'] as $character)
                                @if (isset($kanjiIds[$character]))
                                    <a href="{{ route('kanji.show', $kanjiIds[$character], false) }}">{{ $character }}</a>
                                @else
                                    {{ $character }}
                                @endif
                            @endforeach
                        </div>
                    @endif
                    <form method="post" action="{{ route('kotoba.learned', [], false) }}">
                        @csrf
                        <input type="hidden" name="word" value="{{ $word['word'] }}">
                        <input type="hidden" name="reading" value="{{ $word['reading'] }}">
                        <button type="submit" @class(['learn', 'on' => $word['learned']])>{{ $word['learned'] ? 'ကျက်ပြီးပါပြီ' : 'မကျက်ရသေးပါ' }}</button>
                    </form>
                </article>
            @endforeach
        </div>

        @if ($words->lastPage() > 1)
            <div class="row" style="margin-top: 1.25rem;">
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
    @endif
@endsection
