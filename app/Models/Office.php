<?php

namespace App\Models;

// use Illuminate\Database\Eloquent\Model;
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
    ]),
]
class Office extends Model
{
    use HasUlids, HasFactory;

    protected $table = "offices";

    protected static function booted(): void
    {
        static::creating(function (Office $office) {
            $office->access_link = self::generateUniqueAccessLink(
                $office->office_name,
            );
        });

        static::created(function (Office $office) {
            $office->qr_code = static::generateQrCode($office);
            $office->saveQuietly();
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
                if (
                    $office->qr_code &&
                    Storage::disk("public")->exists($office->qr_code)
                ) {
                    Storage::disk("public")->delete($office->qr_code);
                }

                $office->qr_code = static::generateQrCode($office);
                $office->saveQuietly();
            }
        });

        static::deleted(function (Office $office) {
            if (
                $office->qr_code &&
                Storage::disk("public")->exists($office->qr_code)
            ) {
                Storage::disk("public")->delete($office->qr_code);
            }
        });
    }

    private static function generateUniqueAccessLink(string $officeName): string
    {
        $base = Str::slug($officeName);
        $token = Str::random(8);
        $link = "{$base}-{$token}";

        while (static::query()->where("access_link", $link)->exists()) {
            $token = Str::random(8);
            $link = "{$base}-{$token}";
        }

        return $link;
    }

    private static function generateQrCode(Office $office): string
    {
        $url = url("/student/feedback/{$office->access_link}");

        $qrCode = new QrCode($url);
        $writer = new SvgWriter();
        $result = $writer->write($qrCode);

        $filename = "qrcodes/office-{$office->id}.svg";
        Storage::disk("public")->put($filename, $result->getString());

        return $filename;
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
