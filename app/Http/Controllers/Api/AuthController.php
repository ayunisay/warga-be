<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Citizen;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'login' => 'nullable|string',
            'email' => 'nullable|string',
            'nik' => 'nullable|string',
            'password' => 'nullable|string',
            'role' => 'nullable|string',
        ]);

        $loginIdentifier = $request->input('login') ?? $request->input('email') ?? $request->input('nik');

        // Cari user berdasarkan email atau blind index NIK (UU PDP)
        $nikBindex = $loginIdentifier ? hash_hmac('sha256', trim($loginIdentifier), config('app.key')) : null;

        $user = User::where('email', $loginIdentifier)
            ->when($nikBindex, function ($q) use ($nikBindex) {
                $q->orWhere('nik_bindex', $nikBindex);
            })
            ->first();

        // Jika quick login by role (untuk demo)
        if (!$user && $request->filled('role')) {
            $user = User::where('role', $request->role)->first();
        }

        // Jika user warga tapi belum dibuat di tabel users, coba cek di tabel citizens
        if (!$user && $request->filled('nik')) {
            $citizenNikBindex = hash_hmac('sha256', trim($request->nik), config('app.key'));
            $citizen = Citizen::where('nik_bindex', $citizenNikBindex)->first();
            if ($citizen) {
                $user = User::create([
                    'name' => $citizen->nama,
                    'nik' => $citizen->nik,
                    'password' => Hash::make('password'),
                    'role' => 'Warga',
                    'jabatan' => "Warga ({$citizen->blok} No. {$citizen->no_rumah})",
                    'telepon' => $citizen->telepon,
                    'avatar_initial' => strtoupper(substr($citizen->nama, 0, 2)),
                    'status' => 'Aktif',
                ]);
            }
        }

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Pengguna tidak ditemukan dengan kredensial tersebut.',
            ], 404);
        }

        // Verifikasi password (jika diisi dan bukan bypass demo)
        if ($request->filled('password') && $request->password !== 'password' && !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Password yang Anda masukkan salah.',
            ], 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        // Ambil data warga terkait bila user adalah Warga
        $citizenData = null;
        if ($user->nik_bindex) {
            $citizenData = Citizen::where('nik_bindex', $user->nik_bindex)->first();
        }

        return response()->json([
            'success' => true,
            'message' => 'Login berhasil',
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'nama' => $user->name,
                'email' => $user->email,
                'nik' => $user->nik,
                'nikMasked' => $user->nik_masked,
                'role' => $user->role,
                'jabatan' => $user->jabatan,
                'telepon' => $user->telepon,
                'teleponMasked' => $user->telepon_masked,
                'avatarInitial' => $user->avatar_initial,
                'citizen' => $citizenData,
            ],
        ]);
    }

    public function logout(Request $request)
    {
        if ($request->user()) {
            $request->user()->currentAccessToken()->delete();
        }

        return response()->json([
            'success' => true,
            'message' => 'Logout berhasil',
        ]);
    }

    public function me(Request $request)
    {
        $user = $request->user();
        $citizen = $user->nik_bindex ? Citizen::where('nik_bindex', $user->nik_bindex)->first() : null;

        return response()->json([
            'success' => true,
            'user' => [
                'id' => $user->id,
                'nama' => $user->name,
                'email' => $user->email,
                'nik' => $user->nik,
                'nikMasked' => $user->nik_masked,
                'role' => $user->role,
                'jabatan' => $user->jabatan,
                'telepon' => $user->telepon,
                'teleponMasked' => $user->telepon_masked,
                'avatarInitial' => $user->avatar_initial,
                'citizen' => $citizen,
            ],
        ]);
    }

    public function users()
    {
        $users = User::all()->map(function ($u) {
            return [
                'id' => $u->id,
                'nama' => $u->name,
                'jabatan' => $u->jabatan,
                'role' => $u->role,
                'email' => $u->email,
                'telp' => $u->telepon,
                'telpMasked' => $u->telepon_masked,
                'status' => $u->status,
                'avatarInitial' => $u->avatar_initial,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $users,
        ]);
    }

    public function storeUser(Request $request)
    {
        $request->validate([
            'nama' => 'required|string',
            'role' => 'required|string',
            'email' => 'nullable|email',
        ]);

        $user = User::create([
            'name' => $request->nama,
            'role' => $request->role,
            'jabatan' => $request->jabatan ?? "{$request->role} RT 007",
            'email' => $request->email,
            'telepon' => $request->telp ?? $request->telepon,
            'password' => Hash::make($request->password ?? 'password'),
            'avatar_initial' => strtoupper(substr($request->nama, 0, 2)),
            'status' => $request->status ?? 'Aktif',
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $user->id,
                'nama' => $user->name,
                'jabatan' => $user->jabatan,
                'role' => $user->role,
                'email' => $user->email,
                'telp' => $user->telepon,
                'telpMasked' => $user->telepon_masked,
                'status' => $user->status,
                'avatarInitial' => $user->avatar_initial,
            ],
        ], 201);
    }

    public function updateUser(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $updateData = [];
        if ($request->has('nama')) $updateData['name'] = $request->nama;
        if ($request->has('role')) $updateData['role'] = $request->role;
        if ($request->has('jabatan')) $updateData['jabatan'] = $request->jabatan;
        if ($request->has('email')) $updateData['email'] = $request->email;
        if ($request->has('telp')) $updateData['telepon'] = $request->telp;
        if ($request->has('status')) $updateData['status'] = $request->status;

        $user->update($updateData);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $user->id,
                'nama' => $user->name,
                'jabatan' => $user->jabatan,
                'role' => $user->role,
                'email' => $user->email,
                'telp' => $user->telepon,
                'telpMasked' => $user->telepon_masked,
                'status' => $user->status,
                'avatarInitial' => $user->avatar_initial,
            ],
        ]);
    }

    public function destroyUser($id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'Pengguna berhasil dihapus',
        ]);
    }
}