<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\User\UserAuthController;
use App\Http\Controllers\User\OfficeController;

Route::post("/login", [UserAuthController::class, "user_login"]);

Route::middleware("auth:sanctum")->group(function () {
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
});
