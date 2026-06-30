<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FlushSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('strategic_plan_indicator_years')->truncate();
        DB::table('strategic_plan_indicators')->truncate();
        DB::table('strategic_plan_pillars')->truncate();
        DB::table('strategic_plans')->truncate();
   }
}
