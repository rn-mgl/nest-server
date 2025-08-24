<?php

namespace Database\Seeders;

use App\Models\LeaveType;
use Database\Factories\LeaveFactory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LeaveTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        LeaveType::factory(50)->create();
    }
}
