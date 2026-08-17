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

        $users = $query->orderBy($sort, $order)
                      ->paginate(10)
                      ->withQueryString();

        $roles = Role::all();
        return view('superadmin.users.index', compact('users', 'roles'));
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
}
