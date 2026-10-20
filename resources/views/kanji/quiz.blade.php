@extends('layouts.app')

@section('title', 'Quiz')

@section('content')
    <div class="row">
        <div>
            <h1 style="margin: 0;">အဓိပ္ပာယ် စမ်းမေးခွန်း</h1>
            <p class="meta">ခန်ဂျိကို ကြည့်ပြီး မြန်မာအဓိပ္ပာယ်ကို ရွေးပါ။</p>
        </div>
        <span class="seal">{{ $score['correct'] }} / {{ $score['answered'] }}</span>
    </div>

    @isset($result)
        <p class="{{ $result['correct'] ? 'ok' : 'no' }}">
            @if ($result['correct'])
                မှန်ပါသည်။ {{ $result['kanji']->character }} = {{ $result['kanji']->meaning_my }} ({{ $result['kanji']->meaning_en }})
            @else
                မမှန်ပါ။ {{ $result['kanji']->character }} ၏ အဓိပ္ပာယ်မှာ {{ $result['kanji']->meaning_my }} ({{ $result['kanji']->meaning_en }}) ဖြစ်သည်။ သင်ရွေးထားသည်မှာ {{ $result['choice'] }} ဖြစ်သည်။
            @endif
        </p>
    @endisset

    <div class="panel">
        <p class="meta">နောက်တစ်ခု</p>
        <p class="hero-kanji">{{ $prompt->character }}</p>
        <form method="post" action="{{ route('kanji.quiz.answer', [], false) }}" class="choices">
            @csrf
            <input type="hidden" name="kanji_id" value="{{ $prompt->id }}">
            @foreach ($choices as $choice)
                <button type="submit" name="choice" value="{{ $choice }}">{{ $choice }}</button>
            @endforeach
        </form>
    </div>

    <form method="post" action="{{ route('kanji.quiz.reset', [], false) }}" style="margin-top: 1rem;">
        @csrf
        <button class="ghost" type="submit">အမှတ် ပြန်စမည်</button>
    </form>
@endsection
