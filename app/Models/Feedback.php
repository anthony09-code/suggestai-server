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
        "submission_method",
        "status",
    ]),
]
class Feedback extends Model
{
    use HasUlids, HasFactory;

    protected $table = "feedbacks";

    protected function casts(): array
    {
        return [
            "submission_method" => "string",
            "status" => "string",
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, "student_id");
    }

    public function office(): BelongsTo
    {
        return $this->belongsTo(Office::class, "office_id");
    }
}
