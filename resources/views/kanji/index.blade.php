@extends('layouts.app')

@section('title', 'N5 Kanji list')

@section('content')
    <div class="row">
        <div>
            <p class="meta" style="margin: 0;">အသုံးများသော N5 ခန်ဂျိ {{ $total }} လုံး။ ကဒ်တိုင်းတွင် အဓိကအဓိပ္ပာယ်နှင့် မြန်မာပြန် ပါသည်။</p>
        </div>
        <span class="seal">{{ $kanji->count() }} / {{ $total }}</span>
    </div>

    <form class="search" method="get" action="{{ route('kanji.index') }}">
        <input type="search" name="q" value="{{ $q }}" placeholder="漢字၊ reading၊ day၊ နေ့" autofocus>
        <button type="submit">ရှာမည်</button>
    </form>

    @if ($kanji->isEmpty())
        <p>မတွေ့ပါ။ စာလုံး၊ အသံထွက်၊ အင်္ဂလိပ် သို့မဟုတ် မြန်မာအဓိပ္ပာယ်ဖြင့် ပြန်ရှာပါ။</p>
    @else
        <div class="grid">
            @foreach ($kanji as $item)
                <a class="card" href="{{ route('kanji.show', $item) }}">
                    <div class="glyph">{{ $item->character }}</div>
                    <p class="my">{{ $item->meaning_my }}</p>
                    <p class="en">{{ $item->meaning_en }}</p>
                    <p class="meta">{{ $item->readingLine('kunyomi') }}</p>
                </a>
            @endforeach
        </div>
    @endif
@endsection
