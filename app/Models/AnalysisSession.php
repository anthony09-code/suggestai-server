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
        "user_id",
        "feedback_count",
        "topic_count",
        "status",
        "date_from",
        "date_to",
        "started_at",
        "completed_at",
    ]),
]
class AnalysisSession extends Model
{
    use HasUlids, HasFactory;

    protected $table = "analysis_sessions";

    protected $casts = [
        "date_from" => "datetime",
        "date_to" => "datetime",
        "started_at" => "datetime",
        "completed_at" => "datetime",
    ];

    public function topics(): HasMany
    {
        return $this->hasMany(Topic::class, "session_id");
    }

    public function topicResults(): HasMany
    {
        return $this->hasMany(TopicResult::class, "session_id");
    }

    /**
     * @return BelongsTo<Office, AnalysisSession>
     */
    public function office(): BelongsTo
    {
        return $this->belongsTo(Office::class, "office_id");
    }

    /**
     * @return BelongsTo<User, AnalysisSession>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, "user_id");
    }
}
