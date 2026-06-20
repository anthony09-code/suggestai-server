<?php

use App\Http\Controllers\Student\StudentAuthController;
use App\Http\Controllers\Student\FeedbackController;
use Illuminate\Support\Facades\Route;

Route::prefix("student")
    ->name("student.")
    ->group(function () {
        Route::get("/auth/google", [
            StudentAuthController::class,
            "redirect_to_google",
        ])->name("auth.google");

        Route::get("/auth/google/callback", [
            StudentAuthController::class,
            "handle_google_callback",
        ])->name("auth.google.callback");

        Route::middleware("auth:student")->group(function () {
            Route::post("/logout", [
                StudentAuthController::class,
                "logout",
            ])->name("logout");
        });

        Route::get("/feedback/{office:access_link}", [
            FeedbackController::class,
            "show",
        ])->name("feedback.show");

        Route::post("/feedback/{office:access_link}", [
            FeedbackController::class,
            "store",
        ])
            ->name("feedback.store")
            ->middleware("throttle:feedback");

        Route::get("/feedback/{office:access_link}/success", [
            FeedbackController::class,
            "success_page",
        ])->name("feedback.success");
    });
