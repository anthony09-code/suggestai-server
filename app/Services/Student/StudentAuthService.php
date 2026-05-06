<?php
namespace App\Services\Student;

use App\Models\Student;
use Laravel\Socialite\Contracts\User as GoogleUser;

class StudentAuthService
{
    public function find_or_create_student(GoogleUser $googleUser): Student
    {
        /*
         * Find or create a student based on the Google user's ID.
         */
        return Student::updateOrCreate(
            [
                "google_id" => $googleUser->getId(),
            ],
            [
                "name" => $googleUser->getName(),
                "email" => $googleUser->getEmail(),
                "profile_picture" => $googleUser->getAvatar(),
                "google_token" => $googleUser->token,
                "google_refresh_token" => $googleUser->refreshToken,
                "is_active" => true,
            ],
        );
    }
}
