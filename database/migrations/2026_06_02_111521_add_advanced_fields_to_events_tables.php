<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tambah skema form kustom & token di tabel events
        Schema::table('events', function (Blueprint $table) {
            $table->longText('form_schema')->nullable()->after('description'); // Kolom JSON untuk fields kustom ala GForm
            $table->string('attendance_token', 20)->nullable()->after('quota'); // Token acak kunci absensi
            $table->boolean('is_attendance_open')->default(false)->after('attendance_token'); // Saklar buka/tutup absen
        });

        // 2. Tambah tampungan jawaban kustom & waktu absen di tabel registrasi event
        Schema::table('event_registrations', function (Blueprint $table) {
            $table->longText('form_values')->nullable()->after('event_id'); // Tampungan jawaban JSON dari user
            $table->timestamp('attended_at')->nullable()->after('created_at'); // Jam absen peserta
        });
    }

    public function down(): void
    {
        Schema::table('event_registrations', function (Blueprint $table) {
            $table->dropColumn(['form_values', 'attended_at']);
        });

        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn(['form_schema', 'attendance_token', 'is_attendance_open']);
        });
    }
};
