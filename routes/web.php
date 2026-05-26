<?php

use Illuminate\Support\Facades\Route;
use Laravel\Socialite\Facades\Socialite;

Route::get("/", function () {
    return view("welcome");
});

Route::get("/debug-oauth", function () {
    $url = Socialite::driver("google")->redirect()->getTargetUrl();
    return $url;
});
