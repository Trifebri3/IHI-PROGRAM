<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class DummyUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Akun Admin Program
        $adminProgram = User::firstOrCreate(
            ['email' => 'eva@program.test'],
            [
                'name' => 'Eva',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(), // Bypass verifikasi email
            ]
        );
        $adminProgram->assignRole('Admin Program');

        // 2. Akun Reviewer (Misal untuk keperluan seleksi/keuangan)
        $reviewer = User::firstOrCreate(
            ['email' => 'ridho@program.test'],
            [
                'name' => 'Ridho Muhamad Fauzan',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
            ]
        );
        $reviewer->assignRole('Reviewer');

        // 3. Akun Peserta Biasa 1
        $peserta1 = User::firstOrCreate(
            ['email' => 'salma@program.test'],
            [
                'name' => 'Salma',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
            ]
        );
        $peserta1->assignRole('Participant');

        // 4. Akun Peserta Biasa 2 (Staff Administrasi/User biasa)
        $peserta2 = User::firstOrCreate(
            ['email' => 'amalia@program.test'],
            [
                'name' => 'Amalia Z. P.',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
            ]
        );
        $peserta2->assignRole('Participant');
    }
}
