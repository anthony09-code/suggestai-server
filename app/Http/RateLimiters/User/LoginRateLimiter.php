<?php
namespace App\Http\RateLimiters\User;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class LoginRateLimiter
{
    private const MAX_ATTEMPTS = 5;
    private const DECAY_SECONDS = 60;

    public function key(Request $request): string
    {
        return "login:" . Str::lower($request->email) . "|" . $request->ip();
    }

    public function too_many_attempts(Request $request): bool
    {
        return RateLimiter::tooManyAttempts(
            $this->key($request),
            self::MAX_ATTEMPTS - 1,
        );
    }

    public function increment(Request $request): void
    {
        RateLimiter::hit($this->key($request), self::DECAY_SECONDS);
    }

    public function clear(Request $request): void
    {
        RateLimiter::clear($this->key($request));
    }

    public function available_in(Request $request): int
    {
        return RateLimiter::availableIn($this->key($request));
    }

    public function remaining(Request $request): int
    {
        return max(
            0,
            self::MAX_ATTEMPTS -
                1 -
                RateLimiter::attempts($this->key($request)),
        );
    }
}
