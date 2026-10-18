<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: notojp; font-size: 11pt; color: #1f1a17; }
        h1 { font-family: notojp; font-size: 16pt; margin: 0 0 8px; }
        table { width: 100%; border-collapse: collapse; }
        td { width: 33.33%; border: 0.4pt solid #e4d9cc; padding: 6px 8px; vertical-align: top; }
        .ja { font-family: notojp; font-size: 13pt; }
        .my { font-family: notomy; font-size: 12pt; }
        .en { font-family: notojp; font-size: 8pt; color: #6b625b; }
    </style>
</head>
<body>
    <h1>{{ $heading }}</h1>
    <table>
        @foreach (array_chunk($words, 3) as $row)
            <tr>
                @foreach ($row as $word)
                    <td>
                        @if ($show !== 'my')
                            <div class="ja">{{ $word['masu'] ?? $word['reading'] }}</div>
                        @endif
                        @if ($show !== 'ja')
                            <div class="my">{{ $word['my'] }}</div>
                        @endif
                        @if ($show === 'both')
                            <div class="en">{{ $word['en'] }}</div>
                        @endif
                    </td>
                @endforeach
                @for ($i = count($row); $i < 3; $i++)
                    <td></td>
                @endfor
            </tr>
        @endforeach
    </table>
</body>
</html>
