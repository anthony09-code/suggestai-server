<?php

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class FeedbackFilter
{
    public static function apply(Builder $query, array $filters): Builder
    {
        if (isset($filters["status"]) && $filters["status"] !== "all") {
            $query->where("status", $filters["status"]);
        }

        if (isset($filters["anonymous"]) && $filters["anonymous"] !== "all") {
            $isAnonymous = $filters["anonymous"] === "anonymous";
            $query->where("is_anonymous", $isAnonymous);
        }

        if (isset($filters["date"]) && $filters["date"] !== "all") {
            self::applyDateFilter(
                $query,
                $filters["date"],
                $filters["date_from"] ?? null,
                $filters["date_to"] ?? null,
            );
        }

        return $query;
    }

    protected static function applyDateFilter(
        Builder $query,
        string $range,
        ?string $dateFrom = null,
        ?string $dateTo = null,

    ): void {
        $now = now();

        match ($range) {
            "today" => $query->whereDate("created_at", $now->toDateString()),
            "7days" => $query->where("created_at", ">=", $now->subDays(7)),
            "30days" => $query->where("created_at", ">=", $now->subDays(30)),
            "90days" => $query->where("created_at", ">=", $now->subDays(90)),
            "wtd" => $query->where("created_at", ">=", $now->startOfWeek()),
            "mtd" => $query->where("created_at", ">=", $now->startOfMonth()),
            "qtd" => $query->where("created_at", ">=", $now->startOfQuarter()),
            "ytd" => $query->where("created_at", ">=", $now->startOfYear()),
            "custom" => self::applyCustomRange($query, $dateFrom, $dateTo),
            default => null,
        };
    }

    protected static function applyCustomRange(
        Builder $query,
        ?string $dateFrom,
        ?string $dateTo,
    ): void {
        if ($dateFrom) {
            $query->where(
                "created_at",
                ">=",
                Carbon::parse($dateFrom)->startOfDay(),
            );
        }
        if ($dateTo) {
            $query->where(
                "created_at",
                "<=",
                Carbon::parse($dateTo)->endOfDay(),
            );
        }
    }

}
