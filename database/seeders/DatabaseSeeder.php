<?php

namespace Database\Seeders;

use App\Models\LeaveType;
use App\Models\Training;
use App\Models\User;
use Dom\Document;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        $this->call([
            OnboardingSeeder::class,
            LeaveTypeSeeder::class,
            PerformanceReviewSeeder::class,
            TrainingSeeder::class,
            DocumentSeeder::class,
            FolderSeeder::class
        ]);
    }
}
