<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class Citizen extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $appends = [
        'noKk',
        'jenisKelamin',
        'tempatLahir',
        'tanggalLahir',
        'statusKawin',
        'noRumah',
        'statusTinggal',
        'statusIuran',
        'peranKeluarga',
        'tanggalDaftar',
        'nikMasked',
        'noKkMasked',
        'teleponMasked',
    ];

    public function setNikAttribute($value)
    {
        $val = trim((string) $value);
        $this->attributes['nik'] = $val !== '' ? Crypt::encryptString($val) : null;
        $this->attributes['nik_bindex'] = $val !== '' ? hash_hmac('sha256', $val, config('app.key')) : null;
    }

    public function getNikAttribute($value)
    {
        if (!$value) return null;
        try {
            return Crypt::decryptString($value);
        } catch (\Exception $e) {
            return $value;
        }
    }

    public function setNoKkAttribute($value)
    {
        $val = trim((string) $value);
        $this->attributes['no_kk'] = $val !== '' ? Crypt::encryptString($val) : null;
        $this->attributes['no_kk_bindex'] = $val !== '' ? hash_hmac('sha256', $val, config('app.key')) : null;
    }

    public function getNoKkAttribute($value)
    {
        if (!$value) return null;
        try {
            return Crypt::decryptString($value);
        } catch (\Exception $e) {
            return $value;
        }
    }

    public function setTeleponAttribute($value)
    {
        $val = trim((string) $value);
        $this->attributes['telepon'] = ($val !== '' && $val !== '-') ? Crypt::encryptString($val) : $val;
    }

    public function getTeleponAttribute($value)
    {
        if (!$value || $value === '-') return $value ?: '-';
        try {
            return Crypt::decryptString($value);
        } catch (\Exception $e) {
            return $value;
        }
    }

    public function getNikMaskedAttribute(): string
    {
        $nik = (string) $this->nik;
        if (strlen($nik) < 10) return $nik ?: '-';
        return substr($nik, 0, 6) . '******' . substr($nik, -4);
    }

    public function getNoKkMaskedAttribute(): string
    {
        $kk = (string) $this->no_kk;
        if (strlen($kk) < 10) return $kk ?: '-';
        return substr($kk, 0, 6) . '******' . substr($kk, -4);
    }

    public function getTeleponMaskedAttribute(): string
    {
        $telp = (string) $this->telepon;
        if (strlen($telp) < 8 || $telp === '-') return $telp ?: '-';
        $clean = str_replace([' ', '-', '+'], '', $telp);
        if (strlen($clean) >= 10) {
            return substr($clean, 0, 4) . '-****-' . substr($clean, -4);
        }
        return substr($telp, 0, 4) . '****';
    }

    public function getJenisKelaminAttribute() { return $this->attributes['jenis_kelamin'] ?? null; }
    public function getTempatLahirAttribute() { return $this->attributes['tempat_lahir'] ?? null; }
    public function getTanggalLahirAttribute() { return $this->attributes['tanggal_lahir'] ?? null; }
    public function getStatusKawinAttribute() { return $this->attributes['status_kawin'] ?? null; }
    public function getNoRumahAttribute() { return $this->attributes['no_rumah'] ?? null; }
    public function getStatusTinggalAttribute() { return $this->attributes['status_tinggal'] ?? null; }
    public function getStatusIuranAttribute() { return $this->attributes['status_iuran'] ?? null; }
    public function getPeranKeluargaAttribute() { return $this->attributes['peran_keluarga'] ?? null; }
    public function getTanggalDaftarAttribute() { return $this->attributes['tanggal_daftar'] ?? null; }
}