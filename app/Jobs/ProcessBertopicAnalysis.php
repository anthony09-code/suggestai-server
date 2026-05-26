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
    public int $timeout = 300;
    public int $backoff = 60;

    /**
     * Create a new job instance.
     */
    public function __construct(
        private AnalysisSession $session,
        private Office $office,
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
        $feedbacks = Feedback::query()
            ->whereIn("id", $this->feedbackIds)
            ->get();

        try {
            $result = $bertopicService->analyze(
                officeId: $this->office->id,
                sessionId: $this->session->id,
                documents: $feedbacks->pluck("raw_text")->toArray(),
                feedbackIds: $feedbacks->pluck("id")->toArray(),
            );

            $this->session->update([
                "topic_count" => $result["topic_count"],
                "status" => "completed",
                "completed_at" => now(),
            ]);

            Feedback::query()
                ->whereIn("id", $this->feedbackIds)
                ->update([
                    "status" => "processed",
                    "session_id" => $this->session->id,
                ]);

            $cache->forget_many([
                "sessions.all.page.1.per_page.15.status.",
                "sessions.office.{$this->office->id}.page.1.per_page.15.status.",
                "dashboard.overview",
                "dashboard.office.{$this->office->id}",
            ]);

            Log::info("Analysis completed", [
                "office_id" => $this->office->id,
                "session_id" => $this->session->id,
                "topic_count" => $result["topic_count"],
            ]);
        } catch (\Exception $e) {
            $this->session->update([
                "status" => "failed",
                "completed_at" => now(),
            ]);

            Log::error("Analysis failed", [
                "office_id" => $this->office->id,
                "session_id" => $this->session->id,
                "error" => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    public function failed(\Throwable $e): void
    {
        $this->session->update([
            "status" => "failed",
            "completed_at" => now(),
        ]);

        Log::error("Analysis job permanently failed", [
            "office_id" => $this->office->id,
            "session_id" => $this->session->id,
            "error" => $e->getMessage(),
        ]);
    }
}
