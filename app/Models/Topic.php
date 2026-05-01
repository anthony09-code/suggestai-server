<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[
    Fillable([
        "office_id",
        "label",
        "keywords",
        "feedback_count",
        "cluster_x",
        "cluster_y",
    ]),
]
class Topic extends Model
{
    use HasUuids, HasFactory;

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
    public function topic_result(): HasMany
    {
        return $this->hasMany(TopicResult::class, "topic_id");
    }
}
