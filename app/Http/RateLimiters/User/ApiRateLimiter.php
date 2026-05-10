<?php
namespace App\Http\RateLimiters\User;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class ApiRateLimiter
{
    public function register(): void
    {
        $this->configure_login_limiter();
        $this->configure_api_limiter();
    }

    private function configure_login_limiter(): void
    {
        RateLimiter::for("login", function (Request $request) {
            return Limit::perMinute(5)
                ->by($request->email . "|" . $request->ip())
                ->response(
                    fn() => response()->json(
                        [
                            "success" => false,
                            "message" =>
                                "Too many login attempts. Please try again later.",
                        ],
                        429,
                    ),
                );
        });
    }

    private function configure_api_limiter(): void
    {
        RateLimiter::for("api", function (Request $request) {
            return Limit::perMinute(60)
                ->by($request->user()?->id ?: $request->ip())
                ->response(
                    fn() => response()->json(
                        [
                            "success" => false,
                            "message" => "Too many requests. Please slow down.",
                        ],
                        429,
                    ),
                );
        });
    }
}
