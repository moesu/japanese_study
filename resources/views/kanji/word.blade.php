@php
    $focus = $focus ?? null;
@endphp
<article class="vocab-item">
    <div class="breakdown">
        @foreach (\App\Models\Kanji::charactersIn($example['word']) as $piece)
            <span @class([
                'unit',
                'kana' => ! $piece['kanji'],
                'focus' => $focus !== null && $piece['char'] === $focus,
            ])>
                <span class="glyph">{{ $piece['char'] }}</span>
                @if ($piece['meaning_my'])
                    <span class="my">{{ $piece['meaning_my'] }}</span>
                    <span class="en">{{ $piece['meaning_en'] }}</span>
                @endif
            </span>
        @endforeach
    </div>
    <p class="reading">{{ $example['reading'] }}</p>
    <p class="gloss">
        <span class="my">{{ $example['my'] }}</span>
        <span class="en">{{ $example['en'] }}</span>
    </p>
</article>
