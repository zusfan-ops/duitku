<?php

namespace App\Models;

use CodeIgniter\Model;

class PosVoucherModel extends Model
{
    protected $table            = 'pos_vouchers';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = [
        'user_id', 'code', 'title', 'type', 'value',
        'min_order', 'max_discount', 'usage_limit', 'used_count',
        'is_active', 'expires_at',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Validate & calculate voucher discount
     */
    public function validateAndCalculate(int $userId, string $code, float $subtotal, float $deliveryFee = 0.0): array
    {
        $code = strtoupper(trim($code));
        $voucher = $this->where('user_id', $userId)
                        ->where('UPPER(code)', $code)
                        ->where('is_active', 1)
                        ->first();

        if (!$voucher) {
            return ['valid' => false, 'message' => 'Kode promo tidak ditemukan atau tidak aktif.'];
        }

        if (!empty($voucher['expires_at']) && $voucher['expires_at'] < date('Y-m-d')) {
            return ['valid' => false, 'message' => 'Masa berlaku kode promo telah berakhir.'];
        }

        if ((int)$voucher['usage_limit'] > 0 && (int)$voucher['used_count'] >= (int)$voucher['usage_limit']) {
            return ['valid' => false, 'message' => 'Kuota pemakaian kode promo telah habis.'];
        }

        $minOrder = (float)$voucher['min_order'];
        if ($subtotal < $minOrder) {
            return [
                'valid'   => false,
                'message' => 'Minimal belanja untuk promo ini adalah Rp ' . number_format($minOrder, 0, ',', '.'),
            ];
        }

        $discount = 0.0;
        $type = $voucher['type'];
        $val = (float)$voucher['value'];
        $maxDisc = (float)$voucher['max_discount'];

        if ($type === 'percent') {
            $discount = ($val / 100.0) * $subtotal;
            if ($maxDisc > 0 && $discount > $maxDisc) {
                $discount = $maxDisc;
            }
        } elseif ($type === 'nominal') {
            $discount = min($val, $subtotal);
        } elseif ($type === 'free_shipping') {
            $discount = min($deliveryFee, $val > 0 ? $val : $deliveryFee);
        }

        return [
            'valid'           => true,
            'voucher'         => $voucher,
            'discount_amount' => round($discount, 2),
            'message'         => 'Kode promo berhasil digunakan! Diskon: Rp ' . number_format($discount, 0, ',', '.'),
        ];
    }
}
