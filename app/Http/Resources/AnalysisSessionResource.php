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
            "date_from" => $this->date_from?->toISOString(),
            "date_to" => $this->date_to?->toISOString(),
            "started_at" => $this->started_at?->toISOString(),
            "completed_at" => $this->completed_at?->toISOString(),
            "office" => new OfficeResource($this->whenLoaded("office")),
            "user" => new UserResource($this->whenLoaded("user")),
            "topics" => TopicResource::collection($this->whenLoaded("topics")),
        ];
    }
}
