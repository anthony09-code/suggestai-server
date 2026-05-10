<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AnalysisSessionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "id" => $this->id,
            "status" => $this->status,
            "feedback_count" => $this->feedback_count,
            "topic_count" => $this->topic_count,
            "started_at" => $this->started_at,
            "completed_at" => $this->completed_at,
            "error_message" => $this->when(
                $this->status === "failed",
                $this->error_message,
            ),
            "office" => new OfficeResource($this->whenLoaded("office")),
            "user" => new UserResource($this->whenLoaded("user")),
            "topics" => TopicResource::collection($this->whenLoaded("topics")),
        ];
    }
}
