<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TopicResource extends JsonResource
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
            "label" => $this->label,
            "keywords" => $this->keywords,
            "feedback_count" => $this->feedback_count,
            "cluster_x" => $this->cluster_x,
            "cluster_y" => $this->cluster_y,
            "office" => new OfficeResource($this->whenLoaded("office")),
            "results" => TopicResultResource::collection(
                $this->whenLoaded("topic_result"),
            ),
            "created_at" => $this->created_at,
        ];
    }
}
