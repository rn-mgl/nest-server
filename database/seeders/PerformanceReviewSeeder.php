<?php

namespace Database\Seeders;

use App\Models\PerformanceReview;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PerformanceReviewSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        PerformanceReview::factory(50)->create();
    }
}
