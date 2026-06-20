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

            $intendedOffice = session()->pull("intended_office");

            if ($intendedOffice) {
                return redirect()->route(
                    "student.feedback.show",
                    $intendedOffice,
                );
            }

            return redirect("/");
        } catch (\Exception $e) {
            logger()->error("Google callback failed: " . $e->getMessage());
            abort(500, $e->getMessage());
        }
    }

    public function logout(Request $request): RedirectResponse
    {
        $intendedOffice = $request->input("office");

        auth("student")->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($intendedOffice) {
            session()->put("intended_office", $intendedOffice);
        }

        return redirect()->route("student.auth.google");
    }
}
