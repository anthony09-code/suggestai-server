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

        Route::get("/logout", [StudentAuthController::class, "logout"])->name(
            "student.logout",
        );

        Route::get("/feedback/{office:access_link}", [
            FeedbackController::class,
            "show",
        ])->name("feedback.show");

        Route::post("/feedback/{office:access_link}", [
            FeedbackController::class,
            "store",
        ])->name("feedback.store");
    });
