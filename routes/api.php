<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\User\UserAuthController;
use App\Http\Controllers\User\OfficeController;

Route::post("/login", [UserAuthController::class, "user_login"])->middleware(
    "throttle:login",
);

Route::middleware(["auth:sanctum", "throttle:api"])->group(function () {
    Route::prefix("user")
        ->name("user.")
        ->group(function () {
            Route::get("/", [UserAuthController::class, "user_info"])->name(
                "info",
            );
            Route::post("/logout", [
                UserAuthController::class,
                "user_logout",
            ])->name("logout");

            Route::post("/logout-all", [
                UserAuthController::class,
                "user_logout_all",
            ])->name("logout-all");

            Route::get("/sessions", [
                UserAuthController::class,
                "user_sessions",
            ])->name("sessions");

            Route::delete("/sessions/{tokenId}", [
                UserAuthController::class,
                "user_revoke_session",
            ])->name("sessions.revoke");
        });

    Route::prefix("offices")
        ->name("offices.")
        ->group(function () {
            Route::get("/", [OfficeController::class, "index"])->name("index");

            Route::post("/", [OfficeController::class, "create_office"])->name(
                "create",
            );
            Route::put("/{office}", [
                OfficeController::class,
                "update_office",
            ])->name("update");

            Route::delete("/{office}", [
                OfficeController::class,
                "delete_office",
            ])->name("delete");
        });

    Route::prefix("sessions")
        ->name("sessions.")
        ->group(function () {
            Route::get("/", [AnalysisSessionController::class, "index"])->name(
                "index",
            );
            Route::get("/{session}", [
                AnalysisSessionController::class,
                "show",
            ])->name("show");

            Route::get("/office/{office}", [
                AnalysisSessionController::class,
                "get_by_office",
            ])->name("by_office");

            Route::post("/analyze/{office}", [
                AnalysisSessionController::class,
                "analyze",
            ])->name("analyze");

            Route::delete("/{session}", [
                AnalysisSessionController::class,
                "delete",
            ])->name("delete");
        });
});
