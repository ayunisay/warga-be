<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Surat;

class SuratController extends Controller
{
    public function index(Request $request)
    {
        $query = Surat::query();

        if ($request->filled('nik')) {
            $bindex = hash_hmac('sha256', trim($request->nik), config('app.key'));
            $query->where('nik_bindex', $bindex);
        }

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        $surats = $query->orderBy('created_at', 'desc')->get();

        // Ensure token is persisted on older records
        foreach ($surats as $surat) {
            if (empty($surat->verify_token)) {
                $surat->verify_token = hash('sha256', $surat->id . '-' . $surat->no_surat . '-' . config('app.key'));
                $surat->saveQuietly();
            }
        }

        return response()->json([
            'success' => true,
            'data' => $surats,
            'meta' => [
                'encryption' => 'AES-256-CBC at rest',
                'compliance' => 'UU PDP No. 27 Tahun 2022',
            ]
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'jenisSurat' => 'nullable|string',
            'jenis_surat' => 'nullable|string',
            'pemohon' => 'required|string',
            'nik' => 'required|string',
            'tujuan' => 'required|string',
        ]);

        $customId = $request->id ?? 'SRT-' . date('Y') . '-' . sprintf('%03d', Surat::count() + 1);
        $noSurat = $request->noSurat ?? $request->no_surat ?? sprintf('%03d/RT.007-RW.030/TP/%s/%s', Surat::count() + 1, date('m'), date('Y'));
        $verifyToken = hash('sha256', $customId . '-' . $noSurat . '-' . config('app.key'));

        $surat = Surat::create([
            'id' => $customId,
            'no_surat' => $noSurat,
            'jenis_surat' => $request->jenisSurat ?? $request->jenis_surat ?? 'Surat Pengantar',
            'pemohon' => $request->pemohon,
            'nik' => trim($request->nik),
            'alamat' => $request->alamat ?? '',
            'tujuan' => $request->tujuan,
            'tanggal_pengajuan' => $request->tanggalPengajuan ?? $request->tanggal_pengajuan ?? date('d F Y'),
            'status' => 'Menunggu Verifikasi',
            'urgensi' => $request->urgensi ?? 'Biasa',
            'verify_token' => $verifyToken,
            'penandatangan' => 'Bpk. Bambang Pamungkas (Ketua RT 007)',
            'catatan' => $request->catatan,
            'catatan_penolakan' => null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Permohonan surat pengantar berhasil diajukan dengan enkripsi NIK (UU PDP)',
            'data' => $surat,
        ], 201);
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:Disetujui,Ditolak,Selesai,Menunggu Verifikasi',
            'catatanPenolakan' => 'nullable|string',
            'catatan_penolakan' => 'nullable|string',
        ]);

        $surat = Surat::findOrFail($id);

        $updateData = ['status' => $request->status];

        if ($request->status === 'Ditolak') {
            $updateData['catatan_penolakan'] = $request->catatanPenolakan ?? $request->catatan_penolakan ?? 'Permohonan surat belum memenuhi persyaratan administratif RT 007.';
        } else if ($request->status === 'Disetujui' || $request->status === 'Selesai') {
            $updateData['catatan_penolakan'] = null;
            $updateData['disetujui_pada'] = now();
            if (empty($surat->verify_token)) {
                $updateData['verify_token'] = hash('sha256', $surat->id . '-' . $surat->no_surat . '-' . config('app.key'));
            }
        }

        $surat->update($updateData);

        return response()->json([
            'success' => true,
            'message' => "Status surat berhasil diubah menjadi {$request->status}",
            'data' => $surat->fresh(),
        ]);
    }

    /**
     * Endpoint Verifikasi Keaslian Surat Publik (Scan QR Code HP)
     */
    public function verifyToken($token)
    {
        $surat = Surat::where('verify_token', $token)->first();

        // Fallback: check if matches computed hash
        if (!$surat) {
            foreach (Surat::all() as $item) {
                $computed = hash('sha256', $item->id . '-' . $item->no_surat . '-' . config('app.key'));
                if ($computed === $token) {
                    $surat = $item;
                    $surat->verify_token = $computed;
                    $surat->saveQuietly();
                    break;
                }
            }
        }

        if (!$surat) {
            return response()->json([
                'success' => false,
                'status' => 'TIDAK_VALID',
                'message' => 'Surat tidak terdaftar atau token verifikasi tidak ditemukan dalam basis data resmi RT 007',
            ], 404);
        }

        $isValid = in_array($surat->status, ['Disetujui', 'Selesai']);

        return response()->json([
            'success' => true,
            'status' => $isValid ? 'VALID' : 'NON_AKTIF',
            'message' => $isValid ? 'Dokumen Surat Pengantar Resmi & Sah Terdaftar' : 'Surat masih dalam status: ' . $surat->status,
            'data' => [
                'id' => $surat->id,
                'no_surat' => $surat->no_surat,
                'jenis_surat' => $surat->jenis_surat,
                'pemohon' => $surat->pemohon,
                'nik_masked' => $surat->nik_masked,
                'alamat' => $surat->alamat,
                'tujuan' => $surat->tujuan,
                'tanggal_pengajuan' => $surat->tanggal_pengajuan,
                'tanggal_disetujui' => $surat->disetujui_pada ? \Carbon\Carbon::parse($surat->disetujui_pada)->translatedFormat('d F Y') : ($isValid ? $surat->tanggal_pengajuan : '-'),
                'status' => $surat->status,
                'is_valid' => $isValid,
                'penandatangan' => $surat->penandatangan ?? 'Bpk. Bambang Pamungkas (Ketua RT 007)',
                'institusi' => 'Rukun Tetangga 007 / RW 030, Kelurahan Teluk Pucung, Kecamatan Bekasi Utara',
                'verify_token' => $surat->verify_token,
                'verified_at' => now()->toIso8601String(),
            ]
        ]);
    }
}