<?php
namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Pagination\PaginationMeta;
use App\Http\Resources\FeedbackResource;
use App\Models\Feedback;
use App\Models\Office;
use App\Services\CacheService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Filters\FeedbackFilter;

class FeedbackController extends Controller
{
    private const CACHE_TTL = 300;

    public function __construct(private CacheService $cache) {}

    public function index(Request $request): JsonResponse
    {
        $page = $request->input("page", 1);
        $perPage = $request->input("per_page", 15);

        $filters = $request->only(["date", "status", "anonymous"]);
        $filterHash = md5(json_encode($filters));

        $key = "feedbacks.all.page.{$page}.per_page.{$perPage}.filter.{$filterHash}";

        $feedbacks = $this->cache->remember(
            $key,
            self::CACHE_TTL,
            function () use ($filters, $perPage) {
                $query = Feedback::query()->with(["office", "student"]);

                $paginator = FeedbackFilter::apply($query, $filters)
                    ->orderBy("created_at", "desc")
                    ->paginate($perPage);

                return [
                    "data" => FeedbackResource::collection(
                        $paginator,
                    )->resolve(),
                    "pagination" => PaginationMeta::make($paginator),
                ];
            },
        );
        return $this->success("Feedbacks retrieved.", $feedbacks);
    }

    public function get_by_office(
        Request $request,
        Office $office,
    ): JsonResponse {
        $page = $request->input("page", 1);
        $perPage = $request->input("per_page", 15);

        $filters = $request->only(["date", "status", "anonymous"]);
        $filterHash = md5(json_encode($filters));

        $key = "feedbacks.office.{$office->id}.page.{$page}.per_page.{$perPage}.filter.{$filterHash}";

        $feedbacks = $this->cache->remember(
            $key,
            self::CACHE_TTL,
            function () use ($office, $filters, $perPage) {
                $query = Feedback::query()
                    ->select([
                        "id",
                        "student_id",
                        "office_id",
                        "status",
                        "is_anonymous",
                        "raw_text",
                        "created_at",
                    ])
                    ->with([
                        "office:id,office_name,access_link",
                        "student:id,name,email,profile_picture",
                    ])
                    ->where("office_id", $office->id);

                $paginator = FeedbackFilter::apply($query, $filters)
                    ->orderBy("created_at", "desc")
                    ->paginate($perPage);

                return [
                    "data" => FeedbackResource::collection(
                        $paginator,
                    )->resolve(),
                    "pagination" => PaginationMeta::make($paginator),
                ];
            },
        );

        return $this->success("Feedbacks retrieved.", $feedbacks);
    }

    public function show(Feedback $feedback): JsonResponse
    {
        $data = $this->cache->remember(
            "feedbacks.{$feedback->id}",
            self::CACHE_TTL,
            function () use ($feedback) {
                $feedback->load(["office", "student"]);
                return new FeedbackResource($feedback)->resolve();
            },
        );
        return $this->success("Feedback retrieved.", ["data" => $data]);
    }

    public function delete_feedback(Feedback $feedback): JsonResponse
    {
        $officeId = $feedback->office_id;

        $feedback->delete();

        $this->cache->forget_pattern("feedbacks.");
        $this->cache->forget("dashboard.office.{$officeId}");
        $this->cache->forget("dashboard.overview");

        return $this->success("Feedback deleted successfully.");
    }

    public function stats(): JsonResponse
    {
        $data = $this->cache->remember(
            "feedbacks.stats",
            self::CACHE_TTL,
            function () {
                $counts = DB::table("feedbacks")
                    ->selectRaw(
                        "
                        COUNT(*) as total,
                        SUM(CASE WHEN status = 'processed' THEN 1 ELSE 0 END) as processed,
                        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                        SUM(CASE WHEN is_anonymous = true THEN 1 ELSE 0 END) as anonymous,
                        SUM(CASE WHEN is_anonymous = false THEN 1 ELSE 0 END) as identified
                    ",
                    )
                    ->first();

                return [
                    "total_feedbacks" => (int) $counts->total,
                    "total_pending" => (int) $counts->pending,
                    "total_processed" => (int) $counts->processed,
                    "status_breakdown" => [
                        "processed" => (int) $counts->processed,
                        "pending" => (int) $counts->pending,
                    ],
                    "anonymous_breakdown" => [
                        "anonymous" => (int) $counts->anonymous,
                        "identified" => (int) $counts->identified,
                    ],
                    "feedback_trend" => Feedback::selectRaw(
                        "TO_CHAR(created_at, 'Mon') as month,
                         EXTRACT(MONTH FROM created_at) as month_num,
                         COUNT(*) as count",
                    )
                        ->groupByRaw("1, 2")
                        ->orderByRaw("2")
                        ->get()
                        ->map(
                            fn($r) => [
                                "month" => $r->month,
                                "count" => (int) $r->count,
                            ],
                        ),
                    "feedbacks_per_office" => Office::withCount("feedbacks")
                        ->where("is_active", true)
                        ->orderByDesc("feedbacks_count")
                        ->get()
                        ->map(
                            fn($o) => [
                                "office_name" => $o->office_name,
                                "feedback_count" => $o->feedbacks_count,
                                "color" => $o->color,
                            ],
                        ),
                ];
            },
        );

        return $this->success("Feedback stats retrieved.", ["data" => $data]);
    }
}
