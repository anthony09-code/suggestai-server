<?php
namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\AnalyzeOfficeRequest;
use App\Http\Resources\AnalysisSessionResource;
use App\Models\AnalysisSession;
use App\Models\Feedback;
use App\Models\Office;
use App\Services\BertopicService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AnalysisSessionController extends Controller
{
    public function __construct(private BertopicService $bertopicService) {}

    public function index(): JsonResponse
    {
        $sessions = AnalysisSession::query()
            ->with(["office", "user"])
            ->orderBy("created_at", "desc")
            ->get();

        return $this->success("Sessions retrieved.", [
            "data" => AnalysisSessionResource::collection($sessions),
        ]);
    }

    public function get_by_office(Office $office): JsonResponse
    {
        $sessions = AnalysisSession::query()
            ->with(["user"])
            ->where("office_id", $office->id)
            ->orderBy("created_at", "desc")
            ->get();

        return $this->success("Sessions retrieved.", [
            "data" => AnalysisSessionResource::collection($sessions),
        ]);
    }

    public function show(AnalysisSession $session): JsonResponse
    {
        $session->load(["office", "user", "topics", "topicResults.feedback"]);

        return $this->success("Session retrieved.", [
            "data" => new AnalysisSessionResource($session),
        ]);
    }

    public function analyze(
        AnalyzeOfficeRequest $request,
        Office $office,
    ): JsonResponse {
        $feedbacks = Feedback::query()
            ->where("office_id", $office->id)
            ->where("status", "pending")
            ->get();

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
            "status" => "processing",
            "started_at" => now(),
        ]);

        try {
            $result = $this->bertopicService->analyze(
                officeId: $office->id,
                sessionId: $session->id,
                documents: $feedbacks->pluck("raw_text")->toArray(),
                feedbackIds: $feedbacks->pluck("id")->toArray(),
            );

            $session->update([
                "topic_count" => $result["topic_count"],
                "status" => "completed",
                "completed_at" => now(),
            ]);

            return $this->success(
                "Analysis completed. {$result["topic_count"]} topics found from {$feedbacks->count()} feedbacks.",
                ["data" => $session->fresh(["topics", "nlpResults"])],
                201,
            );
        } catch (\Exception $e) {
            $session->update([
                "status" => "failed",
                "error_message" => $e->getMessage(),
                "completed_at" => now(),
            ]);

            Log::error("Analysis failed", [
                "office_id" => $office->id,
                "session_id" => $session->id,
                "error" => $e->getMessage(),
            ]);

            return $this->error(
                "Analysis failed. Please try again later.",
                500,
            );
        }
    }

    public function delete(AnalysisSession $session): JsonResponse
    {
        $session->delete();

        return $this->success("Session deleted successfully.");
    }
}
