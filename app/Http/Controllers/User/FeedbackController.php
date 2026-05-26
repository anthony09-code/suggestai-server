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
}
