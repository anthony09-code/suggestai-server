<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[
    Fillable([
        "office_name",
        "description",
        "qr_code",
        "access_link",
        "is_active",
    ]),
]
class Office extends Model
{
    use HasUlids, HasFactory;

    protected $table = "offices";

    protected function casts(): array
    {
        return [
            "is_active" => "boolean",
        ];
    }

    /**
     * @return HasMany<Feedback, Office>
     */
    public function feedbacks(): HasMany
    {
        return $this->hasMany(Feedback::class, "office_id");
    }

    /**
     * @return HasMany<Report, Office>
     */
    public function reports(): HasMany
    {
        return $this->hasMany(Report::class, "office_id");
    }

    /**
     * @return HasMany<Topic, Office>
     */
    public function topics(): HasMany
    {
        return $this->hasMany(Topic::class, "office_id");
    }
}
