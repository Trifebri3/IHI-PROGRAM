<?php

namespace Database\Seeders;

use App\Models\Program;
use App\Models\AlumniProgram;
use Illuminate\Database\Seeder;

class AlumniSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $programs = Program::all();

        foreach ($programs as $p) {
            $year = $p->start_date ? date('Y', strtotime($p->start_date)) : date('Y');
            
            AlumniProgram::firstOrCreate(
                ['program_id' => $p->id],
                [
                    'name' => $p->name,
                    'year' => $year,
                ]
            );
        }
    }
}
