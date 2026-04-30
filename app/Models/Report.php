<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[
    Fillable([
        "user_id",
        "office_id",
        "session_id",
        "title",
        "format",
        "file_path",
        "generated_at",
    ]),
]
class Report extends Model
{
    use HasUlids, HasFactory;

    protected $table = "reports";

    protected function casts(): array
    {
        return [
            "generated_at" => "datetime",
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, "user_id");
    }

    public function office(): BelongsTo
    {
        return $this->belongsTo(Office::class, "office_id");
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(AnalysisSession::class, "session_id");
    }
}
