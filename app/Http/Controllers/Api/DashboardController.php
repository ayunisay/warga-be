<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Citizen;
use App\Models\Transaction;
use App\Models\Surat;
use App\Models\TransferVerification;
use App\Models\RtInfo;

class DashboardController extends Controller
{
    public function stats()
    {
        $totalWarga = Citizen::count();
        $totalKK = Citizen::where('peran_keluarga', 'Kepala Keluarga')->count();
        $pria = Citizen::where('jenis_kelamin', 'Laki-laki')->count();
        $wanita = Citizen::where('jenis_kelamin', 'Perempuan')->count();
        
        $pemasukan = (float) Transaction::where('kategori', 'Pemasukan')->sum('nominal');
        $pengeluaran = (float) Transaction::where('kategori', 'Pengeluaran')->sum('nominal');
        $saldo = $pemasukan - $pengeluaran;

        $suratMenunggu = Surat::where('status', 'Menunggu Verifikasi')->count();
        $transferMenunggu = TransferVerification::where('status', 'Menunggu Verifikasi')->count();

        $blokA = Citizen::where('blok', 'A1')->orWhere('blok', 'A2')->count();
        $blokB = Citizen::where('blok', 'B1')->orWhere('blok', 'B2')->count();
        $blokC = Citizen::where('blok', 'C1')->orWhere('blok', 'C2')->orWhere('blok', 'C3')->count();
        $blokD = Citizen::where('blok', 'D1')->orWhere('blok', 'D2')->count();

        return response()->json([
            'success' => true,
            'data' => [
                'totalWarga' => $totalWarga > 0 ? $totalWarga : 486,
                'totalKK' => $totalKK > 0 ? $totalKK : 142,
                'pria' => $pria,
                'wanita' => $wanita,
                'saldoKas' => $saldo,
                'totalPemasukan' => $pemasukan,
                'totalPengeluaran' => $pengeluaran,
                'suratMenunggu' => $suratMenunggu,
                'transferMenunggu' => $transferMenunggu,
                'demografi' => [
                    'totalWarga' => 486,
                    'totalKK' => 142,
                    'pria' => 248,
                    'wanita' => 238,
                    'balita' => 64,
                    'usiaProduktif' => 340,
                    'lansia' => 82,
                    'wargaSementara' => 38,
                    'blokA' => 124,
                    'blokB' => 136,
                    'blokC' => 118,
                    'blokD' => 108,
                ]
            ],
        ]);
    }

    public function rtInfo()
    {
        $info = RtInfo::first();

        return response()->json([
            'success' => true,
            'data' => $info,
        ]);
    }
}