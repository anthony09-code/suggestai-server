<?php

namespace Database\Seeders;

use App\Models\Student;
use Illuminate\Database\Seeder;

class StudentSeeder extends Seeder
{
    public function run(): void
    {
        $path = database_path("seeders/data/students.csv");

        if (!file_exists($path)) {
            $this->command->error("CSV file not found.");

            return;
        }

        $file = fopen($path, "r");

        $header = fgetcsv($file);

        while (($row = fgetcsv($file)) !== false) {
            $student = array_combine($header, $row);

            Student::updateOrCreate(
                [
                    "email" => $student["email"],
                ],
                [
                    "google_id" => $student["google_id"],
                    "name" => $student["name"],
                    "profile_picture" => $student["profile_picture"],
                    "google_token" => $student["google_token"] ?: null,
                    "google_refresh_token" =>
                        $student["google_refresh_token"] ?: null,
                    "is_active" => (bool) $student["is_active"],
                ],
            );
        }

        fclose($file);
    }
}
