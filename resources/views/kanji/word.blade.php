<article class="example">
    <div class="breakdown">
        @foreach (\App\Models\Kanji::charactersIn($example['word']) as $piece)
            <span @class(['unit', 'kana' => ! $piece['kanji']])>
                <span class="glyph">{{ $piece['char'] }}</span>
                @if ($piece['meaning_my'])
                    <span class="my">{{ $piece['meaning_my'] }}</span>
                    <span class="en">{{ $piece['meaning_en'] }}</span>
                @endif
            </span>
        @endforeach
    </div>
    <p class="meta">{{ $example['reading'] }}</p>
    <p class="my">{{ $example['my'] }} <span class="en">· {{ $example['en'] }}</span></p>
</article>
