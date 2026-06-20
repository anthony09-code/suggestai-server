<?php

namespace App\Jobs;

use App\Models\AnalysisSession;
use App\Models\Feedback;
use App\Models\Office;
use App\Services\BertopicService;
use App\Services\CacheService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessBertopicAnalysis implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 700;

    public int $backoff = 60;

    /**
     * @param string[] $feedbackIds
     */
    public function __construct(
        private string $sessionId,
        private string $officeId,
        private array $feedbackIds,
    ) {
        $this->onQueue("analysis");
    }

    /**
     * Execute the job.
     */
    public function handle(
        BertopicService $bertopicService,
        CacheService $cache,
    ): void {
        $session = AnalysisSession::findOrFail($this->sessionId);
        $office = Office::findOrFail($this->officeId);

        $feedbacks = Feedback::query()
            ->whereIn("id", $this->feedbackIds)
            ->get();

        try {
            $result = $bertopicService->analyze(
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

            Feedback::query()
                ->whereIn("id", $this->feedbackIds)
                ->update([
                    "status" => "processed",
                    "session_id" => $session->id,
                ]);

            $cache->forget_many([
                "sessions.all.page.1.per_page.15.status.",
                "sessions.office.{$office->id}.page.1.per_page.15.status.",
                "sessions.{$session->id}",
                "dashboard.overview",
                "dashboard.office.{$office->id}",
            ]);

            Log::info("Analysis completed", [
                "office_id" => $office->id,
                "session_id" => $session->id,
                "topic_count" => $result["topic_count"],
            ]);
        } catch (\Throwable $e) {
            $session->update([
                "status" => "failed",
                "completed_at" => now(),
            ]);

            Log::error("Analysis attempt failed", [
                "office_id" => $office->id,
                "session_id" => $session->id,
                "error" => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Handle a job failure after exhausting all retries.
     */
    public function failed(\Throwable $e): void
    {
        // $session = AnalysisSession::find($this->sessionId);
        $session = AnalysisSession::query()
            ->where("id", $this->sessionId)
            ->first();

        if ($session) {
            $session->update([
                "status" => "failed",
                "completed_at" => now(),
            ]);
        }

        Log::error("Analysis job permanently failed", [
            "office_id" => $this->officeId,
            "session_id" => $this->sessionId,
            "error" => $e->getMessage(),
        ]);
    }
}
