<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransferVerification extends Model
{
    use HasFactory;

    protected $keyType = 'string';
    public $incrementing = false;
    protected $guarded = [];

    protected $casts = [
        'nominal' => 'float',
    ];

    protected $appends = [
        'citizenId',
        'namaWarga',
        'noRumah',
        'rekeningTujuan',
        'tanggalTransfer',
        'noReferensi',
        'buktiUrl',
        'catatanWarga',
        'catatanBendahara',
    ];

    public function getCitizenIdAttribute() { return $this->attributes['citizen_id'] ?? null; }
    public function getNamaWargaAttribute() { return $this->attributes['nama_warga'] ?? null; }
    public function getNoRumahAttribute() { return $this->attributes['no_rumah'] ?? null; }
    public function getRekeningTujuanAttribute() { return $this->attributes['rekening_tujuan'] ?? null; }
    public function getTanggalTransferAttribute() { return $this->attributes['tanggal_transfer'] ?? null; }
    public function getNoReferensiAttribute() { return $this->attributes['no_referensi'] ?? null; }
    public function getBuktiUrlAttribute() { return $this->attributes['bukti_url'] ?? null; }
    public function getCatatanWargaAttribute() { return $this->attributes['catatan_warga'] ?? null; }
    public function getCatatanBendaharaAttribute() { return $this->attributes['catatan_bendahara'] ?? null; }

    public function citizen()
    {
        return $this->belongsTo(Citizen::class, 'citizen_id');
    }
}
