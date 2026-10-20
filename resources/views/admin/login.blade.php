@extends('layouts.app')

@section('title', 'Admin')

@section('content')
    <div class="row">
        <div>
            <h1 style="margin: 0;">Admin</h1>
            <p class="meta">Device analytics အတွက် ဝင်ရန်။</p>
        </div>
    </div>

    <form method="post" action="{{ route('admin.login.store') }}" class="panel" style="max-width: 24rem; margin-top: 1.25rem;">
        @csrf
        <label for="password" class="meta" style="display: block; margin-bottom: 0.4rem;">Password</label>
        <input id="password" type="password" name="password" required autofocus style="width: 100%; border: 1px solid var(--line); background: var(--card); border-radius: 12px; padding: 0.8rem 1rem; font: inherit; color: inherit;">
        @error('password')
            <p class="no" style="margin: 0.6rem 0 0;">{{ $message }}</p>
        @enderror
        <button type="submit" style="margin-top: 1rem; width: 100%;">Login</button>
    </form>
@endsection
