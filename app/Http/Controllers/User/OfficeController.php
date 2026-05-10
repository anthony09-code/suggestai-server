<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\CreateOfficeRequest;
use App\Http\Requests\User\UpdateOfficeRequest;
use App\Http\Resources\OfficeResource;
use App\Models\Office;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OfficeController extends Controller
{
    public function index(): JsonResponse
    {
        $offices = Office::query()->orderBy("office_name")->get();

        return $this->success("Offices retrieved.", [
            "data" => OfficeResource::collection($offices),
        ]);
    }

    public function create_office(CreateOfficeRequest $request): JsonResponse
    {
        $office = Office::create($request->validated());

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

        return $this->success("Office updated successfully.", [
            "data" => new OfficeResource($office),
        ]);
    }

    public function delete_office(Office $office): JsonResponse
    {
        $office->delete();

        return $this->success("Office deleted successfully.");
    }
}
