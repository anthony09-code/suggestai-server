<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

#[
    Fillable([
        "google_id",
        "name",
        "email",
        "profile_picture",
        "google_token",
        "google_refresh_token",
        "is_active",
    ]),
]
class Student extends Authenticatable
{
    use HasUlids, HasApiTokens, HasFactory, Notifiable;

    protected $table = "students";

    protected function casts(): array
    {
        return [
            "is_active" => "boolean",
        ];
    }

    public function feedbacks()
    {
        return $this->hasMany(Feedback::class, "student_id");
    }
}
