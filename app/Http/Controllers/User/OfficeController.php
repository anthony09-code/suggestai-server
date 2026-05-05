<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\CreateOfficeRequest;
use App\Http\Requests\User\UpdateOfficeRequest;
use App\Models\Office;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OfficeController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $offices = Office::query()->orderBy("office_name")->get();

        return response()->json([
            "success" => true,
            "data" => $offices,
        ]);
    }

    public function create_office(CreateOfficeRequest $request): JsonResponse
    {
        $office = Office::create([
            "office_name" => $request->office_name,
            "description" => $request->description,
            "is_active" => $request->is_active ?? true,
        ]);

        return response()->json(
            [
                "success" => true,
                "message" => "Office created successfully.",
                "data" => $office,
            ],
            201,
        );
    }

    public function update_office(
        UpdateOfficeRequest $request,
        Office $office,
    ): JsonResponse {
        $office->update($request->validated());

        return response()->json([
            "success" => true,
            "message" => "Office updated successfully.",
            "data" => $office,
        ]);
    }

    public function delete_office(Office $office): JsonResponse
    {
        $office->delete();

        return response()->json([
            "success" => true,
            "message" => "Office deleted successfully",
        ]);
    }
}
