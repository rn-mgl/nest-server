<?php

namespace Database\Seeders;

use App\Models\Training;
use Database\Factories\TrainingFactory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TrainingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Training::factory(50)->create();
    }
}
