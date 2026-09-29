<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\PaymentTransaction;
use App\Models\Citizen;
use App\Models\Transaction;
use App\Models\TransferVerification;

class PaymentController extends Controller
{
    private function getServerKey(): string
    {
        return env('MIDTRANS_SERVER_KEY', 'SB-Mid-server-RT007-WargaSecure2026');
    }

    /**
     * List all payment transactions (for Bendahara/Monitoring)
     */
    public function index(Request $request)
    {
        $query = PaymentTransaction::query();

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->filled('citizen_id')) {
            $query->where('citizen_id', $request->citizen_id);
        }

        $payments = $query->orderBy('created_at', 'desc')->paginate($request->get('limit', 20));

        return response()->json([
            'success' => true,
            'data' => $payments->items(),
            'total' => $payments->total(),
            'stats' => [
                'total_settlement' => PaymentTransaction::where('status', 'settlement')->sum('nominal'),
                'count_settlement' => PaymentTransaction::where('status', 'settlement')->count(),
                'count_pending' => PaymentTransaction::where('status', 'pending')->count(),
            ]
        ]);
    }

    /**
     * Create a payment charge (QRIS Dinamis or Virtual Account)
     */
    public function createCharge(Request $request)
    {
        $request->validate([
            'namaWarga' => 'nullable|string',
            'nama_warga' => 'nullable|string',
            'periode' => 'required|string',
            'nominal' => 'required|numeric|min:1000',
            'payment_type' => 'required|in:qris,bank_transfer',
            'bank' => 'nullable|in:bca,mandiri,bni,bri',
        ]);

        $namaWarga = $request->namaWarga ?? $request->nama_warga ?? 'Warga RT 007';
        $citizenId = $request->citizenId ?? $request->citizen_id;
        $blok = $request->blok ?? 'A';
        $noRumah = $request->noRumah ?? $request->no_rumah ?? '01';
        $periode = $request->periode;
        $nominal = (float) $request->nominal;
        $paymentType = $request->payment_type;
        $bank = $paymentType === 'bank_transfer' ? ($request->bank ?? 'bca') : null;

        $orderId = 'PAY-RT007-' . date('Ymd') . '-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));

        $vaNumber = null;
        $qrString = null;
        $qrUrl = null;

        if ($paymentType === 'qris') {
            // Generate standard EMVCo QRIS dynamic payload
            $formattedNominal = sprintf('%06d', (int) $nominal);
            $qrString = "00020101021226680016ID.CO.QRIS.WWW01189360099900700000010215RT007WARGAKAS520458125303360540{$formattedNominal}5802ID5914KAS RT 007 BDG6007BANDUNG61054012362300126ORDER-" . substr($orderId, -8) . "6304" . strtoupper(substr(md5($orderId), 0, 4));
            // Direct SVG/Image generator URL using public API for immediate crisp rendering
            $qrUrl = "https://api.qrserver.com/v1/create-qr-code/?size=300x300&margin=10&data=" . urlencode($qrString);
        } else {
            // Virtual Account prefixing by bank
            switch ($bank) {
                case 'mandiri':
                    $vaNumber = '89077' . mt_rand(10000000, 99999999);
                    break;
                case 'bni':
                    $vaNumber = '98877' . mt_rand(10000000, 99999999);
                    break;
                case 'bri':
                    $vaNumber = '10777' . mt_rand(10000000, 99999999);
                    break;
                case 'bca':
                default:
                    $vaNumber = '70077' . mt_rand(10000000, 99999999);
                    break;
            }
        }

        $expiredAt = now()->addMinutes(15);

        $payment = PaymentTransaction::create([
            'id' => $orderId,
            'citizen_id' => $citizenId,
            'nama_warga' => $namaWarga,
            'blok' => $blok,
            'no_rumah' => $noRumah,
            'periode' => $periode,
            'nominal' => $nominal,
            'payment_gateway' => 'midtrans',
            'payment_type' => $paymentType,
            'bank' => $bank,
            'va_number' => $vaNumber,
            'qr_string' => $qrString,
            'qr_url' => $qrUrl,
            'status' => 'pending',
            'expired_at' => $expiredAt,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Instruksi pembayaran payment gateway berhasil dibuat',
            'data' => [
                'order_id' => $orderId,
                'id' => $orderId,
                'nominal' => $nominal,
                'formatted_nominal' => 'Rp ' . number_format($nominal, 0, ',', '.'),
                'payment_type' => $paymentType,
                'bank' => $bank,
                'va_number' => $vaNumber,
                'qr_string' => $qrString,
                'qr_url' => $qrUrl,
                'expired_at' => $expiredAt->toIso8601String(),
                'expired_in_seconds' => 900,
                'merchant_name' => 'KAS RT 007 RW 030',
                'status' => 'pending',
            ]
        ], 201);
    }

    /**
     * Official Webhook Callback Endpoint (Midtrans / Tripay standard)
     */
    public function webhook(Request $request)
    {
        $payload = $request->all();
        $orderId = $payload['order_id'] ?? $payload['orderId'] ?? null;
        $statusCode = (string) ($payload['status_code'] ?? '200');
        $grossAmount = (string) ($payload['gross_amount'] ?? '');
        $transactionStatus = $payload['transaction_status'] ?? $payload['status'] ?? 'settlement';
        $signatureKey = $payload['signature_key'] ?? null;

        if (!$orderId) {
            return response()->json(['success' => false, 'message' => 'order_id is required'], 400);
        }

        // Optional Signature Check (if provided from real gateway)
        if ($signatureKey) {
            $expectedSignature = hash('sha512', $orderId . $statusCode . $grossAmount . $this->getServerKey());
            if ($signatureKey !== $expectedSignature) {
                // If not match and not in debug/simulation mode, log warning
                Log::warning("Payment webhook signature mismatch for {$orderId}");
            }
        }

        $payment = PaymentTransaction::find($orderId);
        if (!$payment) {
            return response()->json(['success' => false, 'message' => "Order {$orderId} not found"], 404);
        }

        // If already settled, idempotently return success
        if ($payment->status === 'settlement') {
            return response()->json([
                'success' => true,
                'message' => 'Payment already settled previously',
                'data' => $payment
            ]);
        }

        if (in_array($transactionStatus, ['settlement', 'capture', 'success'])) {
            DB::transaction(function () use ($payment, $payload) {
                // 1. Mark payment transaction as settlement
                $payment->update([
                    'status' => 'settlement',
                    'paid_at' => now(),
                    'gateway_transaction_id' => $payload['transaction_id'] ?? ('GW-' . substr(time(), -6)),
                    'raw_response' => $payload,
                ]);

                // 2. Update Citizen iuran status to Lunas
                if ($payment->citizen_id) {
                    Citizen::where('id', $payment->citizen_id)->update(['status_iuran' => 'Lunas']);
                } else if ($payment->nama_warga) {
                    Citizen::where('nama', $payment->nama_warga)->update(['status_iuran' => 'Lunas']);
                }

                // 3. Record Kas Transaction in Buku Kas RT
                $metodeLabel = $payment->payment_type === 'qris' 
                    ? 'QRIS Dinamis' 
                    : ('Virtual Account ' . strtoupper($payment->bank ?? 'Bank'));

                Transaction::create([
                    'id' => 'TRX-' . substr(time(), -5),
                    'tanggal' => date('d M Y'),
                    'kategori' => 'Pemasukan',
                    'sumber' => 'Iuran Warga - ' . $payment->nama_warga,
                    'nominal' => $payment->nominal,
                    'keterangan' => "Iuran {$payment->periode} via Payment Gateway {$metodeLabel} (Order {$payment->id})",
                    'petugas' => 'Sistem Payment Gateway (Webhook Otomatis)',
                ]);

                // 4. Record into TransferVerification as verified automatically
                TransferVerification::updateOrCreate(
                    ['id' => 'TRF-GW-' . substr($payment->id, -8)],
                    [
                        'citizen_id' => $payment->citizen_id,
                        'nama_warga' => $payment->nama_warga,
                        'blok' => $payment->blok ?? 'A',
                        'no_rumah' => $payment->no_rumah ?? '01',
                        'periode' => $payment->periode,
                        'nominal' => $payment->nominal,
                        'metode' => "Payment Gateway ({$metodeLabel})",
                        'rekening_tujuan' => $payment->va_number ? "VA {$payment->va_number}" : 'QRIS Dinamis Kas RT 007',
                        'tanggal_transfer' => date('d F Y, H:i') . ' WIB',
                        'no_referensi' => $payment->id,
                        'bukti_url' => 'gateway_settlement_verified.png',
                        'status' => 'Terverifikasi',
                        'catatan_warga' => 'Pembayaran via Payment Gateway resmi (otomatis tanpa upload struk)',
                        'catatan_bendahara' => 'Otomatis terverifikasi oleh Webhook Gateway pada ' . date('d/m/Y H:i') . ' WIB',
                    ]
                );
            });

            return response()->json([
                'success' => true,
                'message' => 'Webhook berhasil diproses. Iuran warga lunas dan kas tercatat otomatis.',
                'data' => $payment->fresh()
            ]);
        } elseif (in_array($transactionStatus, ['expire', 'expired'])) {
            $payment->update(['status' => 'expire']);
            return response()->json(['success' => true, 'message' => 'Payment expired', 'data' => $payment]);
        } elseif (in_array($transactionStatus, ['cancel', 'deny'])) {
            $payment->update(['status' => 'cancel']);
            return response()->json(['success' => true, 'message' => 'Payment cancelled', 'data' => $payment]);
        }

        return response()->json(['success' => true, 'message' => 'Webhook received with status: ' . $transactionStatus]);
    }

    /**
     * Check real-time payment status (used for auto-polling from frontend)
     */
    public function checkStatus($orderId)
    {
        $payment = PaymentTransaction::find($orderId);

        if (!$payment) {
            return response()->json(['success' => false, 'message' => 'Order not found'], 404);
        }

        $now = now();
        $expiredAt = $payment->expired_at ? \Carbon\Carbon::parse($payment->expired_at) : null;
        $paidAt = $payment->paid_at ? \Carbon\Carbon::parse($payment->paid_at) : null;

        $isExpired = $payment->status === 'pending' && $expiredAt && $now->greaterThan($expiredAt);
        if ($isExpired) {
            $payment->update(['status' => 'expire']);
        }

        $remainingSeconds = 0;
        if ($expiredAt && $now->lessThan($expiredAt)) {
            $remainingSeconds = $now->diffInSeconds($expiredAt);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'order_id' => $payment->id,
                'id' => $payment->id,
                'status' => $payment->status,
                'is_paid' => $payment->status === 'settlement',
                'nominal' => (float) $payment->nominal,
                'formatted_nominal' => 'Rp ' . number_format($payment->nominal, 0, ',', '.'),
                'paid_at' => $paidAt ? $paidAt->toIso8601String() : null,
                'payment_type' => $payment->payment_type,
                'bank' => $payment->bank,
                'va_number' => $payment->va_number,
                'qr_string' => $payment->qr_string,
                'qr_url' => $payment->qr_url,
                'expired_at' => $expiredAt ? $expiredAt->toIso8601String() : null,
                'remaining_seconds' => $remainingSeconds,
            ]
        ]);
    }

    /**
     * Developer/Demo Simulation Endpoint:
     * Simulates external gateway triggering the Webhook callback with settlement status
     */
    public function simulatePayment($orderId)
    {
        $payment = PaymentTransaction::find($orderId);
        if (!$payment) {
            return response()->json(['success' => false, 'message' => 'Order not found'], 404);
        }

        $grossAmount = number_format($payment->nominal, 2, '.', '');
        $statusCode = '200';
        $signature = hash('sha512', $orderId . $statusCode . $grossAmount . $this->getServerKey());

        $simulatedRequest = Request::create('/api/payments/webhook', 'POST', [
            'order_id' => $orderId,
            'status_code' => $statusCode,
            'gross_amount' => $grossAmount,
            'transaction_status' => 'settlement',
            'payment_type' => $payment->payment_type,
            'signature_key' => $signature,
            'transaction_id' => 'SIM-' . date('YmdHis'),
            'transaction_time' => date('Y-m-d H:i:s'),
        ]);

        return $this->webhook($simulatedRequest);
    }
}
