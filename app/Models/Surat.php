<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\EncryptsSensitiveAttributes;

class Surat extends Model
{
    use HasFactory, EncryptsSensitiveAttributes;

    protected $keyType = 'string';
    public $incrementing = false;
    protected $guarded = [];

    /**
     * Enkripsi AES-256 At-Rest (UU PDP)
     */
    protected $casts = [
        'nik' => 'encrypted',
        'disetujui_pada' => 'datetime',
    ];

    protected $appends = [
        'noSurat',
        'jenisSurat',
        'tanggalPengajuan',
        'catatanPenolakan',
        'nikMasked',
        'verifyToken',
        'verifyUrl',
        'disetujuiPada',
    ];

    protected static function booted()
    {
        static::creating(function ($surat) {
            if (empty($surat->verify_token)) {
                $surat->verify_token = hash('sha256', ($surat->id ?? uniqid()) . '-' . ($surat->no_surat ?? time()) . '-' . config('app.key'));
            }
            if (empty($surat->penandatangan)) {
                $surat->penandatangan = 'Bpk. Bambang Pamungkas (Ketua RT 007)';
            }
        });
    }

    public function getNoSuratAttribute() { return $this->attributes['no_surat'] ?? null; }
    public function getJenisSuratAttribute() { return $this->attributes['jenis_surat'] ?? null; }
    public function getTanggalPengajuanAttribute() { return $this->attributes['tanggal_pengajuan'] ?? null; }
    public function getCatatanPenolakanAttribute() { return $this->attributes['catatan_penolakan'] ?? null; }
    public function getVerifyTokenAttribute() {
        return $this->attributes['verify_token'] ?? hash('sha256', ($this->id ?? '') . '-' . ($this->no_surat ?? '') . '-' . config('app.key'));
    }
    public function getVerifyUrlAttribute() {
        $token = $this->verifyToken;
        return "http://localhost:5173/verifikasi-surat/{$token}";
    }
    public function getDisetujuiPadaAttribute() { return $this->attributes['disetujui_pada'] ?? null; }
}