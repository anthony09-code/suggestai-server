<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

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
}
