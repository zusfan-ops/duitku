<?php

namespace App\Libraries;

class CurrencyService
{
    /**
     * Default exchange rates relative to IDR (Indonesian Rupiah)
     */
    private array $defaultRates = [
        'IDR' => 1.0,
        'USD' => 16250.0,
        'SGD' => 12150.0,
        'MYR' => 3650.0,
        'EUR' => 17600.0,
        'JPY' => 105.0,
        'GBP' => 20800.0,
        'AUD' => 10600.0,
        'SAR' => 4330.0, // Saudi Riyal (Umroh/Haji)
        'CNY' => 2240.0,
        'KRW' => 11.8,
        'THB' => 465.0,
    ];

    /**
     * Get all supported currencies with labels & symbols
     */
    public function getCurrencies(): array
    {
        return [
            'IDR' => ['code' => 'IDR', 'symbol' => 'Rp',  'name' => 'Rupiah Indonesia', 'rate_to_idr' => 1.0],
            'USD' => ['code' => 'USD', 'symbol' => '$',   'name' => 'US Dollar',        'rate_to_idr' => 16250.0],
            'SGD' => ['code' => 'SGD', 'symbol' => 'S$',  'name' => 'Singapore Dollar', 'rate_to_idr' => 12150.0],
            'MYR' => ['code' => 'MYR', 'symbol' => 'RM',  'name' => 'Ringgit Malaysia', 'rate_to_idr' => 3650.0],
            'SAR' => ['code' => 'SAR', 'symbol' => 'SR',  'name' => 'Saudi Riyal',      'rate_to_idr' => 4330.0],
            'EUR' => ['code' => 'EUR', 'symbol' => '€',   'name' => 'Euro',             'rate_to_idr' => 17600.0],
            'JPY' => ['code' => 'JPY', 'symbol' => '¥',   'name' => 'Japanese Yen',     'rate_to_idr' => 105.0],
            'GBP' => ['code' => 'GBP', 'symbol' => '£',   'name' => 'British Pound',    'rate_to_idr' => 20800.0],
            'AUD' => ['code' => 'AUD', 'symbol' => 'A$',  'name' => 'Australian Dollar','rate_to_idr' => 10600.0],
            'CNY' => ['code' => 'CNY', 'symbol' => '¥',   'name' => 'Chinese Yuan',     'rate_to_idr' => 2240.0],
            'KRW' => ['code' => 'KRW', 'symbol' => '₩',   'name' => 'South Korean Won', 'rate_to_idr' => 11.8],
            'THB' => ['code' => 'THB', 'symbol' => '฿',   'name' => 'Thai Baht',        'rate_to_idr' => 465.0],
        ];
    }

    /**
     * Convert an amount between two currencies
     */
    public function convert(float $amount, string $from, string $to = 'IDR'): array
    {
        $from = strtoupper($from);
        $to = strtoupper($to);
        $currencies = $this->getCurrencies();

        $fromRate = $currencies[$from]['rate_to_idr'] ?? $this->defaultRates[$from] ?? 1.0;
        $toRate = $currencies[$to]['rate_to_idr'] ?? $this->defaultRates[$to] ?? 1.0;

        // Convert to IDR first, then to target
        $amountInIdr = $amount * $fromRate;
        $converted = $amountInIdr / $toRate;

        return [
            'original_amount' => $amount,
            'from_currency'   => $from,
            'to_currency'     => $to,
            'converted_amount'=> round($converted, 2),
            'rate'            => round($fromRate / $toRate, 4),
            'amount_in_idr'   => round($amountInIdr, 2),
            'timestamp'       => date('Y-m-d H:i:s'),
        ];
    }
}
