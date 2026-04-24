<?php

namespace App\Http\Controllers\Api;

use App\Enums\SummaryStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreIssueRequest;
use App\Http\Requests\UpdateIssueRequest;
use App\Http\Resources\IssueResource;
use App\Jobs\GenerateIssueSummary;
use App\Models\Issue;
use App\Services\Escalation\EscalationEvaluator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class IssueController extends Controller
{
    public function __construct(private readonly EscalationEvaluator $escalator)
    {
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        $trashedMode = $request->query('trashed');
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
            'status' => $request->query('status'),
            'category' => $request->query('category'),
            'priority' => $request->query('priority'),
            'is_escalated' => $request->query('is_escalated'),
            'q' => $request->query('q'),
        ]);

        $query->latest('id');

        return IssueResource::collection($query->paginate($perPage)->withQueryString());
    }

    public function store(StoreIssueRequest $request): JsonResponse
    {
        $issue = Issue::create(array_merge($request->validated(), [
            'summary_status' => SummaryStatus::Pending,
        ]));

        $this->escalator->apply($issue->fresh());

        GenerateIssueSummary::dispatch($issue->id);

        return IssueResource::make($issue->fresh())
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(int $id): IssueResource
    {
        $issue = Issue::findOrFail($id);

        return IssueResource::make($issue);
    }

    public function update(UpdateIssueRequest $request, int $id): IssueResource
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

        return IssueResource::make($issue->fresh());
    }

    public function destroy(int $id): Response
    {
        $issue = Issue::findOrFail($id);
        $issue->delete();

        return response()->noContent();
    }

    public function restore(int $id): IssueResource
    {
        $issue = Issue::onlyTrashed()->findOrFail($id);
        $issue->restore();

        return IssueResource::make($issue->fresh());
    }

    public function regenerateSummary(int $id): IssueResource
    {
        $issue = Issue::findOrFail($id);
        $issue->forceFill(['summary_status' => SummaryStatus::Pending])->save();

        GenerateIssueSummary::dispatch($issue->id);

        return IssueResource::make($issue->fresh());
    }
}
