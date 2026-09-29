<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Announcement;

class AnnouncementController extends Controller
{
    public function index()
    {
        $announcements = Announcement::orderBy('created_at', 'desc')->get();

        return response()->json([
            'success' => true,
            'data' => $announcements,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'judul' => 'required|string',
            'kategori' => 'required|string',
            'tanggal' => 'required|string',
        ]);

        $announcement = Announcement::create([
            'judul' => $request->judul,
            'kategori' => $request->kategori,
            'tanggal' => $request->tanggal,
            'waktu' => $request->waktu,
            'lokasi' => $request->lokasi,
            'icon' => $request->icon ?? '📢',
            'color' => $request->color ?? '#3B7594',
            'deskripsi' => $request->deskripsi,
            'status' => $request->status ?? 'Aktif',
            'prioritas' => $request->prioritas ?? 'Rutin',
            'penulis' => $request->penulis ?? 'Pengurus RT 007',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pengumuman berhasil dipublikasikan',
            'data' => $announcement,
        ], 201);
    }

    public function destroy($id)
    {
        $announcement = Announcement::findOrFail($id);
        $announcement->delete();

        return response()->json([
            'success' => true,
            'message' => 'Pengumuman berhasil dihapus',
        ]);
    }
}