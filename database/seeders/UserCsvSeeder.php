<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;

class UserCsvSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $path = database_path("seeders/data/users.csv");

        if (!file_exists($path)) {
            $this->command->error("CSV file not found: {$path}");
            return;
        }

        $file = fopen($path, "r");

        $header = fgetcsv($file);

        while (($row = fgetcsv($file)) !== false) {
            User::create([
                "full_name" => $row[0],
                "email" => $row[1],
                "password" => bcrypt($row[2]),
                "is_active" => (bool) $row[3],
                "last_login" => $row[4],
            ]);
        }

        fclose($file);

        $this->command->info("Users imported successfully.");
    }
}
