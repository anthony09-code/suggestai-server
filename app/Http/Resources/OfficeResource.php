<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class OfficeResource extends JsonResource
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
            "office_name" => $this->office_name,
            "description" => $this->description,
            "is_active" => $this->is_active,
            "access_link" => $this->access_link,
            "qr_code_url" => $this->qr_code
                ? Storage::disk("public")->url($this->qr_code)
                : null,
            "created_at" => $this->created_at,
            "updated_at" => $this->updated_at,
        ];
    }
}
