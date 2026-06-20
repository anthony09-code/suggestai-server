<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\CreateOfficeRequest;
use App\Http\Requests\User\UpdateOfficeRequest;
use App\Http\Resources\OfficeResource;
use App\Models\Office;
use App\Services\CacheService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

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

    public function show(Office $office): JsonResponse
    {
        return $this->success("Office retrieved.", [
            "data" => new OfficeResource($office),
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

    public function downloadQr(string $accessLink)
    {
        $office = Office::where("access_link", $accessLink)->firstOrFail();

        $path = str_replace(asset("storage") . "/", "", $office->qr_code);

        if (!Storage::disk("public")->exists($path)) {
            abort(404, "QR code not found.");
        }

        return Storage::disk("public")->download(
            $path,
            "{$office->access_link}-qr.svg",
            [
                "Content-Type" => "image/svg+xml",
            ],
        );
    }
}
