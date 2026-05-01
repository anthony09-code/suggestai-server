<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;

Route::post("/login", [UserController::class, "user_login"]);

Route::middleware("auth:sanctum")->group(function () {
    Route::get("/user", [UserController::class, "user_info"]);
    Route::post("/logout", [UserController::class, "user_logout"]);
    Route::post("/logout-all", [UserController::class, "user_logout_all"]);
});
