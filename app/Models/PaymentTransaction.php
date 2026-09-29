<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentTransaction extends Model
{
    use HasFactory;

    protected $keyType = 'string';
    public $incrementing = false;
    protected $guarded = [];

    protected $casts = [
        'nominal' => 'float',
        'raw_response' => 'array',
        'paid_at' => 'datetime',
        'expired_at' => 'datetime',
    ];

    protected $appends = [
        'citizenId',
        'namaWarga',
        'noRumah',
        'paymentGateway',
        'paymentType',
        'vaNumber',
        'qrString',
        'qrUrl',
        'gatewayTransactionId',
        'expiredAt',
        'paidAt',
    ];

    public function getCitizenIdAttribute() { return $this->attributes['citizen_id'] ?? null; }
    public function getNamaWargaAttribute() { return $this->attributes['nama_warga'] ?? null; }
    public function getNoRumahAttribute() { return $this->attributes['no_rumah'] ?? null; }
    public function getPaymentGatewayAttribute() { return $this->attributes['payment_gateway'] ?? null; }
    public function getPaymentTypeAttribute() { return $this->attributes['payment_type'] ?? null; }
    public function getVaNumberAttribute() { return $this->attributes['va_number'] ?? null; }
    public function getQrStringAttribute() { return $this->attributes['qr_string'] ?? null; }
    public function getQrUrlAttribute() { return $this->attributes['qr_url'] ?? null; }
    public function getGatewayTransactionIdAttribute() { return $this->attributes['gateway_transaction_id'] ?? null; }
    public function getExpiredAtAttribute() { return $this->attributes['expired_at'] ?? null; }
    public function getPaidAtAttribute() { return $this->attributes['paid_at'] ?? null; }

    public function citizen()
    {
        return $this->belongsTo(Citizen::class, 'citizen_id');
    }
}
