<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TurnstileService
{
    public function verify(string $token, string $ip): bool
    {
        if (app()->environment("local")) {
            return true;
        }

        try {
            $response = Http::asForm()->post(
                "https://challenges.cloudflare.com/turnstile/v0/siteverify",
                [
                    "secret" => config("services.turnstile.secret"),
                    "response" => $token,
                    "remoteip" => $ip,
                ],
            );

            return $response->json("success") === true;
        } catch (\Exception $e) {
            Log::error("Turnstile verification failed", [
                "error" => $e->getMessage(),
                "ip" => $ip,
            ]);

            return false;
        }
    }
}
