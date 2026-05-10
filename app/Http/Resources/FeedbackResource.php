<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FeedbackResource extends JsonResource
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
            "raw_text" => $this->when(!$this->is_anonymous, $this->raw_text),
            "status" => $this->status,
            "is_anonymous" => $this->is_anonymous,
            "is_summarized" => $this->is_summarized,
            "submitted_at" => $this->created_at,
            "student" => new StudentResource($this->whenLoaded("student")),
            "office" => new OfficeResource($this->whenLoaded("office")),
        ];
    }
}
