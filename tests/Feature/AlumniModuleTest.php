<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Program;
use App\Models\Registration;
use App\Models\AlumniProfile;
use App\Models\AlumniProgram;
use App\Models\UserAlumni;
use App\Models\AlumniVerificationRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AlumniModuleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that changing a registration's status to passed triggers auto-alumni generation.
     */
    public function test_auto_alumni_generation_on_graduation(): void
    {
        // 1. Create a user
        $user = User::factory()->create([
            'status' => 'Peserta'
        ]);

        // 2. Create a program
        $program = Program::create([
            'name' => 'Program Kepemudaan IHI 2026',
            'slug' => 'program-kepemudaan-ihi-2026',
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
            'status' => 'published',
            'total_hours' => 40
        ]);

        // 3. Create a registration for the user in the program
        $registration = Registration::create([
            'user_id' => $user->id,
            'program_id' => $program->id,
            'status' => 'process'
        ]);

        // Assert initial states
        $this->assertEquals('Peserta', $user->fresh()->status);
        $this->assertDatabaseMissing('alumni_profiles', ['user_id' => $user->id]);
        $this->assertDatabaseMissing('alumni_programs', ['program_id' => $program->id]);

        // 4. Update status to passed (triggers updated event hook)
        $registration->status = 'passed';
        $registration->save();

        // 5. Assert user status is updated to Alumni
        $this->assertEquals('Alumni', $user->fresh()->status);

        // 6. Assert database entries are created
        $this->assertDatabaseHas('alumni_profiles', [
            'user_id' => $user->id,
        ]);

        $this->assertDatabaseHas('alumni_programs', [
            'program_id' => $program->id,
            'name' => 'Program Kepemudaan IHI 2026',
            'year' => '2026'
        ]);

        $alumniProgram = AlumniProgram::where('program_id', $program->id)->first();
        $this->assertNotNull($alumniProgram);

        $this->assertDatabaseHas('user_alumni', [
            'user_id' => $user->id,
            'alumni_program_id' => $alumniProgram->id,
            'verification_status' => 'approved'
        ]);

        // 7. Verify the verification page route works
        $userAlumni = UserAlumni::where('user_id', $user->id)->first();
        $this->assertNotNull($userAlumni);

        $response = $this->get(route('public.alumni.verify', $userAlumni->uuid));
        $response->assertStatus(200);
        $response->assertSee(strtoupper($user->name));
        $response->assertSee('Program Kepemudaan IHI 2026');
    }

    /**
     * Test the manual alumni verification request submission.
     */
    public function test_manual_alumni_verification_flow(): void
    {
        Storage::fake('public');

        // 1. Create a user
        $user = User::factory()->create([
            'status' => 'Peserta'
        ]);

        // 2. Create a program
        $program = Program::create([
            'name' => 'Program Green Leadership Indonesia',
            'slug' => 'program-green-leadership-indonesia',
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
            'status' => 'published',
            'total_hours' => 40
        ]);

        // 3. Submit manual verification request
        $file = UploadedFile::fake()->image('certificate.jpg');

        $response = $this->actingAs($user)
            ->post(route('peserta.alumni.verify.store'), [
                'program_id' => $program->id,
                'certificate_scan' => $file
            ]);

        $response->assertRedirect(route('peserta.alumni.index'));
        $this->assertDatabaseHas('alumni_verification_requests', [
            'user_id' => $user->id,
            'status' => 'pending'
        ]);

        $alumniProgram = AlumniProgram::where('program_id', $program->id)->first();
        $this->assertNotNull($alumniProgram);

        $verificationRequest = AlumniVerificationRequest::where('user_id', $user->id)->first();
        $this->assertNotNull($verificationRequest);

        // 4. Admin acts to approve the request
        $admin = User::factory()->create();
        // Give Super Admin role to bypass managed program restrictions in test
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
        $admin->assignRole('Super Admin');

        $approveResponse = $this->actingAs($admin)
            ->post(route('adminprogram.alumni.verifications.process', $verificationRequest->id), [
                'action' => 'approve',
                'nilai_akhir' => 'A',
                'predikat' => 'Sangat Memuaskan',
                'jam_pelatihan' => 40
            ]);

        $approveResponse->assertRedirect(route('adminprogram.alumni.verifications'));
        
        $this->assertEquals('approved', $verificationRequest->fresh()->status);
        $this->assertEquals('Alumni', $user->fresh()->status);

        $this->assertDatabaseHas('user_alumni', [
            'user_id' => $user->id,
            'alumni_program_id' => $alumniProgram->id,
            'verification_status' => 'approved',
        ]);

        // 5. Verify the guest download certificate route is accessible and does not redirect to login
        $userAlumniModel = UserAlumni::where('user_id', $user->id)
            ->where('alumni_program_id', $alumniProgram->id)
            ->first();
        $this->assertNotNull($userAlumniModel);

        $downloadResponse = $this->get(route('public.alumni.certificate.download', $userAlumniModel->uuid));
        $this->assertNotEquals(302, $downloadResponse->getStatusCode());
    }

    /**
     * Test the instant graduation pass flow.
     */
    public function test_instant_graduation_pass_flow(): void
    {
        Storage::fake('public');

        // 1. Create a user
        $user = User::factory()->create([
            'status' => 'Peserta'
        ]);

        // 2. Create a program
        $program = Program::create([
            'name' => 'Program Speak Justice',
            'slug' => 'program-speak-justice',
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
            'status' => 'published',
            'total_hours' => 32
        ]);

        // Create a stage for the program
        $stage = \App\Models\ProgramStage::create([
            'program_id' => $program->id,
            'name' => 'Tahap Awal',
            'sequence' => 1,
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31'
        ]);

        // 3. Create a registration for the user in the program
        $registration = Registration::create([
            'user_id' => $user->id,
            'program_id' => $program->id,
            'current_stage_id' => $stage->id,
            'status' => 'process'
        ]);

        // 4. Admin acts to instantly pass the participant
        $admin = User::factory()->create();
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
        $admin->assignRole('Super Admin');

        $response = $this->actingAs($admin)
            ->post(route('adminprogram.programs.applicant.instant-pass', [$program->id, $registration->id]));

        $response->assertRedirect(route('adminprogram.programs.workspace', $program->id));
        
        $this->assertEquals('passed', $registration->fresh()->status);
        $this->assertEquals('Alumni', $user->fresh()->status);

        $alumniProgram = AlumniProgram::where('program_id', $program->id)->first();
        $this->assertNotNull($alumniProgram);

        $this->assertDatabaseHas('user_alumni', [
            'user_id' => $user->id,
            'alumni_program_id' => $alumniProgram->id,
            'verification_status' => 'approved',
        ]);
    }

    /**
     * Test the Alumni Client API integration flow (Login, Details, and Sync).
     */
    public function test_alumni_client_api_flow(): void
    {
        // Fake Client Key
        $clientKey = 'Alumni_Client_Secure_Token_2027';
        config(['services.alumni_client.key' => $clientKey]);

        \Illuminate\Support\Facades\Http::fake([
            'https://client-api.instituthijauindonesia.or.id/api/v1/alumni/receive' => \Illuminate\Support\Facades\Http::response(['success' => true, 'message' => 'Synced'], 200),
        ]);

        $user = User::factory()->create([
            'status' => 'Peserta',
            'email' => 'clientalumni@test.com',
            'password' => bcrypt('password123')
        ]);
        $program = Program::create([
            'name' => 'Client Sync Program',
            'slug' => 'client-sync-program',
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
            'status' => 'published',
            'total_hours' => 32
        ]);
        $registration = Registration::create([
            'user_id' => $user->id,
            'program_id' => $program->id,
            'status' => 'process'
        ]);

        // 1. Verify Invalid Key returns 403
        $response = $this->withHeaders(['X-ALUMNI-CLIENT-KEY' => 'wrong-key'])
            ->postJson('/api/v1/alumni-client/login', [
                'email' => 'clientalumni@test.com',
                'password' => 'password123'
            ]);
        $response->assertStatus(403);

        // 2. Verify User not passed returns 403 on login
        $response = $this->withHeaders(['X-ALUMNI-CLIENT-KEY' => $clientKey])
            ->postJson('/api/v1/alumni-client/login', [
                'email' => 'clientalumni@test.com',
                'password' => 'password123'
            ]);
        $response->assertStatus(403);
        $response->assertJsonFragment(['success' => false]);

        // 3. Mark registration as passed and try login again (should succeed)
        $registration->status = 'passed';
        $registration->save();

        $response = $this->withHeaders(['X-ALUMNI-CLIENT-KEY' => $clientKey])
            ->postJson('/api/v1/alumni-client/login', [
                'email' => 'clientalumni@test.com',
                'password' => 'password123'
            ]);
        $response->assertStatus(200);
        $response->assertJsonFragment(['success' => true]);

        // 4. Test Details Endpoint
        $response = $this->withHeaders(['X-ALUMNI-CLIENT-KEY' => $clientKey])
            ->getJson("/api/v1/alumni-client/details/{$registration->id}");
        $response->assertStatus(200);
        $response->assertJsonFragment(['success' => true]);

        // 5. Test Outbound Sync
        $response = $this->withHeaders(['X-ALUMNI-CLIENT-KEY' => $clientKey])
            ->postJson("/api/v1/alumni-client/sync/{$registration->id}");
        $response->assertStatus(200);
        $response->assertJsonFragment(['success' => true]);
    }

    /**
     * Test the sendAlumniToLms integration API endpoint.
     */
    public function test_send_alumni_to_lms_api(): void
    {
        \Illuminate\Support\Facades\Http::fake([
            'https://lms.instituthijauindonesia.or.id/api/v1/sync-from-program' => \Illuminate\Support\Facades\Http::response(['success' => true, 'message' => 'Synced'], 200),
        ]);

        $user = User::factory()->create(['status' => 'Peserta']);
        $program = Program::create([
            'name' => 'LMS Sync Program',
            'slug' => 'lms-sync-program',
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
            'status' => 'published',
            'total_hours' => 32
        ]);

        // 1. Test non-existent registration ID (404)
        $response = $this->postJson('/api/v1/alumni/send-to-lms/9999');
        $response->assertStatus(404);

        // 2. Test registration that is NOT passed (400)
        $registration = Registration::create([
            'user_id' => $user->id,
            'program_id' => $program->id,
            'status' => 'process'
        ]);

        $response = $this->postJson("/api/v1/alumni/send-to-lms/{$registration->id}");
        $response->assertStatus(400);
        $response->assertJsonFragment(['success' => false]);

        // 3. Test registration that IS passed (200 & synced)
        $registration->status = 'passed';
        $registration->save();

        $response = $this->postJson("/api/v1/alumni/send-to-lms/{$registration->id}");
        $response->assertStatus(200);
        $response->assertJsonFragment(['success' => true]);
    }
}
