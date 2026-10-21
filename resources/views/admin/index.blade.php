@extends('layouts.app')

@section('title', 'Admin Devices')

@section('content')
    <div class="row">
        <div>
            <h1 style="margin: 0;">Devices</h1>
            <p class="meta">Total devices: {{ $totalDevices }} · Timezone: Asia/Bangkok</p>
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
                    <th>SR No</th>
                    <th>Device</th>
                    <th>Last active</th>
                    <th>ကျက်ပြီး</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($devices as $device)
                    <tr>
                        <td>{{ $devices->firstItem() + $loop->index }}</td>
                        <td><code title="{{ $device->device }}">{{ \Illuminate\Support\Str::limit($device->device, 13, '…') }}</code></td>
                        <td>{{ $device->last_active_at?->timezone('Asia/Bangkok')->format('Y-m-d h:i A') ?? '—' }}</td>
                        <td>{{ $device->learned_count }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="meta">No devices yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($devices->hasPages())
        <nav class="admin-pager" aria-label="Pagination">
            @if ($devices->onFirstPage())
                <span class="button ghost" aria-disabled="true">← အရင်</span>
            @else
                <a class="button ghost" href="{{ $devices->previousPageUrl() }}">← အရင်</a>
            @endif

            <div class="admin-pages">
                @foreach ($devices->getUrlRange(1, $devices->lastPage()) as $page => $url)
                    <a href="{{ $url }}" @class(['button', 'ghost' => $page !== $devices->currentPage()])>{{ $page }}</a>
                @endforeach
            </div>

            @if ($devices->hasMorePages())
                <a class="button ghost" href="{{ $devices->nextPageUrl() }}">နောက် →</a>
            @else
                <span class="button ghost" aria-disabled="true">နောက် →</span>
            @endif
        </nav>
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
            vertical-align: top;
        }
        .admin-table th { color: var(--muted); font-weight: 600; }
        .admin-table code {
            font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
            font-size: 0.85rem;
        }
        .admin-pager {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
            margin-top: 1rem;
        }
        .admin-pages {
            display: flex;
            flex-wrap: wrap;
            gap: 0.35rem;
            justify-content: center;
        }
        .admin-pages .button {
            min-width: 2.4rem;
            padding: 0.55rem 0.7rem;
            text-align: center;
        }
        .admin-pager [aria-disabled="true"] {
            opacity: 0.45;
            pointer-events: none;
        }
    </style>
@endsection
