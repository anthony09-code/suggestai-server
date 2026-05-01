<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class UserController extends Controller
{
    public function user_login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            "email" => ["required", "email"],
            "password" => ["required", "string", "min:8"],
        ]);

        if (!Auth::attempt($validated)) {
            return response()->json(
                [
                    "success" => false,
                    "message" => "Invalid email or password.",
                ],
                401,
            );
        }

        /** @var User $user */
        $user = Auth::user();

        $token = $user->createToken("auth_token")->plainTextToken;

        return response()->json(
            [
                "success" => true,
                "message" => "Login successful.",
                "token" => $token,
                "token_type" => "Bearer",
                "user" => [
                    "id" => $user->id,
                    "name" => $user->full_name,
                    "email" => $user->email,
                ],
            ],
            200,
        );
    }

    public function user_logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(
            [
                "success" => true,
                "message" => "Logout successful.",
            ],
            200,
        );
    }

    /**
     * Logout user from all devices.
     */
    public function user_logout_all(Request $request): JsonResponse
    {
        $request->user()->tokens()->delete();

        return response()->json(
            [
                "success" => true,
                "message" => "Logged out from all devices.",
            ],
            200,
        );
    }

    public function user_info(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        return response()->json(
            [
                "success" => true,
                "user" => [
                    "id" => $request->user()->id,
                    "name" => $request->user()->full_name,
                    "email" => $request->user()->email,
                ],
            ],
            200,
        );
    }
}
