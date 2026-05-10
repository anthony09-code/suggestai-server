<?php

namespace App\Http\Controllers;
use Illuminate\Http\JsonResponse;

abstract class Controller
{
    protected function success(
        string $message,
        array $data = [],
        int $status = 200,
    ): JsonResponse {
        return response()->json(
            [
                "success" => true,
                "message" => $message,
                ...$data,
            ],
            $status,
        );
    }

    protected function error(
        string $message,
        int $status,
        array $data = [],
    ): JsonResponse {
        return response()->json(
            [
                "success" => false,
                "message" => $message,
                ...$data,
            ],
            $status,
        );
    }
}
