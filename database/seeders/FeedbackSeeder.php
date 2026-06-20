<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Carbon\Carbon;
use App\Models\Feedback;
use App\Models\Student;
use App\Models\Office;

class FeedbackSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $path = database_path("seeders/data/sample-comments.csv");

        if (!file_exists($path)) {
            $this->command->error("feedbacks.csv not found");
            return;
        }

        $file = fopen($path, "r");

        $created = 0;
        $skipped = 0;

        fgetcsv($file);

        $officeId = Office::where("office_name", "SASO Office")->value("id");

        $students = Student::pluck("id")->toArray();

        if (empty($students)) {
            $this->command->error("No students found");
            return;
        }

        while (($row = fgetcsv($file)) !== false) {
            $raw_text = $row[0] ?? null;

            if (!$raw_text || strlen(trim($raw_text)) < 5) {
                $skipped++;
                continue;
            }

            $createdAt = fake()->dateTimeBetween("-1 year", "now");

            Feedback::create([
                "student_id" => $students[array_rand($students)],
                "office_id" => $officeId,
                "raw_text" => $raw_text,
                "status" => "pending",
                "is_anonymous" => (bool) rand(0, 1),
                "is_summarized" => false,
                "created_at" => $createdAt,
                "updated_at" => $createdAt,
            ]);

            $created++;
        }

        fclose($file);

        $this->command->info("Done!");
        $this->command->info("Created: {$created} | Skipped: {$skipped}");
    }
}
