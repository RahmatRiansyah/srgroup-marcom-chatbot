<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Roadmap Minggu 7: "Kelola user yang boleh akses chatbot (role/permission)".
 * Halaman ini cuma bisa diakses admin (lihat middleware 'admin' di routes/web.php).
 */
class UserManagementController extends Controller
{
    public function index()
    {
        $users = User::orderBy('name')->get();

        return view('admin.users.index', compact('users'));
    }

    /** Ubah role user ('admin' atau 'member'). */
    public function updateRole(Request $request, User $user)
    {
        $request->validate([
            'role' => 'required|in:admin,member',
        ]);

        if ($user->id === Auth::id()) {
            return back()->with('error', 'Tidak bisa mengubah role akun sendiri lewat sini.');
        }

        $user->update(['role' => $request->input('role')]);

        return back()->with('success', "Role {$user->name} diubah jadi {$request->input('role')}.");
    }

    /** Aktifkan/nonaktifkan akses chatbot seorang user (tanpa hapus akunnya). */
    public function toggleActive(User $user)
    {
        if ($user->id === Auth::id()) {
            return back()->with('error', 'Tidak bisa menonaktifkan akun sendiri.');
        }

        $user->update(['is_active' => ! $user->is_active]);

        $status = $user->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return back()->with('success', "Akun {$user->name} {$status}.");
    }
}
