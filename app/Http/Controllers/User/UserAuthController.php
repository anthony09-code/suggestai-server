<?php

namespace App\Http\Controllers\User;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Models\User;

class UserAuthController extends Controller
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

        $token = $user->createToken("auth_token", ["*"], now()->addDays(7))
            ->plainTextToken;

        return response()->json(
            [
                "success" => true,
                "message" => "Login successful.",
                "token" => $token,
                "token_type" => "Bearer",
                "expires_in" => "7 days",
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

    public function user_sessions(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $sessions = $user
            ->tokens()
            ->orderBy("last_used_at", "desc")
            ->get()
            ->map(
                fn($token) => [
                    "id" => $token->id,
                    "name" => $token->name,
                    "last_used_at" => $token->last_used_at,
                    "created_at" => $token->created_at,
                    "expires_at" => $token->expires_at,
                    "is_current" =>
                        $token->id === $user->currentAccessToken()->id,
                ],
            );

        return response()->json(
            [
                "success" => true,
                "sessions" => $sessions,
            ],
            200,
        );
    }

    public function user_revoke_session(
        Request $request,
        int $tokenId,
    ): JsonResponse {
        /** @var User $user */
        $user = $request->user();
        $token = $user->tokens()->find($tokenId);

        if (!$token) {
            return response()->json(
                [
                    "success" => false,
                    "message" => "Session not found.",
                ],
                404,
            );
        }

        if ($token->id === $user->currentAccessToken()->id) {
            return response()->json(
                [
                    "success" => false,
                    "message" =>
                        "Cannot revoke current session. Use logout instead.",
                ],
                400,
            );
        }

        $token->delete();

        return response()->json(
            [
                "success" => true,
                "message" => "Session revoked successfully.",
            ],
            200,
        );
    }
}
