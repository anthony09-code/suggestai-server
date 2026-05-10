<?php

namespace App\Services;

use App\Models\TopicResult;
use App\Models\Topic;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BertopicService
{
    private string $baseUrl;

    public function __construct()
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
        $data = $this->fetchAnalysis($officeId, $documents);

        return DB::transaction(function () use (
            $data,
            $officeId,
            // $sessionId,
            $feedbackIds,
        ) {
            $topicMap = $this->createTopics(
                $data["topics"],
                $officeId,
                // $sessionId,
            );

            $this->createTopicResults(
                $data["results"],
                $feedbackIds,
                $officeId,
                // $sessionId,
                $topicMap,
            );

            return [
                "topics" => $data["topics"],
                "topic_count" => count($data["topics"]),
            ];
        });
    }

    private function fetchAnalysis(string $officeId, array $documents): array
    {
        try {
            $response = Http::timeout(120)->post(
                "{$this->baseUrl}/api/analyze",
                [
                    "office_id" => $officeId,
                    "documents" => $documents,
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

    private function createTopics(
        array $topics,
        string $officeId,
        string $sessionId,
    ): array {
        $topicMap = [];

        foreach ($topics as $topicData) {
            $topic = Topic::create([
                "office_id" => $officeId,
                // "session_id" => $sessionId,
                "label" => $topicData["label"],
                "keywords" => $topicData["keywords"],
                "feedback_count" => $topicData["feedback_count"],
                "cluster_x" => $topicData["cluster_x"] ?? null,
                "cluster_y" => $topicData["cluster_y"] ?? null,
            ]);

            $topicMap[$topicData["topic_id"]] = $topic->id;
        }

        return $topicMap;
    }

    private function createTopicResults(
        array $results,
        array $feedbackIds,
        string $officeId,
        string $sessionId,
        array $topicMap,
    ): void {
        $records = array_map(
            fn($result, $index) => [
                "feedback_id" => $feedbackIds[$index] ?? null,
                "office_id" => $officeId,
                // "session_id" => $sessionId,
                "topic_id" => $topicMap[$result["topic_id"]] ?? null,
                "cleaned_text" => $result["cleaned_text"],
                "translated_text" => $result["translated_text"] ?? null,
                "summary" => $result["summary"] ?? null,
                "confidence_score" => $result["confidence_score"],
                "processed_at" => now(),
            ],
            $results,
            array_keys($results),
        );

        TopicResult::insert($records);
    }
}
