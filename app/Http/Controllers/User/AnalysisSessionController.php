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
use App\Models\Topic;
use App\Services\CacheService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Filters\FeedbackFilter;
use Barryvdh\DomPDF\Facade\Pdf;

class AnalysisSessionController extends Controller
{
    private const CACHE_TTL = 300;

    public function __construct(private CacheService $cache) {}

    public function index(Request $request): JsonResponse
    {
        $page = $request->get("page", 1);
        $perPage = $request->get("per_page", 15);
        $status = $request->get("status");
        $key = "sessions.all.page.{$page}.per_page.{$perPage}.status.{$status}";

        $sessions = $this->cache->remember(
            $key,
            self::CACHE_TTL,
            function () use ($status, $perPage) {
                $paginator = AnalysisSession::query()
                    ->with(["office", "user"])
                    ->when($status, fn($q) => $q->where("status", $status))
                    ->orderBy("created_at", "desc")
                    ->paginate($perPage);

                return [
                    "data" => AnalysisSessionResource::collection(
                        $paginator,
                    )->resolve(),
                    "pagination" => PaginationMeta::make($paginator),
                ];
            },
        );

        return $this->success("Sessions retrieved.", $sessions);
    }

    public function get_by_office(
        Request $request,
        Office $office,
    ): JsonResponse {
        $page = $request->get("page", 1);
        $perPage = $request->get("per_page", 15);
        $status = $request->get("status");

        $paginator = AnalysisSession::query()
            ->with(["user", "office"])
            ->where("office_id", $office->id)
            ->when($status, fn($q) => $q->where("status", $status))
            ->orderBy("created_at", "desc")
            ->paginate($perPage);

        return $this->success("Sessions retrieved.", [
            "data" => AnalysisSessionResource::collection(
                $paginator,
            )->resolve(),
            "pagination" => PaginationMeta::make($paginator),
        ]);
    }

    public function show(AnalysisSession $session): JsonResponse
    {
        try {
            if (in_array($session->status, ["pending", "processing"])) {
                $session->load(["office", "user"]);
            } else {
                $session->load([
                    "office",
                    "user",
                    "topics.topicResults.feedback",
                ]);
            }

            return $this->success("Session retrieved.", [
                "data" => new AnalysisSessionResource($session),
            ]);
        } catch (\ErrorException $e) {
            Log::error("Resource error", [
                "message" => $e->getMessage(),
                "file" => $e->getFile(),
                "line" => $e->getLine(),
            ]);

            return response()->json(
                [
                    "error" => $e->getMessage(),
                    "file" => $e->getFile(),
                    "line" => $e->getLine(),
                ],
                500,
            );
        }
    }

    public function analyze(
        AnalyzeOfficeRequest $request,
        Office $office,
    ): JsonResponse {
        $query = Feedback::query()
            ->where("office_id", $office->id)
            ->readyForAnalysis();

        $filters = [
            "date" => $request->input("date", "all"),
            "status" => $request->input("status", "pending"),
            "anonymous" => $request->input("anonymous", "all"),
            "date_from" => $request->input("date_from"),
            "date_to" => $request->input("date_to"),
        ];

        $feedbacks = FeedbackFilter::apply($query, $filters)->get();

        if ($feedbacks->isEmpty()) {
            return $this->error(
                "No pending feedback found for this office.",
                422,
            );
        }

        $session = AnalysisSession::create([
            "office_id" => $office->id,
            "user_id" => \Illuminate\Support\Facades\Auth::id(),
            "feedback_count" => $feedbacks->count(),
            "topic_count" => 0,
            "status" => "pending",
            "date_from" => $request->input("date_from") ?? now()->toDateTimeString(),
            "date_to" => $request->input("date_to") ?? now()->toDateTimeString(),
            "started_at" => now(),
        ]);

        ProcessBertopicAnalysis::dispatch(
            $session->id,
            $office->id,
            $feedbacks->pluck("id")->toArray(),
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

    public function stats(): JsonResponse
    {
        $data = $this->cache->remember(
            "sessions.stats",
            self::CACHE_TTL,
            fn() => [
                "total_sessions" => AnalysisSession::count(),

                "total_topics" => Topic::count(),

                "topics_per_office" => Office::withCount(
                    "topics as topics_count",
                )
                    ->where("is_active", true)
                    ->orderByDesc("topics_count")
                    ->get()
                    ->map(
                        fn($o) => [
                            "office_name" => $o->office_name,
                            "topic_count" => (int) $o->topics_count,
                        ],
                    ),

                "top_topics" => Topic::where("label", "!=", "Uncategorized")
                    ->orderByDesc("feedback_count")
                    ->limit(30)
                    ->get()
                    ->map(
                        fn($t) => [
                            "text" => $t->label,
                            "weight" => $t->feedback_count,
                        ],
                    ),

                "recent_sessions" => AnalysisSession::with("office")
                    ->orderByDesc("created_at")
                    ->limit(5)
                    ->get()
                    ->map(
                        fn($s) => [
                            "office" => $s->office?->office_name,
                            "feedback_count" => $s->feedback_count,
                            "topic_count" => $s->topic_count,
                            "status" => $s->status,
                            "started_at" => $s->started_at?->toISOString(),
                            "completed_at" => $s->completed_at?->toISOString(),
                        ],
                    ),
            ],
        );

        return $this->success("Session stats retrieved.", ["data" => $data]);
    }

    public function download(
        AnalysisSession $session,
    ): \Illuminate\Http\Response {
        if ($session->status !== "completed") {
            abort(422, "Session is not completed yet.");
        }

        $session->load(["office", "topics.topicResults.feedback"]);

        $topics = $session->topics->map(
            fn($topic) => [
                "id" => $topic->id,
                "label" => $topic->label,
                "keywords" => $topic->keywords,
                "feedback_count" => $topic->feedback_count,
                "sample_feedbacks" => $topic->topicResults
                    ->sortByDesc("confidence_score")
                    ->map(
                        fn($result) => [
                            "text" => $result->feedback?->raw_text,
                            "cleaned_text" => $result->cleaned_text,
                            "confidence_score" => round(
                                $result->confidence_score * 100,
                                1,
                            ),
                        ],
                    )
                    ->values(),
            ],
        );

        $report = [
            "session_id" => $session->id,
            "office" => $session->office?->office_name ?? "Unknown Office",
            "status" => $session->status,
            "feedback_count" => $session->feedback_count,
            "topic_count" => $session->topic_count,
            "started_at" => $session->started_at?->toISOString(),
            "completed_at" => $session->completed_at?->toISOString(),
            "topics" => $topics,
        ];

        $filename = str($session->office?->office_name ?? "report")
            ->slug()
            ->append("-analysis-" . now()->format("Y-m-d"))
            ->append(".pdf")
            ->toString();

        $pdf = Pdf::loadView("reports.session", [
            "report" => $report,
        ])->setPaper("a4", "portrait");

        return $pdf->download($filename);
    }
}
