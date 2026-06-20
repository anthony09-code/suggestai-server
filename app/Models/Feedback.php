<?php

namespace App\Models;

use App\Models\Model;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[
    Fillable([
        "student_id",
        "office_id",
        "session_id",
        "raw_text",
        "status",
        "is_anonymous",
        "is_summarized",
    ]),
]
class Feedback extends Model
{
    use HasUlids, HasFactory;

    protected $table = "feedbacks";

    /**
     * @return BelongsTo<Student, Feedback>
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, "student_id");
    }

    /**
     * @return BelongsTo<Office, Feedback>
     */
    public function office(): BelongsTo
    {
        return $this->belongsTo(Office::class, "office_id");
    }

    /**
     * @return BelongsTo<AnalysisSession, Feedback>
     */
    public function session(): BelongsTo
    {
        return $this->belongsTo(AnalysisSession::class, "session_id");
    }

    /**
     * @param Builder<Feedback> $query
     */
    public function scopeReadyForAnalysis(Builder $query): Builder
    {
        return $query
            ->where("status", "pending")
            ->where("is_summarized", false);
    }
}
