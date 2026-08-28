<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserProfile;
use App\Models\Address;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $sort = $request->query('sort', 'created_at');
        $order = $request->query('order', 'desc');
        $search = $request->query('search');
        $roleFilter = $request->query('role');
        $statusFilter = $request->query('status');
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        $query = User::with(['roles', 'profile', 'address']);

        // Search name or email
        if ($request->filled('search')) {
            $qSearch = '%' . $search . '%';
            $query->where(function($q) use ($qSearch) {
                $q->where('name', 'LIKE', $qSearch)
                  ->orWhere('email', 'LIKE', $qSearch);
            });
        }

        // Filter by role (Spatie)
        if ($request->filled('role')) {
            $query->role($roleFilter);
        }

        // Filter by block status
        if ($request->filled('status')) {
            if ($statusFilter === 'blocked') {
                $query->where('is_blocked', true);
            } elseif ($statusFilter === 'active') {
                $query->where('is_blocked', false);
            }
        }

        // Filter by Date Range (created_at)
        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $startDate);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $endDate);
        }

        $users = $query->orderBy($sort, $order)
                      ->paginate(10)
                      ->withQueryString();

        $roles = Role::all();
        $mitigationMode = \App\Models\SystemSetting::getVal('mitigation_mode', '0');
        return view('superadmin.users.index', compact('users', 'roles', 'mitigationMode'));
    }

    /**
     * Mengekspor semua data user beserta profil dan alamat ke file Excel (CSV UTF-8)
     */
    public function export(Request $request)
    {
        $search = $request->query('search');
        $roleFilter = $request->query('role');
        $statusFilter = $request->query('status');
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        $query = User::with(['roles', 'profile', 'address', 'biodataValues']);

        // Search name or email
        if ($request->filled('search')) {
            $qSearch = '%' . $search . '%';
            $query->where(function($q) use ($qSearch) {
                $q->where('name', 'LIKE', $qSearch)
                  ->orWhere('email', 'LIKE', $qSearch);
            });
        }

        // Filter by role (Spatie)
        if ($request->filled('role')) {
            $query->role($roleFilter);
        }

        // Filter by block status
        if ($request->filled('status')) {
            if ($statusFilter === 'blocked') {
                $query->where('is_blocked', true);
            } elseif ($statusFilter === 'active') {
                $query->where('is_blocked', false);
            }
        }

        // Filter by Date Range (created_at)
        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $startDate);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $endDate);
        }

        $users = $query->orderBy('created_at', 'desc')->get();

        $filename = 'users_export_' . date('Y-m-d_H-i-s') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0'
        ];

        $callback = function() use ($users) {
            $file = fopen('php://output', 'w');
            
            // Tambahkan BOM (Byte Order Mark) agar Excel mendeteksi UTF-8 dengan benar
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            // Tambahkan instruksi delimiter agar Excel memisahkan kolom dengan benar dan tidak menyatu
            fwrite($file, "sep=,\n");

            // Load semua fields biodata secara dinamis dari database
            $biodataFields = DB::table('biodata_fields')->orderBy('id')->get();

            // Bangun CSV Header
            $csvHeaders = [
                'ID User',
                'Nama Lengkap',
                'Email',
                'Role / Hak Akses',
                'Status Akun',
                'Tanggal Registrasi'
            ];

            foreach ($biodataFields as $field) {
                $csvHeaders[] = $field->name;
            }

            $csvHeaders = array_merge($csvHeaders, [
                'Foto Profil (URL)',
                'Negara',
                'Provinsi',
                'Kabupaten/Kota',
                'Kecamatan',
                'Desa/Kelurahan',
                'Kampung/Dusun',
                'Detail Alamat / Patokan'
            ]);

            fputcsv($file, $csvHeaders);

            foreach ($users as $user) {
                $roles = $user->roles->pluck('name')->implode(', ') ?: 'Tidak ada role';
                $status = $user->is_blocked ? 'Diblokir' : 'Aktif';
                $photoPath = $user->profile?->profile_photo_path ? asset('storage/' . $user->profile->profile_photo_path) : 'Belum unggah';
                
                $row = [
                    $user->id,
                    $user->name,
                    $user->email,
                    $roles,
                    $status,
                    $user->created_at ? $user->created_at->format('Y-m-d H:i:s') : '-'
                ];

                // Ambil nilai biodata dinamis untuk masing-masing kolom
                foreach ($biodataFields as $field) {
                    $val = $user->biodataValues->where('biodata_field_id', $field->id)->first()?->value ?? '-';
                    $row[] = $val;
                }

                $row = array_merge($row, [
                    $photoPath,
                    $user->address?->negara ?? '-',
                    $user->address?->provinsi ?? '-',
                    $user->address?->kabupaten ?? '-',
                    $user->address?->kecamatan ?? '-',
                    $user->address?->desa ?? '-',
                    $user->address?->kampung ?? '-',
                    $user->address?->detail_alamat ?? '-'
                ]);

                fputcsv($file, $row);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Menyimpan pengguna baru dan menetapkan role hak akses
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'role' => 'required|exists:roles,name',
        ]);

        DB::transaction(function () use ($request) {
            // 1. Create User
            $user = User::create([
                'name' => trim($request->name),
                'email' => trim($request->email),
                'password' => bcrypt($request->password),
            ]);

            // 2. Assign Role (Spatie)
            $user->assignRole($request->role);

            // 3. Create Empty Profile & Address
            UserProfile::create([
                'user_id' => $user->id,
            ]);

            Address::create([
                'user_id' => $user->id,
            ]);
        });

        return redirect()->back()->with('success', 'Pengguna baru berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'role' => 'required|exists:roles,name',
            'password' => 'nullable|string|min:8',
        ]);

        $admin = Auth::user();

        DB::transaction(function () use ($request, $user, $admin) {
            $updateData = [
                'name' => $request->name,
                'email' => $request->email,
            ];

            $passwordChanged = false;
            if ($request->filled('password')) {
                $updateData['password'] = bcrypt($request->password);
                $passwordChanged = true;
            }

            // 1. Update User
            $user->update($updateData);

            // 2. Update Role (Spatie)
            $user->syncRoles([$request->role]);

            // 3. Update Profil (Upsert)
            UserProfile::updateOrCreate(
                ['user_id' => $user->id],
                ['biodata_lengkap' => $request->biodata]
            );

            // 4. Log Action
            AuditLog::create([
                'user_id' => $admin->id,
                'action' => 'update_user',
                'target_user_id' => $user->id,
                'details' => 'Mengupdate data user' . ($passwordChanged ? ' & mengganti password' : ''),
                'ip_address' => $request->ip()
            ]);
        });

        return redirect()->back()->with('success', 'Profil, Role, dan Kredensial user berhasil diperbarui.');
    }

    /**
     * Impersonate a user
     */
    public function impersonate(Request $request, $id)
    {
        $admin = Auth::user();
        if (!$admin->hasRole('Super Admin')) {
            abort(403, 'Aksi ini tidak diijinkan.');
        }

        $targetUser = User::findOrFail($id);

        // Prevent self-impersonation
        if ($admin->id === $targetUser->id) {
            return redirect()->back()->with('error', 'Anda tidak bisa melakukan impersonasi diri sendiri.');
        }

        // Store admin ID in session
        $request->session()->put('impersonator_id', $admin->id);

        // Login as target user
        Auth::login($targetUser);

        // Log Action
        AuditLog::create([
            'user_id' => $admin->id,
            'action' => 'impersonate_start',
            'target_user_id' => $targetUser->id,
            'details' => 'Mulai impersonasi sebagai pengguna: ' . $targetUser->name,
            'ip_address' => $request->ip()
        ]);

        return redirect()->route('dashboard')
            ->with('success', 'Masuk ke mode impersonasi sebagai: ' . $targetUser->name);
    }

    /**
     * Toggle block status for a user
     */
    public function toggleBlock(Request $request, $id)
    {
        $admin = Auth::user();
        if (!$admin->hasRole('Super Admin')) {
            abort(403, 'Aksi ini tidak diijinkan.');
        }

        $user = User::findOrFail($id);

        // Prevent blocking self
        if ($admin->id === $user->id) {
            return redirect()->back()->with('error', 'Anda tidak bisa memblokir akun Anda sendiri.');
        }

        $user->is_blocked = !$user->is_blocked;
        $user->save();

        $action = $user->is_blocked ? 'block_user' : 'unblock_user';
        $actionText = $user->is_blocked ? 'Memblokir' : 'Membuka blokir';

        AuditLog::create([
            'user_id' => $admin->id,
            'action' => $action,
            'target_user_id' => $user->id,
            'details' => "{$actionText} akses akun user: " . $user->name,
            'ip_address' => $request->ip()
        ]);

        return redirect()->back()->with('success', "Akun user {$user->name} berhasil " . ($user->is_blocked ? 'diblokir.' : 'diaktifkan kembali.'));
    }

    /**
     * Delete a user permanently
     */
    public function destroy(Request $request, $id)
    {
        $admin = Auth::user();
        if (!$admin->hasRole('Super Admin')) {
            abort(403, 'Aksi ini tidak diijinkan.');
        }

        $user = User::findOrFail($id);

        // Prevent deleting self
        if ($admin->id === $user->id) {
            return redirect()->back()->with('error', 'Anda tidak bisa menghapus akun Anda sendiri.');
        }

        $userName = $user->name;

        DB::transaction(function () use ($user, $admin, $request) {
            // Delete user (cascade delete handles profiles, addresses, registrations, verifications)
            $user->delete();

            AuditLog::create([
                'user_id' => $admin->id,
                'action' => 'delete_user',
                'details' => "Menghapus akun user secara permanen: " . $user->name,
                'ip_address' => $request->ip()
            ]);
        });

        return redirect()->back()->with('success', "Akun user {$userName} berhasil dihapus secara permanen dari sistem.");
    }

    /**
     * Melewati verifikasi email user (bypass email verification)
     */
    public function bypassEmail(Request $request, $id)
    {
        $admin = Auth::user();
        if (!$admin->hasRole('Super Admin')) {
            abort(403, 'Aksi ini tidak diijinkan.');
        }

        $user = User::findOrFail($id);
        $user->email_verified_at = now();
        $user->save();

        AuditLog::create([
            'user_id' => $admin->id,
            'action' => 'bypass_email_verification',
            'target_user_id' => $user->id,
            'details' => "Melewati verifikasi email untuk user: " . $user->name,
            'ip_address' => $request->ip()
        ]);

        return redirect()->back()->with('success', "Verifikasi email untuk user {$user->name} berhasil dilewati.");
    }

    /**
     * Memaksa reset password ke default dan mewajibkan ganti password saat login berikutnya
     */
    public function forcePassword(Request $request, $id)
    {
        $admin = Auth::user();
        if (!$admin->hasRole('Super Admin')) {
            abort(403, 'Aksi ini tidak diijinkan.');
        }

        $user = User::findOrFail($id);
        
        // Atur password ke default dan set flag must_change_password ke true
        // Karena must_change_password=true, user bisa login dengan password APA PUN dan wajib menggantinya setelah masuk.
        $user->password = bcrypt('pendaftarIHI2026!');
        $user->must_change_password = true;
        $user->save();

        AuditLog::create([
            'user_id' => $admin->id,
            'action' => 'force_password_reset',
            'target_user_id' => $user->id,
            'details' => "Memaksa reset password ke default & mewajibkan ganti password untuk user: " . $user->name,
            'ip_address' => $request->ip()
        ]);

        return redirect()->back()->with('success', "Password user {$user->name} berhasil diset ke default. User dapat login dengan password APA PUN dan wajib menggantinya setelah login.");
    }

    /**
     * Mengaktifkan/menonaktifkan mode mitigasi pendaftaran secara global
     */
    public function toggleMitigationGlobal(Request $request)
    {
        $admin = Auth::user();
        if (!$admin->hasRole('Super Admin')) {
            abort(403, 'Aksi ini tidak diijinkan.');
        }

        $current = \App\Models\SystemSetting::getVal('mitigation_mode', '0');
        $newVal = ($current === '1') ? '0' : '1';
        \App\Models\SystemSetting::setVal('mitigation_mode', $newVal);

        $statusText = ($newVal === '1') ? 'diaktifkan secara global' : 'dinonaktifkan secara global';

        AuditLog::create([
            'user_id' => $admin->id,
            'action' => 'toggle_mitigation_mode_global',
            'details' => "Mengubah status mitigasi global menjadi: {$statusText}",
            'ip_address' => $request->ip()
        ]);

        return redirect()->back()->with('success', "Mode Mitigasi Pendaftaran berhasil {$statusText}!");
    }

    /**
     * Memproses aksi massal (bulk action) untuk beberapa user sekaligus
     */
    public function bulkAction(Request $request)
    {
        $admin = Auth::user();
        if (!$admin->hasRole('Super Admin')) {
            abort(403, 'Aksi ini tidak diijinkan.');
        }

        $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
            'action' => 'required|string|in:bypass_email,auto_password,block,delete',
        ]);

        $userIds = $request->user_ids;
        $action = $request->action;

        // Filter id admin sendiri agar tidak bisa memblokir/menghapus diri sendiri
        $userIdsFiltered = array_filter($userIds, function($id) use ($admin) {
            return (int)$id !== $admin->id;
        });

        if (empty($userIdsFiltered) && in_array($action, ['block', 'delete'])) {
            return redirect()->back()->with('error', 'Anda tidak bisa memblokir atau menghapus akun Anda sendiri.');
        }

        DB::transaction(function () use ($userIdsFiltered, $action, $admin, $request, $userIds) {
            $count = count($userIdsFiltered);

            if ($action === 'bypass_email') {
                // Untuk bypass email, admin sendiri boleh ikut ke-bypass jika tidak sengaja terpilih (aman)
                User::whereIn('id', $userIds)->update(['email_verified_at' => now()]);
                
                AuditLog::create([
                    'user_id' => $admin->id,
                    'action' => 'bulk_bypass_email',
                    'details' => "Melewati verifikasi email secara massal untuk " . count($userIds) . " user.",
                    'ip_address' => $request->ip()
                ]);
            } elseif ($action === 'auto_password') {
                User::whereIn('id', $userIdsFiltered)->update([
                    'password' => bcrypt('pendaftarIHI2026!'),
                    'must_change_password' => true
                ]);

                AuditLog::create([
                    'user_id' => $admin->id,
                    'action' => 'bulk_force_password_reset',
                    'details' => "Mengatur password massal ke default & mewajibkan ganti password untuk {$count} user.",
                    'ip_address' => $request->ip()
                ]);
            } elseif ($action === 'block') {
                User::whereIn('id', $userIdsFiltered)->update(['is_blocked' => true]);

                AuditLog::create([
                    'user_id' => $admin->id,
                    'action' => 'bulk_block_users',
                    'details' => "Memblokir akses akun secara massal untuk {$count} user.",
                    'ip_address' => $request->ip()
                ]);
            } elseif ($action === 'delete') {
                // Delete users (cascade delete handles related models)
                User::whereIn('id', $userIdsFiltered)->delete();

                AuditLog::create([
                    'user_id' => $admin->id,
                    'action' => 'bulk_delete_users',
                    'details' => "Menghapus akun secara massal untuk {$count} user.",
                    'ip_address' => $request->ip()
                ]);
            }
        });

        $messages = [
            'bypass_email' => 'Verifikasi email secara massal berhasil diproses.',
            'auto_password' => 'Password massal berhasil diatur ke default (User dapat login dengan password apa pun & wajib ganti setelah masuk).',
            'block' => 'Akun terpilih berhasil diblokir.',
            'delete' => 'Akun terpilih berhasil dihapus secara permanen.',
        ];

        return redirect()->back()->with('success', $messages[$action]);
    }
}
