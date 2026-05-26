<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\CreateOfficeRequest;
use App\Http\Requests\User\UpdateOfficeRequest;
use App\Http\Resources\OfficeResource;
use App\Models\Office;
use App\Services\CacheService;
use Illuminate\Http\JsonResponse;

class OfficeController extends Controller
{
    private const CACHE_KEY = "offices.all";
    private const CACHE_TTL = 3600;

    public function __construct(private CacheService $cache) {}

    public function index(): JsonResponse
    {
        $offices = $this->cache->remember(
            self::CACHE_KEY,
            self::CACHE_TTL,
            fn() => OfficeResource::collection(
                Office::query()->orderBy("office_name")->get(),
            )->resolve(),
        );

        return $this->success("Offices retrieved.", [
            "data" => $offices,
        ]);
    }

    public function create_office(CreateOfficeRequest $request): JsonResponse
    {
        $office = Office::create($request->validated());

        $this->cache->forget(self::CACHE_KEY);

        return $this->success(
            "Office created successfully.",
            [
                "data" => new OfficeResource($office),
            ],
            201,
        );
    }

    public function update_office(
        UpdateOfficeRequest $request,
        Office $office,
    ): JsonResponse {
        $office->update($request->validated());

        $this->cache->forget_many([
            self::CACHE_KEY,
            "dashboard.office.{$office->id}",
            "dashboard.overview",
        ]);

        return $this->success("Office updated successfully.", [
            "data" => new OfficeResource($office->fresh()),
        ]);
    }

    public function delete_office(Office $office): JsonResponse
    {
        $office->delete();

        $this->cache->forget_many([
            self::CACHE_KEY,
            "dashboard.office.{$office->id}",
            "dashboard.overview",
        ]);

        return $this->success("Office deleted successfully.");
    }
}
