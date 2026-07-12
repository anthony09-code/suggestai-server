<?php

namespace App\Models;

use App\Models\Model;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\SvgWriter;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

#[
    Fillable([
        "office_name",
        "description",
        "qr_code",
        "access_link",
        "is_active",
        "color",
        "image",
    ]),
]
class Office extends Model
{
    use HasUlids, HasFactory;

    protected $table = "offices";

    private static function randomColor(): string
    {
        $colors = [
            "#6366f1",
            "#8b5cf6",
            "#ec4899",
            "#f43f5e",
            "#f97316",
            "#eab308",
            "#22c55e",
            "#14b8a6",
            "#3b82f6",
            "#06b6d4",
        ];
        return $colors[array_rand($colors)];
    }

    protected static function booted(): void
    {
        static::creating(function (Office $office) {
            $office->access_link ??= self::generateUniqueAccessLink(
                $office->office_name,
            );
            $office->color ??= self::randomColor();
        });

        static::created(function (Office $office) {
            static::regenerateQrCode($office);
        });

        static::updating(function (Office $office) {
            if ($office->isDirty("office_name")) {
                $office->access_link = self::generateUniqueAccessLink(
                    $office->office_name,
                );
            }
        });

        static::updated(function (Office $office) {
            if ($office->wasChanged("access_link")) {
                static::regenerateQrCode($office);
            }
        });

        static::deleted(function (Office $office) {
            foreach (["qr_code", "image"] as $field) {
                if (
                    $office->$field &&
                    Storage::disk("public")->exists($office->$field)
                ) {
                    Storage::disk("public")->delete($office->$field);
                }
            }
        });
    }

    private static function generateUniqueAccessLink(string $officeName): string
    {
        do {
            $link = Str::slug($officeName) . "-" . Str::random(8);
        } while (static::query()->where("access_link", $link)->exists());

        return $link;
    }

    private static function generateQrCode(Office $office): string
    {
        $url = url("/student/feedback/{$office->access_link}");
        $result = (new SvgWriter())->write(new QrCode($url));

        $filename = "qrcodes/office-{$office->id}.svg";
        Storage::disk("public")->put($filename, $result->getString());

        return $filename;
    }

    private static function regenerateQrCode(Office $office): void
    {
        if (
            $office->qr_code &&
            Storage::disk("public")->exists($office->qr_code)
        ) {
            Storage::disk("public")->delete($office->qr_code);
        }
        $office->qr_code = static::generateQrCode($office);
        $office->saveQuietly();
    }

    public function getRouteKeyName(): string
    {
        return "access_link";
    }

    protected function casts(): array
    {
        return [
            "is_active" => "boolean",
        ];
    }

    public function getImageUrlAttribute(): ?string
    {
        return $this->image ? Storage::disk("public")->url($this->image) : null;
    }

    public function getQrCodeUrlAttribute(): ?string
    {
        return $this->qr_code
            ? Storage::disk("public")->url($this->qr_code)
            : null;
    }

    /** @return HasMany<Feedback, Office> */
    public function feedbacks(): HasMany
    {
        return $this->hasMany(Feedback::class, "office_id");
    }

    /** @return HasMany<Report, Office> */
    public function reports(): HasMany
    {
        return $this->hasMany(Report::class, "office_id");
    }

    /** @return HasMany<Topic, Office> */
    public function topics(): HasMany
    {
        return $this->hasMany(Topic::class, "office_id");
    }
}
