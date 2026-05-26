<?php
namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Pagination\PaginationMeta;
use App\Http\Requests\User\AnalyzeOfficeRequest;
use App\Http\Resources\AnalysisSessionResource;
use App\Jobs\ProcessBertopicAnalysis;
use App\Models\AnalysisSession;
use App\Models\Feedback;
use App\Models\Office;
use App\Services\BertopicService;
use App\Services\CacheService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Filters\FeedbackFilter;

class AnalysisSessionController extends Controller
{
    private const CACHE_TTL = 300;

    public function __construct(
        private BertopicService $bertopicService,
        private CacheService $cache,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $page = $request->get("page", 1);
        $perPage = $request->get("per_page", 15);
        $status = $request->get("status");
        $key = "sessions.all.page.{$page}.per_page.{$perPage}.status.{$status}";

        $sessions = $this->cache->remember(
            $key,
            self::CACHE_TTL,
            fn() => AnalysisSession::query()
                ->with(["office", "user"])
                ->when($status, fn($q) => $q->where("status", $status))
                ->orderBy("created_at", "desc")
                ->paginate($perPage),
        );

        return $this->success("Sessions retrieved.", [
            "data" => AnalysisSessionResource::collection($sessions),
            "pagination" => PaginationMeta::make($sessions),
        ]);
    }

    public function get_by_office(
        Request $request,
        Office $office,
    ): JsonResponse {
        $page = $request->get("page", 1);
        $perPage = $request->get("per_page", 15);
        $status = $request->get("status");
        $key = "sessions.office.{$office->id}.page.{$page}.per_page.{$perPage}.status.{$status}";

        $sessions = $this->cache->remember(
            $key,
            self::CACHE_TTL,
            fn() => AnalysisSession::query()
                ->with(["user"])
                ->where("office_id", $office->id)
                ->when($status, fn($q) => $q->where("status", $status))
                ->orderBy("created_at", "desc")
                ->paginate($perPage),
        );

        return $this->success("Sessions retrieved.", [
            "data" => AnalysisSessionResource::collection($sessions),
            "pagination" => PaginationMeta::make($sessions),
        ]);
    }

    public function show(AnalysisSession $session): JsonResponse
    {
        $key = "sessions.{$session->id}";

        $data = $this->cache->remember(
            $key,
            self::CACHE_TTL,
            fn() => $session->load([
                "office",
                "user",
                "topics",
                "topicResults.feedback",
            ]),
        );

        return $this->success("Session retrieved.", [
            "data" => new AnalysisSessionResource($data),
        ]);
    }

    public function analyze(
        AnalyzeOfficeRequest $request,
        Office $office,
    ): JsonResponse {
        $query = Feedback::query()->where("office_id", $office->id);

        $filters = [
            "date" => $request->input("date", "all"),
            "status" => $request->input("status", "pending"),
            "anonymous" => $request->input("anonymous", "all"),
        ];

        $feedbacks = FeedbackFilter::apply($query, $filters)->get();

        if ($feedbacks->isEmpty()) {
            return $this->error(
                "No pending feedbacks found for this office.",
                422,
            );
        }

        $session = AnalysisSession::create([
            "office_id" => $office->id,
            "user_id" => auth()->id(),
            "feedback_count" => $feedbacks->count(),
            "topic_count" => 0,
            "status" => "pending",
            "date_from" => $request->input(
                "date_from",
                now()->toDateTimeString(),
            ),
            "date_to" => $request->input("date_to", now()->toDateTimeString()),
            "started_at" => now(),
        ]);

        ProcessBertopicAnalysis::dispatch(
            session: $session,
            office: $office,
            feedbackIds: $feedbacks->pluck("id")->toArray(),
        );

        return $this->success(
            "Analysis queued. {$feedbacks->count()} feedbacks will be processed shortly.",
            ["data" => new AnalysisSessionResource($session)],
            202,
        );
    }

    public function delete(AnalysisSession $session): JsonResponse
    {
        $officeId = $session->office_id;

        $session->delete();

        $this->cache->forget_many([
            "sessions.{$session->id}",
            "dashboard.overview",
            "dashboard.office.{$officeId}",
        ]);

        $this->cache->forget_pattern("sessions.");

        return $this->success("Session deleted successfully.");
    }
}
