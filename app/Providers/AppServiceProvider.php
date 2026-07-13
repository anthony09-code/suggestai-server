<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use App\Http\RateLimiters\User\ApiRateLimiter;
use App\Http\RateLimiters\Student\FeedbackRateLimiter;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        (new ApiRateLimiter())->register();
        (new FeedbackRateLimiter())->register();

        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }
    }
}