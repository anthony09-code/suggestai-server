<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\Office;

class OfficeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $path = database_path("seeders/data/offices.csv");

        if (!file_exists($path)) {
            $this->command->error("offices.csv not found at: " . $path);
            return;
        }

        $file = fopen($path, "r");

        fgetcsv($file);

        $created = 0;
        $skipped = 0;

        while (($row = fgetcsv($file)) !== false) {
            [$office_name, $description] = $row;

            if (empty(trim($office_name))) {
                continue;
            }

            $exists = Office::where(
                "office_name",
                trim($office_name),
            )->exists();

            if ($exists) {
                $this->command->warn(
                    "Skipped (already exists): {$office_name}",
                );
                $skipped++;
                continue;
            }

            Office::create([
                "office_name" => trim($office_name),
                "description" => trim($description),
                "is_active" => true,
                "access_link" =>
                    Str::slug(trim($office_name)) . "-" . Str::random(8),
            ]);

            $this->command->info("Created: {$office_name}");
            $created++;
        }

        fclose($file);

        $this->command->info("Done! Created: {$created} | Skipped: {$skipped}");
    }
}
