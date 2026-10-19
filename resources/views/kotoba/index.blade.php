@extends('layouts.app')

@section('title', 'N5 言葉')

@section('content')
    <div class="row">
        <div>
            <h1 style="margin: 0;">N5 言葉</h1>
            <p class="meta">JLPT N5 ဝေါဟာရ {{ $total }} လုံး။ အမျိုးအစားရွေးပြီး ရှာနိုင်သည်။</p>
        </div>
        <span class="seal">{{ $words->total() }} / {{ $total }}</span>
    </div>

    <form class="search" method="get" action="{{ route('kotoba.index') }}">
        @if ($type !== 'all')
            <input type="hidden" name="type" value="{{ $type }}">
        @endif
        @if ($show !== 'both')
            <input type="hidden" name="show" value="{{ $show }}">
        @endif
        @if ($learned !== 'all')
            <input type="hidden" name="learned" value="{{ $learned }}">
        @endif
        <input type="search" name="q" value="{{ $q }}" placeholder="言葉、 reading၊ school၊ ကျောင်း" autofocus>
        <button type="submit">ရှာမည်</button>
        <a class="button ghost" href="{{ route('kotoba.pdf', array_filter(['q' => $q ?: null, 'type' => $type === 'all' ? null : $type, 'show' => $show === 'both' ? null : $show, 'learned' => $learned === 'all' ? null : $learned])) }}">PDF</a>
    </form>

    <details class="type-drop">
        <summary>{{ $types[$type] }} <span>{{ $counts[$type] }}</span></summary>
        <div class="filters">
            @foreach ($types as $key => $label)
                <a href="{{ route('kotoba.index', array_filter(['q' => $q ?: null, 'type' => $key === 'all' ? null : $key, 'show' => $show === 'both' ? null : $show, 'learned' => $learned === 'all' ? null : $learned])) }}" @class(['active' => $type === $key])>
                    {{ $label }}
                    <span>{{ $counts[$key] }}</span>
                </a>
            @endforeach
        </div>
    </details>

    <div class="filters">
        @foreach ($shows as $key => $label)
            <a href="{{ route('kotoba.index', array_filter(['q' => $q ?: null, 'type' => $type === 'all' ? null : $type, 'show' => $key === 'both' ? null : $key, 'learned' => $learned === 'all' ? null : $learned])) }}" @class(['active' => $show === $key])>
                {{ $label }}
            </a>
        @endforeach
    </div>

    <div class="filters">
        @foreach ($learnedOptions as $key => $label)
            <a href="{{ route('kotoba.index', array_filter(['q' => $q ?: null, 'type' => $type === 'all' ? null : $type, 'show' => $show === 'both' ? null : $show, 'learned' => $key === 'all' ? null : $key])) }}" @class(['active' => $learned === $key])>
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
                @php($shown = $word['masu'] ?? $word['reading'])
                <article class="vocab-card">
                    @if ($show !== 'my')
                        <div @class(['word', 'hiragana' => preg_match('/[\x{3040}-\x{309F}]/u', $shown) === 1])>{{ $shown }}</div>
                    @endif
                    @if ($show !== 'ja')
                        <div class="my">{{ $word['my'] }}</div>
                    @endif
                    @if ($show === 'both')
                        <div class="en">{{ $word['en'] }}</div>
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
