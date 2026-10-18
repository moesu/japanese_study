@extends('layouts.app')

@section('title', $kanji->character)

@section('content')
    <p><a href="{{ route('kanji.index') }}">← စာရင်းသို့</a></p>
    <div class="panel">
        <p class="seal">{{ $kanji->strokes }} strokes · radical {{ $kanji->radical }}</p>
        <p class="hero-kanji">{{ $kanji->character }}</p>
        <p class="my" style="font-size: 1.8rem; margin-bottom: 0;">{{ $kanji->meaning_my }}</p>
        <p class="en" style="font-size: 1.15rem;">{{ $kanji->meaning_en }}</p>

        <table>
            <tr>
                <th>On'yomi</th>
                <td>{{ $kanji->readingLine('onyomi') }}</td>
            </tr>
            <tr>
                <th>Kun'yomi</th>
                <td>{{ $kanji->readingLine('kunyomi') }}</td>
            </tr>
        </table>

        <h2>အဓိပ္ပာယ်များ</h2>
        <table>
            @foreach ($kanji->meanings as $meaning)
                <tr>
                    <td>{{ $meaning['my'] }}</td>
                    <td class="en">{{ $meaning['en'] }}</td>
                </tr>
            @endforeach
        </table>

        @if ($vocabGroups !== [])
            <h2>N5 ဝေါဟာရ · လူ</h2>
            <p class="meta">ခန်ဂျိတစ်လုံးချင်းစီအောက်တွင် ၎င်း၏ အဓိကအဓိပ္ပာယ်ကို ပြထားသည်။ စကားလုံးအဓိပ္ပာယ်က အောက်ဆုံးတွင် ရှိသည်။</p>
            @foreach ($vocabGroups as $group)
                <h3>{{ $group['label'] }}</h3>
                <div class="examples">
                    @foreach ($group['words'] as $example)
                        @include('kanji.word', ['example' => $example])
                    @endforeach
                </div>
            @endforeach
        @endif

        @if ($chapterWords !== [])
            <h2>ဝေါဟာရ</h2>
            <p class="meta">ခန်ဂျိတစ်လုံးချင်းစီအောက်တွင် ၎င်း၏ အဓိကအဓိပ္ပာယ်ကို ပြထားသည်။</p>
            <div class="examples">
                @foreach ($chapterWords as $example)
                    @include('kanji.word', ['example' => $example])
                @endforeach
            </div>
        @elseif ($vocabGroups === [])
            <h2>ဥပမာစကားလုံး</h2>
            <p class="meta">ခန်ဂျိတစ်လုံးချင်းစီအောက်တွင် ၎င်း၏ အဓိကအဓိပ္ပာယ်ကို ပြထားသည်။</p>
            <div class="examples">
                @foreach ($kanji->examples as $example)
                    @include('kanji.word', ['example' => $example])
                @endforeach
            </div>
        @endif
    </div>

    <div class="row" style="margin-top: 1rem;">
        @if ($previous)
            <a class="button ghost" href="{{ route('kanji.show', $previous) }}">← {{ $previous->character }}</a>
        @else
            <span></span>
        @endif
        @if ($next)
            <a class="button ghost" href="{{ route('kanji.show', $next) }}">{{ $next->character }} →</a>
        @endif
    </div>
@endsection
