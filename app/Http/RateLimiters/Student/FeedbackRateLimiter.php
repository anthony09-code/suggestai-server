<?php
namespace App\Http\RateLimiters\Student;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class FeedbackRateLimiter
{
    public function register(): void
    {
        $this->configureFeedbackLimiter();
    }

    private function configureFeedbackLimiter(): void
    {
        RateLimiter::for("feedback", function (Request $request) {
            return Limit::perMinutes(5, 3)
                ->by(auth("student")->id() . ":" . $request->route("office"))
                ->response(
                    fn() => back()
                        ->withInput()
                        ->withErrors([
                            "raw_text" =>
                                "Too many submissions. Please wait a few minutes before trying again.",
                        ]),
                );
        });
    }
}
