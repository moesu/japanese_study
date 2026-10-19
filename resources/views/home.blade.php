@extends('layouts.app')

@section('title', 'JLPT')

@section('content')
    <div class="row">
        <div>
            <h1 style="margin: 0;">JLPT</h1>
            <p class="meta">အဆင့်ရွေးပြီး စတင်လေ့လာပါ။</p>
        </div>
    </div>

    <div class="level-grid">
        <a class="level" href="{{ route('goi.show') }}">N5</a>
        <a class="level" href="{{ route('n4.kotoba') }}">N4</a>
        <span class="level soon">N3</span>
        <span class="level soon">N2</span>
        <span class="level soon">N1</span>
    </div>
@endsection
