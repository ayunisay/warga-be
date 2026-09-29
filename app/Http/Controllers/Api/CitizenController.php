<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Citizen;

class CitizenController extends Controller
{
    public function index(Request $request)
    {
        $query = Citizen::query();

        if ($request->filled('blok') && $request->blok !== 'all') {
            $query->where('blok', $request->blok);
        }

        if ($request->filled('statusIuran') && $request->statusIuran !== 'all') {
            $query->where('status_iuran', $request->statusIuran);
        }

        $citizens = $query->orderBy('id', 'desc')->get();

        // Search filtering (mendukung pencarian nama, alamat, ataupun NIK terdekripsi)
        if ($request->filled('search')) {
            $s = strtolower(trim($request->search));
            $citizens = $citizens->filter(function ($c) use ($s) {
                return str_contains(strtolower($c->nama), $s) ||
                       str_contains(strtolower($c->alamat ?? ''), $s) ||
                       str_contains(strtolower($c->nik ?? ''), $s) ||
                       str_contains(strtolower($c->nik_masked ?? ''), $s) ||
                       str_contains(strtolower($c->no_kk ?? ''), $s);
            })->values();
        }

        return response()->json([
            'success' => true,
            'data' => $citizens,
            'meta' => [
                'encryption' => 'AES-256-CBC at rest',
                'compliance' => 'UU PDP No. 27 Tahun 2022',
            ]
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nik' => 'required|string',
            'noKk' => 'nullable|string',
            'no_kk' => 'nullable|string',
            'nama' => 'required|string',
            'jenisKelamin' => 'nullable|string',
            'jenis_kelamin' => 'nullable|string',
            'tempatLahir' => 'nullable|string',
            'tempat_lahir' => 'nullable|string',
            'tanggalLahir' => 'nullable|string',
            'tanggal_lahir' => 'nullable|string',
            'agama' => 'nullable|string',
            'statusKawin' => 'nullable|string',
            'status_kawin' => 'nullable|string',
            'pekerjaan' => 'nullable|string',
            'alamat' => 'nullable|string',
            'blok' => 'nullable|string',
            'noRumah' => 'nullable|string',
            'no_rumah' => 'nullable|string',
            'statusTinggal' => 'nullable|string',
            'status_tinggal' => 'nullable|string',
            'telepon' => 'nullable|string',
            'statusIuran' => 'nullable|string',
            'status_iuran' => 'nullable|string',
            'peranKeluarga' => 'nullable|string',
            'peran_keluarga' => 'nullable|string',
            'tanggalDaftar' => 'nullable|string',
            'tanggal_daftar' => 'nullable|string',
        ]);

        $rawNik = trim($request->nik);
        $bindex = hash_hmac('sha256', $rawNik, config('app.key'));

        // Cek keunikan NIK via blind index (tanpa mengekspos plaintext)
        if (Citizen::where('nik_bindex', $bindex)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'NIK tersebut sudah terdaftar dalam sistem RT 007.',
            ], 422);
        }

        $citizen = Citizen::create([
            'nik' => $rawNik,
            'no_kk' => $request->noKk ?? $request->no_kk ?? '-',
            'nama' => $request->nama,
            'jenis_kelamin' => $request->jenisKelamin ?? $request->jenis_kelamin ?? 'Laki-laki',
            'tempat_lahir' => $request->tempatLahir ?? $request->tempat_lahir,
            'tanggal_lahir' => $request->tanggalLahir ?? $request->tanggal_lahir,
            'agama' => $request->agama ?? 'Islam',
            'status_kawin' => $request->statusKawin ?? $request->status_kawin ?? 'Kawin',
            'pekerjaan' => $request->pekerjaan ?? '-',
            'alamat' => $request->alamat ?? '',
            'blok' => $request->blok ?? 'A1',
            'no_rumah' => $request->noRumah ?? $request->no_rumah ?? '01',
            'status_tinggal' => $request->statusTinggal ?? $request->status_tinggal ?? 'Tetap',
            'telepon' => $request->telepon ?? '-',
            'status_iuran' => $request->statusIuran ?? $request->status_iuran ?? 'Lunas',
            'peran_keluarga' => $request->peranKeluarga ?? $request->peran_keluarga ?? 'Kepala Keluarga',
            'tanggal_daftar' => $request->tanggalDaftar ?? $request->tanggal_daftar ?? date('Y-m-d'),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Data warga berhasil ditambahkan dengan enkripsi AES-256 (UU PDP)',
            'data' => $citizen,
        ], 201);
    }

    public function show($id)
    {
        $citizen = Citizen::findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $citizen,
        ]);
    }

    public function update(Request $request, $id)
    {
        $citizen = Citizen::findOrFail($id);

        $updateData = [];
        $mappings = [
            'nik' => 'nik',
            'nama' => 'nama',
            'noKk' => 'no_kk',
            'no_kk' => 'no_kk',
            'jenisKelamin' => 'jenis_kelamin',
            'jenis_kelamin' => 'jenis_kelamin',
            'tempatLahir' => 'tempat_lahir',
            'tempat_lahir' => 'tempat_lahir',
            'tanggalLahir' => 'tanggal_lahir',
            'tanggal_lahir' => 'tanggal_lahir',
            'agama' => 'agama',
            'statusKawin' => 'status_kawin',
            'status_kawin' => 'status_kawin',
            'pekerjaan' => 'pekerjaan',
            'alamat' => 'alamat',
            'blok' => 'blok',
            'noRumah' => 'no_rumah',
            'no_rumah' => 'no_rumah',
            'statusTinggal' => 'status_tinggal',
            'status_tinggal' => 'status_tinggal',
            'telepon' => 'telepon',
            'statusIuran' => 'status_iuran',
            'status_iuran' => 'status_iuran',
            'peranKeluarga' => 'peran_keluarga',
            'peran_keluarga' => 'peran_keluarga',
            'tanggalDaftar' => 'tanggal_daftar',
            'tanggal_daftar' => 'tanggal_daftar',
        ];

        foreach ($mappings as $requestKey => $dbCol) {
            if ($request->has($requestKey)) {
                $updateData[$dbCol] = $request->input($requestKey);
            }
        }

        $citizen->update($updateData);

        return response()->json([
            'success' => true,
            'message' => 'Data warga berhasil diperbarui',
            'data' => $citizen,
        ]);
    }

    public function destroy($id)
    {
        $citizen = Citizen::findOrFail($id);
        $citizen->delete();

        return response()->json([
            'success' => true,
            'message' => 'Data warga berhasil dihapus',
        ]);
    }

    public function toggleIuran($id)
    {
        $citizen = Citizen::findOrFail($id);
        $newStatus = ($citizen->status_iuran === 'Lunas') ? 'Menunggak' : 'Lunas';
        $citizen->update(['status_iuran' => $newStatus]);

        return response()->json([
            'success' => true,
            'message' => "Status iuran berhasil diubah menjadi {$newStatus}",
            'data' => $citizen,
        ]);
    }
}