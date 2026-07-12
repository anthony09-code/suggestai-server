<?php

namespace App\Models;

use App\Models\Model;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[
    Fillable([
        "office_id",
        "session_id",
        "label",
        "description",
        "keywords",
        "feedback_count",
        "cluster_x",
        "cluster_y",
    ]),
]
class Topic extends Model
{
    use HasUlids, HasFactory;

    protected $table = "topics";

    protected function casts(): array
    {
        return [
            "keywords" => "array",
        ];
    }

    /**
     * @return BelongsTo<Office, Topic>
     */
    public function office(): BelongsTo
    {
        return $this->belongsTo(Office::class, "office_id");
    }

    /**
     * @return HasMany<TopicResult, Topic>
     */
    public function topicResults(): HasMany
    {
        return $this->hasMany(TopicResult::class, "topic_id");
    }

    /**
     * @return BelongsTo<AnalysisSession, Topic>
     */
    public function session(): BelongsTo
    {
        return $this->belongsTo(AnalysisSession::class, "session_id");
    }
}
