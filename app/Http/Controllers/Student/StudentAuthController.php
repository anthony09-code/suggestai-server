<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Services\Student\StudentAuthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class StudentAuthController extends Controller
{
    public function __construct(
        private StudentAuthService $studentAuthService,
    ) {}

    public function redirect_to_google(): RedirectResponse
    {
        return Socialite::driver("google")->redirect();
    }

    public function handle_google_callback(Request $request): RedirectResponse
    {
        try {
            $googleUser = Socialite::driver("google")->user();

            $student = $this->studentAuthService->find_or_create_student(
                $googleUser,
            );

            if (!$student->is_active) {
                return redirect()
                    ->route("student.auth.google")
                    ->withErrors([
                        "email" => "Your account has been deactivated.",
                    ]);
            }

            auth("student")->login($student);

            return redirect()->intended(
                route("student.offices", session("intended_office", "/")),
            );
        } catch (\Exception $e) {
            return redirect()
                ->route("student.auth.google")
                ->withErrors([
                    "error" => "Google login failed. Please try again.",
                ]);
        }
    }

    public function logout(Request $request): RedirectResponse
    {
        auth("student")->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route("student.auth.google");
    }
}
