@extends('layouts.app')

@section('title', 'Device URLs')

@section('content')
    <div class="row">
        <div>
            <h1 style="margin: 0;">URL access</h1>
            <p class="meta">
                Device: <code>{{ $user->device }}</code>
                · Timezone: Asia/Bangkok
            </p>
        </div>
        <a class="button ghost" href="{{ route('admin.index') }}">← Devices</a>
    </div>

    <div class="panel" style="margin-top: 1.25rem; overflow-x: auto;">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>SR No</th>
                    <th>URL</th>
                    <th>Time</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($visits as $visit)
                    <tr>
                        <td>{{ $visits->firstItem() + $loop->index }}</td>
                        <td><code>{{ $visit->path }}</code></td>
                        <td>{{ $visit->visited_at->timezone('Asia/Bangkok')->format('Y-m-d h:i A') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="meta">No URL visits yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($visits->hasPages())
        <nav class="admin-pager" aria-label="Pagination">
            @if ($visits->onFirstPage())
                <span class="button ghost" aria-disabled="true">← အရင်</span>
            @else
                <a class="button ghost" href="{{ $visits->previousPageUrl() }}">← အရင်</a>
            @endif

            <div class="admin-pages">
                @foreach ($visits->getUrlRange(1, $visits->lastPage()) as $page => $url)
                    <a href="{{ $url }}" @class(['button', 'ghost' => $page !== $visits->currentPage()])>{{ $page }}</a>
                @endforeach
            </div>

            @if ($visits->hasMorePages())
                <a class="button ghost" href="{{ $visits->nextPageUrl() }}">နောက် →</a>
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
        }
        .admin-table th { color: var(--muted); font-weight: 600; }
        .admin-table code {
            font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
            font-size: 0.85rem;
            white-space: normal;
            word-break: break-all;
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
