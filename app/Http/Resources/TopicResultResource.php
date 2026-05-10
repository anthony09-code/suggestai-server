<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TopicResultResource extends JsonResource
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
            "cleaned_text" => $this->cleaned_text,
            "translated_text" => $this->translated_text,
            "summary" => $this->summary,
            "confidence_score" => $this->confidence_score,
            "processed_at" => $this->processed_at,
            "feedback" => new FeedbackResource($this->whenLoaded("feedback")),
            "topic" => new TopicResource($this->whenLoaded("topic")),
            "office" => new OfficeResource($this->whenLoaded("office")),
        ];
    }
}
