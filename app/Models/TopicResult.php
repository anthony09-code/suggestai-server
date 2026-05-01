<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[
    Fillable([
        "feedback_id",
        "office_id",
        "topic_id",
        "cleaned_text",
        "translated_text",
        "summary",
        "confidence_score",
        "processed_at",
    ]),
]
class TopicResult extends Model
{
    use HasUlids, HasFactory;

    protected $table = "topic_results";

    protected function casts(): array
    {
        return [
            "processed_at" => "datetime",
            "confidence_score" => "float",
        ];
    }

    /**
     * @return BelongsTo<Feedback, TopicResult>
     */

    public function feedback(): BelongsTo
    {
        return $this->belongsTo(Feedback::class, "feedback_id");
    }

    /**
     * @return BelongsTo<Topic, TopicResult>
     */
    public function topic(): BelongsTo
    {
        return $this->belongsTo(Topic::class, "topic_id");
    }

    /**
     * @return BelongsTo<Office, TopicResult>
     */
    public function office(): BelongsTo
    {
        return $this->belongsTo(Office::class, "office_id");
    }
}
