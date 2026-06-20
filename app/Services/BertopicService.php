<?php

namespace App\Services;

use App\Models\Topic;
use App\Models\TopicResult;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class BertopicService
{
    public function __construct(private string $baseUrl = "")
    {
        $this->baseUrl = config(
            "services.bertopic.url",
            "http://127.0.0.1:8001",
        );
    }

    public function analyze(
        string $officeId,
        string $sessionId,
        array $documents,
        array $feedbackIds,
    ): array {
        $data = $this->fetch_analysis($officeId, $documents, $feedbackIds);

        if (empty($data["topics"]) || !isset($data["results"])) {
            throw new \RuntimeException(
                "Malformed payload signature received from BERT API backend service.",
            );
        }

        return DB::transaction(function () use ($data, $officeId, $sessionId) {
            $topicMap = $this->persist_topics(
                $data["topics"],
                $officeId,
                $sessionId,
            );
            $this->persist_topic_results(
                $data["results"],
                $officeId,
                $sessionId,
                $topicMap,
            );

            return [
                "topics" => $data["topics"],
                "topic_count" => count($data["topics"]),
            ];
        });
    }

    private function fetch_analysis(
        string $officeId,
        array $documents,
        array $feedbackIds,
    ): array {
        try {
            $response = Http::timeout(600)->post(
                "{$this->baseUrl}/api/analyze",
                [
                    "office_id" => $officeId,
                    "documents" => $documents,
                    "feedback_ids" => $feedbackIds,
                ],
            );

            if ($response->failed()) {
                throw new \RuntimeException(
                    "BERTopic service failed: " . $response->body(),
                );
            }

            return $response->json();
        } catch (\RuntimeException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error("BERTopic HTTP request failed", [
                "office_id" => $officeId,
                "error" => $e->getMessage(),
            ]);

            throw new \RuntimeException(
                "Could not reach BERTopic service. Please try again later.",
            );
        }
    }

    private function persist_topics(
        array $topics,
        string $officeId,
        string $sessionId,
    ): array {
        return collect($topics)
            ->mapWithKeys(function (array $topic) use ($officeId, $sessionId) {
                $record = Topic::create([
                    "office_id" => $officeId,
                    "session_id" => $sessionId,
                    "label" => $topic["label"],
                    "keywords" => $topic["keywords"],
                    "feedback_count" => $topic["feedback_count"],
                    "cluster_x" => $topic["cluster_x"] ?? null,
                    "cluster_y" => $topic["cluster_y"] ?? null,
                ]);

                return [$topic["topic_id"] => $record->id];
            })
            ->all();
    }

    private function persist_topic_results(
        array $results,
        string $officeId,
        string $sessionId,
        array $topicMap,
    ): void {
        $records = collect($results)
            ->filter(fn(array $result) => !empty($result["feedback_id"]))
            ->map(
                fn(array $result) => [
                    "id" => (string) Str::ulid(),
                    "feedback_id" => $result["feedback_id"],
                    "office_id" => $officeId,
                    "session_id" => $sessionId,
                    "topic_id" => $topicMap[$result["topic_id"]] ?? null,
                    "cleaned_text" => $result["cleaned_text"] ?? null,
                    "translated_text" => $result["translated_text"] ?? null,
                    "summary" => $result["summary"] ?? null,
                    "confidence_score" => $result["confidence_score"] ?? 0.0,
                    "processed_at" => now(),
                ],
            )
            ->values()
            ->all();

        if (!empty($records)) {
            TopicResult::insert($records);
        }
    }
}
