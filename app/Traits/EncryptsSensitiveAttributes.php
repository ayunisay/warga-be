<?php

namespace App\Traits;

trait EncryptsSensitiveAttributes
{
    public static function bootEncryptsSensitiveAttributes()
    {
        static::saving(function ($model) {
            // Compute Blind Index untuk NIK jika kolom ada dan nilai diisi
            if (isset($model->attributes['nik']) && !empty($model->nik)) {
                $rawNik = (string) $model->nik;
                $model->nik_bindex = static::generateBlindIndex($rawNik);
            }

            // Compute Blind Index untuk No KK jika kolom ada dan nilai diisi
            if (isset($model->attributes['no_kk']) && !empty($model->no_kk)) {
                $rawKk = (string) $model->no_kk;
                $model->no_kk_bindex = static::generateBlindIndex($rawKk);
            }
        });
    }

    public static function generateBlindIndex(string $value): string
    {
        return hash_hmac('sha256', trim($value), config('app.key'));
    }

    /**
     * Data Masking NIK sesuai UU PDP No. 27/2022
     * Format: 327501******0001 (6 digit pertama kode wilayah, 6 digit tengah disensor, 4 digit urut)
     */
    public function getNikMaskedAttribute(): string
    {
        $nik = (string) $this->nik;
        if (strlen($nik) < 10) {
            return $nik ? substr($nik, 0, 3) . '***' : '-';
        }
        return substr($nik, 0, 6) . '******' . substr($nik, -4);
    }

    /**
     * Data Masking No KK sesuai UU PDP No. 27/2022
     * Format: 327501******0001
     */
    public function getNoKkMaskedAttribute(): string
    {
        $kk = (string) $this->no_kk;
        if (strlen($kk) < 10) {
            return $kk ? substr($kk, 0, 3) . '***' : '-';
        }
        return substr($kk, 0, 6) . '******' . substr($kk, -4);
    }

    /**
     * Data Masking Telepon
     * Format: 0812-****-7890
     */
    public function getTeleponMaskedAttribute(): string
    {
        $telp = (string) $this->telepon;
        if (strlen($telp) < 8 || $telp === '-') {
            return $telp ?: '-';
        }
        $clean = str_replace([' ', '-', '+'], '', $telp);
        if (strlen($clean) >= 10) {
            return substr($clean, 0, 4) . '-****-' . substr($clean, -4);
        }
        return substr($telp, 0, 4) . '****';
    }
}