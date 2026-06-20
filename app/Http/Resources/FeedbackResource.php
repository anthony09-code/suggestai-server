<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FeedbackResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            "id" => $this->id,
            "raw_text" => $this->raw_text,
            "status" => $this->status,
            "is_anonymous" => $this->is_anonymous,
            "is_summarized" => $this->is_summarized,
            "session_id" => $this->session_id,
            "created_at" => $this->created_at?->toISOString(),
            "student" => $this->whenLoaded(
                "student",
                fn() => [
                    "id" => $this->student->id,
                    "email" => $this->student->email,
                    "name" => $this->student->name,
                    "profile_picture" => $this->student->profile_picture,
                ],
            ),
            "office" => $this->whenLoaded(
                "office",
                fn() => [
                    "id" => $this->office->id,
                    "office_name" => $this->office->office_name,
                    "access_link" => $this->office->access_link,
                ],
            ),
        ];
    }
}
