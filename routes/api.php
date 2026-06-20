<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\User\UserAuthController;
use App\Http\Controllers\User\OfficeController;
use App\Http\Controllers\User\FeedbackController;
use App\Http\Controllers\User\FeedbackExportController;
use App\Http\Controllers\User\AnalysisSessionController;

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
            Route::get("/{office:access_link}", [
                OfficeController::class,
                "show",
            ])->name("show");
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

    Route::prefix("feedbacks")
        ->name("feedbacks.")
        ->group(function () {
            Route::get("/stats", [FeedbackController::class, "stats"])->name(
                "stats",
            );
            Route::get("/", [FeedbackController::class, "index"])->name(
                "index",
            );
            Route::get("/export/{office:access_link}", [
                FeedbackExportController::class,
                "export",
            ])->name("feedbacks.export");
            Route::get("/office/{office:access_link}", [
                FeedbackController::class,
                "get_by_office",
            ])->name("by_office");
            Route::get("/{feedback}", [
                FeedbackController::class,
                "show",
            ])->name("show");
            Route::delete("/{feedback}", [
                FeedbackController::class,
                "delete_feedback",
            ])->name("delete");
        });

    Route::prefix("sessions")
        ->name("sessions.")
        ->group(function () {
            Route::get("/stats", [
                AnalysisSessionController::class,
                "stats",
            ])->name("stats");
            Route::get("/", [AnalysisSessionController::class, "index"])->name(
                "index",
            );
            Route::get("/office/{office:access_link}", [
                AnalysisSessionController::class,
                "get_by_office",
            ])->name("by_office");
            Route::post("/analyze/{office:access_link}", [
                AnalysisSessionController::class,
                "analyze",
            ])->name("analyze");
            Route::delete("/{session}", [
                AnalysisSessionController::class,
                "delete",
            ])->name("delete");
            Route::get("/{session}/report/download", [
                AnalysisSessionController::class,
                "download",
            ])->name("report.download");
            Route::get("/{session}", [
                AnalysisSessionController::class,
                "show",
            ])->name("show");
        });
});
