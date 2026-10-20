@extends('layouts.app')

@section('title', 'Admin Devices')

@section('content')
    <div class="row">
        <div>
            <h1 style="margin: 0;">Devices</h1>
            <p class="meta">Total devices: {{ $totalDevices }}</p>
        </div>
        <form method="post" action="{{ route('admin.logout') }}">
            @csrf
            <button type="submit" class="ghost">Logout</button>
        </form>
    </div>

    <div class="panel" style="margin-top: 1.25rem; overflow-x: auto;">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Device</th>
                    <th>Last active</th>
                    <th>ကျက်ပြီး</th>
                    <th>Marked</th>
                    <th>First seen</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($devices as $device)
                    <tr>
                        <td>{{ $device->id }}</td>
                        <td><code title="{{ $device->device }}">{{ \Illuminate\Support\Str::limit($device->device, 13, '…') }}</code></td>
                        <td>{{ $device->last_active_at?->timezone(config('app.timezone'))->format('Y-m-d H:i') ?? '—' }}</td>
                        <td>{{ $device->learned_count }}</td>
                        <td>{{ $device->marked_count }}</td>
                        <td>{{ $device->created_at?->timezone(config('app.timezone'))->format('Y-m-d H:i') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="meta">No devices yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($devices->hasPages())
        <div class="row" style="margin-top: 1rem;">
            @if ($devices->onFirstPage())
                <span></span>
            @else
                <a class="button ghost" href="{{ $devices->previousPageUrl() }}">← အရင်</a>
            @endif
            <span class="meta">{{ $devices->currentPage() }} / {{ $devices->lastPage() }}</span>
            @if ($devices->hasMorePages())
                <a class="button ghost" href="{{ $devices->nextPageUrl() }}">နောက် →</a>
            @endif
        </div>
    @endif

    <style>
        .admin-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.95rem;
        }
        .admin-table th,
        .admin-table td {
            text-align: left;
            padding: 0.7rem 0.55rem;
            border-bottom: 1px solid var(--line);
            white-space: nowrap;
        }
        .admin-table th { color: var(--muted); font-weight: 600; }
        .admin-table code {
            font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
            font-size: 0.85rem;
        }
    </style>
@endsection
