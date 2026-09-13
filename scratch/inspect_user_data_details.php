<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Registration;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo "=== USERS COLUMNS ===\n";
print_r(Schema::getColumnListing('users'));

echo "=== USER_PROFILES COLUMNS ===\n";
print_r(Schema::getColumnListing('user_profiles'));

echo "=== ADDRESSES COLUMNS ===\n";
print_r(Schema::getColumnListing('addresses'));

echo "=== REGISTRATIONS COLUMNS ===\n";
print_r(Schema::getColumnListing('registrations'));

echo "=== REGISTRATION_STAGE_DATA COLUMNS ===\n";
print_r(Schema::getColumnListing('registration_stage_data'));

echo "=== PROGRAM_BIODATA_SUBMISSIONS COLUMNS ===\n";
print_r(Schema::getColumnListing('program_biodata_submissions'));

echo "=== SAMPLE REGISTRATION FOR PROGRAM 11 ===\n";
$sampleReg = Registration::where('program_id', 11)->with(['user.profile', 'user.address'])->first();
if ($sampleReg) {
    echo "User: " . $sampleReg->user->name . " (" . $sampleReg->user->email . ")\n";
    echo "Profile:\n";
    print_r($sampleReg->user->profile ? $sampleReg->user->profile->toArray() : null);
    echo "Address:\n";
    print_r($sampleReg->user->address ? $sampleReg->user->address->toArray() : null);
}

$stageDataCount = DB::table('registration_stage_data')->where('registration_id', $sampleReg->id ?? 0)->count();
echo "Stage data count for sample: $stageDataCount\n";

$bioSubCount = DB::table('program_biodata_submissions')->where('registration_id', $sampleReg->id ?? 0)->count();
echo "Bio submission count for sample: $bioSubCount\n";
