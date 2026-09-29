<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\TransferVerification;
use App\Models\Citizen;
use App\Models\Transaction;

class TransferVerificationController extends Controller
{
    public function index(Request $request)
    {
        $query = TransferVerification::query();

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->filled('citizen_id')) {
            $query->where('citizen_id', $request->citizen_id);
        }

        $transfers = $query->orderBy('created_at', 'desc')->get();

        return response()->json([
            'success' => true,
            'data' => $transfers,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'namaWarga' => 'nullable|string',
            'nama_warga' => 'nullable|string',
            'periode' => 'required|string',
            'nominal' => 'required|numeric|min:0',
            'metode' => 'required|string',
        ]);

        $buktiUrl = $request->buktiUrl ?? $request->bukti_url ?? 'struk_transfer_warga.jpg';

        if ($request->hasFile('bukti_file')) {
            $path = $request->file('bukti_file')->store('transfers', 'public');
            $buktiUrl = '/storage/' . $path;
        }

        $customId = 'TRF-' . date('Y') . '-' . substr(time(), -4);

        $transfer = TransferVerification::create([
            'id' => $customId,
            'citizen_id' => $request->citizenId ?? $request->citizen_id,
            'nama_warga' => $request->namaWarga ?? $request->nama_warga ?? 'Warga RT 007',
            'blok' => $request->blok ?? 'A1',
            'no_rumah' => $request->noRumah ?? $request->no_rumah ?? '01',
            'periode' => $request->periode,
            'nominal' => $request->nominal,
            'metode' => $request->metode,
            'rekening_tujuan' => $request->rekeningTujuan ?? $request->rekening_tujuan ?? 'Bank Mandiri 156-00-1928374-1 (Kas RT 007)',
            'tanggal_transfer' => $request->tanggalTransfer ?? $request->tanggal_transfer ?? date('d F Y, H:i') . ' WIB',
            'no_referensi' => $request->noReferensi ?? $request->no_referensi ?? 'TRF-' . substr(time(), -5),
            'bukti_url' => $buktiUrl,
            'status' => 'Menunggu Verifikasi',
            'catatan_warga' => $request->catatanWarga ?? $request->catatan_warga ?? 'Pembayaran iuran mandiri',
            'catatan_bendahara' => '',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Bukti transfer berhasil dikirim untuk verifikasi bendahara',
            'data' => $transfer,
        ], 201);
    }

    public function verify(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:Terverifikasi,Ditolak',
            'catatanBendahara' => 'nullable|string',
            'catatan_bendahara' => 'nullable|string',
        ]);

        $transfer = TransferVerification::findOrFail($id);

        $status = $request->status;
        $note = $request->catatanBendahara ?? $request->catatan_bendahara;

        if ($status === 'Terverifikasi' && !$note) {
            $note = 'Pembayaran valid & telah masuk mutasi kas RT 007';
        }

        DB::transaction(function () use ($transfer, $status, $note) {
            $transfer->update([
                'status' => $status,
                'catatan_bendahara' => $note,
            ]);

            if ($status === 'Terverifikasi') {
                // 1. Update citizen iuran status to Lunas
                if ($transfer->citizen_id) {
                    Citizen::where('id', $transfer->citizen_id)->update(['status_iuran' => 'Lunas']);
                } else if ($transfer->nama_warga) {
                    Citizen::where('nama', $transfer->nama_warga)->update(['status_iuran' => 'Lunas']);
                }

                // 2. Add into kas transactions automatically
                Transaction::create([
                    'id' => 'TRX-' . substr(time(), -5),
                    'tanggal' => date('d M Y'),
                    'kategori' => 'Pemasukan',
                    'sumber' => 'Iuran Warga - ' . $transfer->nama_warga,
                    'nominal' => $transfer->nominal,
                    'keterangan' => "Verifikasi transfer iuran {$transfer->periode} ({$transfer->metode})",
                    'petugas' => 'Hj. Endang (Bendahara)',
                ]);
            }
        });

        return response()->json([
            'success' => true,
            'message' => "Transfer berhasil di-{$status}",
            'data' => $transfer->fresh(),
        ]);
    }
}