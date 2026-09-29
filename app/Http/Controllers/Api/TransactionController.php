<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Transaction;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $query = Transaction::query();

        if ($request->filled('kategori') && $request->kategori !== 'all') {
            $query->where('kategori', $request->kategori);
        }

        $transactions = $query->orderBy('created_at', 'desc')->get();

        return response()->json([
            'success' => true,
            'data' => $transactions,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'kategori' => 'required|in:Pemasukan,Pengeluaran',
            'sumber' => 'required|string',
            'nominal' => 'required|numeric|min:0',
        ]);

        $customId = $request->id ?? 'TRX-' . substr(time(), -5);

        $trx = Transaction::create([
            'id' => $customId,
            'tanggal' => $request->tanggal ?? date('d M Y'),
            'kategori' => $request->kategori,
            'sumber' => $request->sumber,
            'nominal' => $request->nominal,
            'keterangan' => $request->keterangan,
            'petugas' => $request->petugas ?? 'Bendahara RT 007',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Transaksi kas berhasil dicatat',
            'data' => $trx,
        ], 201);
    }

    public function summary()
    {
        $totalPemasukan = (float) Transaction::where('kategori', 'Pemasukan')->sum('nominal');
        $totalPengeluaran = (float) Transaction::where('kategori', 'Pengeluaran')->sum('nominal');
        $saldo = $totalPemasukan - $totalPengeluaran;

        return response()->json([
            'success' => true,
            'data' => [
                'totalPemasukan' => $totalPemasukan,
                'totalPengeluaran' => $totalPengeluaran,
                'saldo' => $saldo,
            ],
        ]);
    }
}