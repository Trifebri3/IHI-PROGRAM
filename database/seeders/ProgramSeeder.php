<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ProgramSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('programs')->insert([
            [
                'name' => 'Program Inovasi Digital 2026',
                'slug' => 'program-inovasi-digital-2026',
                'description' => 'Program pelatihan untuk talenta digital masa depan.',
                'quota' => 50,
                'start_date' => Carbon::now()->addDays(10),
                'end_date' => Carbon::now()->addMonths(3),
                'status' => 'published',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Bootcamp Kepemimpinan Muda',
                'slug' => 'bootcamp-kepemimpinan-muda',
                'description' => 'Membangun karakter pemimpin yang adaptif dan solutif.',
                'quota' => 30,
                'start_date' => Carbon::now()->addDays(5),
                'end_date' => Carbon::now()->addMonths(1),
                'status' => 'published',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
