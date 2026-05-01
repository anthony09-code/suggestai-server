<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[
    Fillable([
        "student_id",
        "office_id",
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

    protected function casts(): array
    {
        return [
            "status" => "string",
            "is_anonymous" => "boolean",
            "is_summarized" => "boolean",
        ];
    }

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
}
