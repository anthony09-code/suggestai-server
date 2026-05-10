<?php

namespace App\Http\Controllers\User;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use App\Http\RateLimiters\User\LoginRateLimiter;
use App\Http\Resources\UserResource;
use App\Http\Controllers\Controller;
use App\Services\TurnstileService;
use App\Models\User;

class UserAuthController extends Controller
{
    public function __construct(
        private LoginRateLimiter $limiter,
        private TurnstileService $turnstileService,
    ) {}

    public function user_login(Request $request): JsonResponse
    {
        $request->validate([
            "email" => ["required", "email"],
            "password" => ["required", "string", "min:8"],
            "cf_turnstile_response" => ["required", "string"],
        ]);

        if (
            !$this->turnstileService->verify(
                $request->cf_turnstile_response,
                $request->ip(),
            )
        ) {
            return $this->error(
                "Human verification failed. Please try again.",
                422,
            );
        }

        if ($this->limiter->too_many_attempts($request)) {
            $seconds = $this->limiter->available_in($request);

            return $this->error(
                "Too many login attempts. Please try again in {$seconds} seconds.",
                429,
                [
                    "retry_after" => $seconds,
                ],
            );
        }

        if (!Auth::attempt($request->only("email", "password"))) {
            $this->limiter->increment($request);
            $remaining = $this->limiter->remaining($request);

            $message =
                $remaining === 0
                    ? "Too many failed attempts. Please wait before trying again."
                    : "Email or password is incorrect. {$remaining} attempt" .
                        ($remaining === 1 ? "" : "s") .
                        " remaining.";

            return $this->error($message, 401, [
                "attempts_remaining" => $remaining,
                "retry_after" =>
                    $remaining === 0
                        ? $this->limiter->available_in($request)
                        : null,
            ]);
        }

        $this->limiter->clear($request);

        /** @var User $user */
        $user = Auth::user();

        $token = $user->createToken("auth_token", ["*"], now()->addDays(7))
            ->plainTextToken;

        return $this->success("Login successful.", [
            "token" => $token,
            "token_type" => "Bearer",
            "expires_in" => "7 days",
            "user" => new UserResource($user),
        ]);
    }

    public function user_logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return $this->success("Logout successful.");
    }

    /**
     * Logout user from all devices.
     */
    public function user_logout_all(Request $request): JsonResponse
    {
        $request->user()->tokens()->delete();

        return $this->success("Logged out from all devices.");
    }

    public function user_info(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        return $this->success("User info retrieved.", [
            "user" => new UserResource($user),
        ]);
    }

    public function user_sessions(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $currentTokenId = $user->currentAccessToken()->id;

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
                    "is_current" => $token->id === $currentTokenId,
                ],
            );

        return $this->success("Sessions retrieved.", ["sessions" => $sessions]);
    }

    public function user_revoke_session(
        Request $request,
        int $tokenId,
    ): JsonResponse {
        /** @var User $user */
        $user = $request->user();
        $token = $user->tokens()->find($tokenId);

        if (!$token) {
            return $this->error("Session not found.", 404);
        }

        if ($token->id === $user->currentAccessToken()->id) {
            return $this->error(
                "Cannot revoke current session. Use logout instead.",
                400,
            );
        }

        $token->delete();
        return $this->success("Session revoked successfully.");
    }
}
