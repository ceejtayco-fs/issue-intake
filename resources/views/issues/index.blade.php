@extends('layouts.app')
@section('title', 'Issues')

@section('content')
    @php
        $priorityClasses = [
            'low'      => 'bg-slate-100 text-slate-700 ring-slate-200',
            'medium'   => 'bg-blue-50 text-blue-700 ring-blue-200',
            'high'     => 'bg-amber-50 text-amber-800 ring-amber-200',
            'critical' => 'bg-red-50 text-red-700 ring-red-200',
        ];
        $statusClasses = [
            'open'        => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
            'in_progress' => 'bg-indigo-50 text-indigo-700 ring-indigo-200',
            'resolved'    => 'bg-slate-100 text-slate-700 ring-slate-200',
            'closed'      => 'bg-slate-100 text-slate-500 ring-slate-200',
        ];
        $summaryClasses = [
            'pending' => 'bg-amber-50 text-amber-800 ring-amber-200',
            'ready'   => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
            'failed'  => 'bg-red-50 text-red-700 ring-red-200',
        ];
    @endphp

    <div class="flex items-end justify-between mb-6 flex-wrap gap-4">
        <div>
            <h1 class="text-2xl font-semibold">
                @if (($filters['trashed'] ?? null) === 'only') Trash @else Issues @endif
            </h1>
            <p class="text-sm text-slate-500 mt-1">{{ $issues->total() }} total</p>
        </div>

        <form method="GET" action="{{ route('issues.index') }}" class="flex flex-wrap items-end gap-2">
            @if (($filters['trashed'] ?? null))
                <input type="hidden" name="trashed" value="{{ $filters['trashed'] }}">
            @endif

            <div>
                <label class="block text-xs text-slate-500 mb-1">Status</label>
                <select name="status" class="rounded-md border-slate-300 text-sm px-2 py-1.5">
                    <option value="">all</option>
                    @foreach ($statuses as $s)
                        <option value="{{ $s->value }}" @selected(($filters['status'] ?? '') === $s->value)>{{ $s->value }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs text-slate-500 mb-1">Priority</label>
                <select name="priority" class="rounded-md border-slate-300 text-sm px-2 py-1.5">
                    <option value="">all</option>
                    @foreach ($priorities as $p)
                        <option value="{{ $p->value }}" @selected(($filters['priority'] ?? '') === $p->value)>{{ $p->value }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs text-slate-500 mb-1">Category</label>
                <select name="category" class="rounded-md border-slate-300 text-sm px-2 py-1.5">
                    <option value="">all</option>
                    @foreach ($categories as $c)
                        <option value="{{ $c->value }}" @selected(($filters['category'] ?? '') === $c->value)>{{ $c->value }}</option>
                    @endforeach
                </select>
            </div>

            <label class="inline-flex items-center gap-2 text-sm">
                <input type="checkbox" name="is_escalated" value="1"
                       @checked(($filters['is_escalated'] ?? '') === '1')
                       class="rounded border-slate-300">
                <span>Escalated only</span>
            </label>

            <div>
                <label class="block text-xs text-slate-500 mb-1">Search</label>
                <input name="q" value="{{ $filters['q'] ?? '' }}" placeholder="title or description"
                       class="rounded-md border-slate-300 text-sm px-2 py-1.5 w-56">
            </div>

            <button type="submit" class="rounded-md bg-indigo-600 text-white text-sm px-3 py-1.5 hover:bg-indigo-500">Apply</button>
            <a href="{{ route('issues.index') }}" class="text-sm text-slate-500 hover:text-slate-800 px-2 py-1.5">Reset</a>
        </form>
    </div>

    <div class="bg-white border border-slate-200 rounded-lg overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-slate-600 text-xs uppercase tracking-wide">
                <tr>
                    <th class="px-4 py-3 text-left">ID</th>
                    <th class="px-4 py-3 text-left">Title</th>
                    <th class="px-4 py-3 text-left">Priority</th>
                    <th class="px-4 py-3 text-left">Category</th>
                    <th class="px-4 py-3 text-left">Status</th>
                    <th class="px-4 py-3 text-left">Summary</th>
                    <th class="px-4 py-3 text-left">Flags</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($issues as $issue)
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-3 text-slate-500">#{{ $issue->id }}</td>
                        <td class="px-4 py-3 font-medium">
                            <a href="{{ route('issues.show', $issue->id) }}" class="text-indigo-700 hover:underline">
                                {{ \Illuminate\Support\Str::limit($issue->title, 60) }}
                            </a>
                        </td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center rounded px-2 py-0.5 text-xs font-medium ring-1 ring-inset {{ $priorityClasses[$issue->priority->value] }}">
                                {{ $issue->priority->value }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-slate-700">{{ $issue->category->value }}</td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center rounded px-2 py-0.5 text-xs ring-1 ring-inset {{ $statusClasses[$issue->status->value] }}">
                                {{ $issue->status->value }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center rounded px-2 py-0.5 text-xs ring-1 ring-inset {{ $summaryClasses[$issue->summary_status->value] }}">
                                {{ $issue->summary_status->value }}
                            </span>
                        </td>
                        <td class="px-4 py-3 space-x-1">
                            @if ($issue->is_escalated)
                                <span class="inline-flex items-center rounded px-2 py-0.5 text-xs font-medium bg-red-50 text-red-700 ring-1 ring-inset ring-red-200">⚑ escalated</span>
                            @endif
                            @if ($issue->trashed())
                                <span class="inline-flex items-center rounded px-2 py-0.5 text-xs bg-slate-100 text-slate-600 ring-1 ring-inset ring-slate-200">trashed</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-10 text-center text-slate-500">No issues match the current filters.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $issues->links() }}
    </div>
@endsection
