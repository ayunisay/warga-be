<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RtInfo extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $appends = [
        'appName',
        'kodePos',
        'alamatSekretariat',
        'ketuaRt',
    ];

    public function getAppNameAttribute() { return $this->attributes['app_name'] ?? null; }
    public function getKodePosAttribute() { return $this->attributes['kode_pos'] ?? null; }
    public function getAlamatSekretariatAttribute() { return $this->attributes['alamat_sekretariat'] ?? null; }
    public function getKetuaRtAttribute() { return $this->attributes['ketua_rt'] ?? null; }
}
