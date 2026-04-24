@extends('layouts.app')
@section('title', 'Issue #' . $issue->id)

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

    <div class="flex items-start justify-between mb-6 gap-4 flex-wrap">
        <div>
            <a href="{{ route('issues.index') }}" class="text-sm text-slate-500 hover:text-slate-800">&larr; Back to list</a>
            <h1 class="text-2xl font-semibold mt-1">
                <span class="text-slate-400">#{{ $issue->id }}</span> {{ $issue->title }}
            </h1>
            <div class="mt-2 flex flex-wrap items-center gap-2">
                <span class="inline-flex items-center rounded px-2 py-0.5 text-xs font-medium ring-1 ring-inset {{ $priorityClasses[$issue->priority->value] }}">priority: {{ $issue->priority->value }}</span>
                <span class="inline-flex items-center rounded px-2 py-0.5 text-xs ring-1 ring-inset {{ $statusClasses[$issue->status->value] }}">status: {{ $issue->status->value }}</span>
                <span class="inline-flex items-center rounded px-2 py-0.5 text-xs bg-slate-100 text-slate-700 ring-1 ring-inset ring-slate-200">category: {{ $issue->category->value }}</span>
                @if ($issue->is_escalated)
                    <span class="inline-flex items-center rounded px-2 py-0.5 text-xs font-medium bg-red-50 text-red-700 ring-1 ring-inset ring-red-200">⚑ escalated ({{ $issue->escalation_reason }})</span>
                @endif
                @if ($issue->trashed())
                    <span class="inline-flex items-center rounded px-2 py-0.5 text-xs bg-slate-100 text-slate-600 ring-1 ring-inset ring-slate-200">trashed</span>
                @endif
            </div>
        </div>

        <div class="flex gap-2">
            @if ($issue->trashed())
                <form method="POST" action="{{ route('issues.restore', $issue->id) }}">
                    @csrf
                    <button type="submit" class="rounded-md bg-emerald-600 px-3 py-2 text-sm text-white hover:bg-emerald-500">Restore</button>
                </form>
            @else
                <form method="POST" action="{{ route('issues.regenerate', $issue->id) }}">
                    @csrf
                    <button type="submit" class="rounded-md bg-slate-800 px-3 py-2 text-sm text-white hover:bg-slate-700">Regenerate summary</button>
                </form>
                <form method="POST" action="{{ route('issues.destroy', $issue->id) }}"
                      onsubmit="return confirm('Delete this issue? It will be recoverable from Trash.');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="rounded-md bg-red-600 px-3 py-2 text-sm text-white hover:bg-red-500">Delete</button>
                </form>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <section class="bg-white border border-slate-200 rounded-lg p-5">
                <h2 class="text-sm font-semibold text-slate-500 uppercase tracking-wide mb-2">Description</h2>
                <p class="text-sm whitespace-pre-wrap leading-relaxed">{{ $issue->description }}</p>
            </section>

            <section class="bg-white border border-slate-200 rounded-lg p-5">
                <div class="flex items-center justify-between mb-3">
                    <h2 class="text-sm font-semibold text-slate-500 uppercase tracking-wide">AI summary</h2>
                    <span class="inline-flex items-center rounded px-2 py-0.5 text-xs ring-1 ring-inset {{ $summaryClasses[$issue->summary_status->value] }}">
                        {{ $issue->summary_status->value }}
                    </span>
                </div>
                @if ($issue->summary_status->value === 'ready')
                    <p class="text-sm mb-3"><span class="font-medium">Summary:</span> {{ $issue->summary }}</p>
                    <p class="text-sm"><span class="font-medium">Suggested next action:</span> {{ $issue->next_action }}</p>
                @elseif ($issue->summary_status->value === 'pending')
                    <p class="text-sm text-slate-500">A summary is being generated. Refresh the page in a moment, or make sure the queue worker is running: <code class="text-xs bg-slate-100 px-1.5 py-0.5 rounded">php artisan queue:work</code></p>
                @else
                    <p class="text-sm text-red-600">Summary generation failed after retries.</p>
                @endif
            </section>

            <section class="bg-white border border-slate-200 rounded-lg p-5">
                <h2 class="text-sm font-semibold text-slate-500 uppercase tracking-wide mb-3">Update status</h2>
                <form method="POST" action="{{ route('issues.update', $issue->id) }}" class="flex items-end gap-3">
                    @csrf
                    @method('PATCH')
                    <div>
                        <label class="block text-xs text-slate-500 mb-1">Status</label>
                        <select name="status" class="rounded-md border-slate-300 text-sm px-3 py-1.5">
                            @foreach ($statuses as $s)
                                <option value="{{ $s->value }}" @selected($s === $issue->status)>{{ $s->value }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs text-slate-500 mb-1">Priority</label>
                        <select name="priority" class="rounded-md border-slate-300 text-sm px-3 py-1.5">
                            @foreach ($priorities as $p)
                                <option value="{{ $p->value }}" @selected($p === $issue->priority)>{{ $p->value }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="rounded-md bg-indigo-600 px-3 py-1.5 text-sm text-white hover:bg-indigo-500">Save</button>
                </form>
            </section>
        </div>

        <aside class="space-y-6">
            <section class="bg-white border border-slate-200 rounded-lg p-5">
                <h2 class="text-sm font-semibold text-slate-500 uppercase tracking-wide mb-3">Meta</h2>
                <dl class="text-sm space-y-2">
                    <div class="flex justify-between"><dt class="text-slate-500">Created</dt><dd>{{ $issue->created_at?->diffForHumans() }}</dd></div>
                    <div class="flex justify-between"><dt class="text-slate-500">Updated</dt><dd>{{ $issue->updated_at?->diffForHumans() }}</dd></div>
                    @if ($issue->due_at)
                        <div class="flex justify-between"><dt class="text-slate-500">Due</dt><dd>{{ $issue->due_at->format('Y-m-d H:i') }}</dd></div>
                    @endif
                    @if ($issue->escalated_at)
                        <div class="flex justify-between"><dt class="text-slate-500">Escalated</dt><dd>{{ $issue->escalated_at->diffForHumans() }}</dd></div>
                    @endif
                </dl>
            </section>

            <section class="bg-white border border-slate-200 rounded-lg p-5">
                <h2 class="text-sm font-semibold text-slate-500 uppercase tracking-wide mb-3">Summary history</h2>
                @if ($issue->summaries->isEmpty())
                    <p class="text-sm text-slate-500">No attempts yet.</p>
                @else
                    <ul class="space-y-3 text-sm">
                        @foreach ($issue->summaries as $attempt)
                            <li class="border-l-2 {{ $attempt->status === 'succeeded' ? 'border-emerald-400' : 'border-red-400' }} pl-3">
                                <div class="flex justify-between items-baseline">
                                    <span class="font-medium">{{ $attempt->driver }}{{ $attempt->model ? ' · ' . $attempt->model : '' }}</span>
                                    <span class="text-xs text-slate-500">{{ $attempt->created_at?->diffForHumans() }}</span>
                                </div>
                                <div class="text-xs text-slate-500">
                                    status: {{ $attempt->status }}
                                    @if ($attempt->latency_ms) · {{ $attempt->latency_ms }}ms @endif
                                    @if ($attempt->prompt_tokens !== null) · {{ $attempt->prompt_tokens }}+{{ $attempt->completion_tokens }} tok @endif
                                </div>
                                @if ($attempt->error)
                                    <p class="text-xs text-red-600 mt-1">{{ \Illuminate\Support\Str::limit($attempt->error, 200) }}</p>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                @endif
            </section>
        </aside>
    </div>
@endsection
