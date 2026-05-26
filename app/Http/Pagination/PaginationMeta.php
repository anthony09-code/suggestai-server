<?php
namespace App\Http\Pagination;

use Illuminate\Pagination\LengthAwarePaginator;

class PaginationMeta
{
    /**
     * @param LengthAwarePaginator<mixed> $paginator
     * @return array<string, mixed>
     */
    public static function make(LengthAwarePaginator $paginator): array
    {
        return [
            "total" => $paginator->total(),
            "per_page" => $paginator->perPage(),
            "current_page" => $paginator->currentPage(),
            "last_page" => $paginator->lastPage(),
            "from" => $paginator->firstItem(),
            "to" => $paginator->lastItem(),
        ];
    }
}
