<?php

namespace App\Http\Controllers\Web;

use App\Enums\IssueCategory;
use App\Enums\IssuePriority;
use App\Enums\IssueStatus;
use App\Enums\SummaryStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreIssueRequest;
use App\Http\Requests\UpdateIssueRequest;
use App\Jobs\GenerateIssueSummary;
use App\Models\Issue;
use App\Services\Escalation\EscalationEvaluator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class IssueWebController extends Controller
{
    public function __construct(private readonly EscalationEvaluator $escalator)
    {
    }

    public function index(Request $request): View
    {
        $trashedMode = $request->query('trashed');
        $statusFilter = $request->query('status');

        if (blank($statusFilter)) {
            $statusFilter = IssueStatus::Open->value;
        }

        $perPage = min((int) $request->query('per_page', 15), 100);
        if ($perPage < 1) {
            $perPage = 15;
        }

        $query = Issue::query();

        if ($trashedMode === 'only') {
            $query->onlyTrashed();
        } elseif ($trashedMode === 'with') {
            $query->withTrashed();
        }

        $query->filter([
            'status' => $statusFilter,
            'category' => $request->query('category'),
            'priority' => $request->query('priority'),
            'is_escalated' => $request->query('is_escalated'),
            'q' => $request->query('q'),
        ]);

        $issues = $query->latest('id')->paginate($perPage)->withQueryString();

        return view('issues.index', [
            'issues' => $issues,
            'filters' => array_merge(
                $request->only(['status', 'category', 'priority', 'is_escalated', 'q', 'trashed']),
                ['status' => $statusFilter],
            ),
            'priorities' => IssuePriority::cases(),
            'categories' => IssueCategory::cases(),
            'statuses' => IssueStatus::cases(),
        ]);
    }

    public function create(): View
    {
        return view('issues.create', [
            'priorities' => IssuePriority::cases(),
            'categories' => IssueCategory::cases(),
        ]);
    }

    public function store(StoreIssueRequest $request): RedirectResponse
    {
        $issue = Issue::create(array_merge($request->validated(), [
            'summary_status' => SummaryStatus::Pending,
        ]));

        $this->escalator->apply($issue->fresh());
        GenerateIssueSummary::dispatch($issue->id);

        return redirect()
            ->route('issues.show', $issue->id)
            ->with('flash', ['type' => 'success', 'message' => 'Issue submitted. Summary is being generated.']);
    }

    public function show(int $id): View
    {
        $issue = Issue::withTrashed()
            ->with(['summaries' => fn ($q) => $q->latest('id')])
            ->findOrFail($id);

        return view('issues.show', [
            'issue' => $issue,
            'statuses' => IssueStatus::cases(),
            'priorities' => IssuePriority::cases(),
            'categories' => IssueCategory::cases(),
        ]);
    }

    public function update(UpdateIssueRequest $request, int $id): RedirectResponse
    {
        $issue = Issue::findOrFail($id);

        $descriptionChanged = $request->filled('description')
            && $request->input('description') !== $issue->description;

        $issue->fill($request->validated());
        if ($descriptionChanged) {
            $issue->summary_status = SummaryStatus::Pending;
        }
        $issue->save();

        $this->escalator->apply($issue->fresh());

        if ($descriptionChanged) {
            GenerateIssueSummary::dispatch($issue->id);
        }

        return redirect()
            ->route('issues.show', $issue->id)
            ->with('flash', ['type' => 'success', 'message' => 'Issue updated.']);
    }

    public function destroy(int $id): RedirectResponse
    {
        $issue = Issue::findOrFail($id);
        $issue->delete();

        return redirect()
            ->route('issues.index')
            ->with('flash', ['type' => 'success', 'message' => 'Issue deleted.']);
    }

    public function restore(int $id): RedirectResponse
    {
        $issue = Issue::onlyTrashed()->findOrFail($id);
        $issue->restore();

        return redirect()
            ->route('issues.show', $issue->id)
            ->with('flash', ['type' => 'success', 'message' => 'Issue restored.']);
    }

    public function regenerate(int $id): RedirectResponse
    {
        $issue = Issue::findOrFail($id);
        $issue->forceFill(['summary_status' => SummaryStatus::Pending])->save();

        GenerateIssueSummary::dispatch($issue->id);

        return redirect()
            ->route('issues.show', $issue->id)
            ->with('flash', ['type' => 'success', 'message' => 'Summary regeneration queued.']);
    }
}
