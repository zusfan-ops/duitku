<?php

namespace App\Libraries;

class ReceiptOcrService
{
    /**
     * Parse receipt text into structured transaction data
     */
    public function parseReceiptText(string $text): array
    {
        $lines = preg_split('/\r\n|\r|\n/', trim($text));
        $cleanLines = array_values(array_filter(array_map('trim', $lines)));

        $merchant = $this->extractMerchant($cleanLines);
        $amount = $this->extractTotalAmount($text, $cleanLines);
        $date = $this->extractDate($text);
        $category = $this->guessCategory($merchant, $text);

        return [
            'merchant'           => $merchant,
            'amount'             => $amount,
            'date'               => $date ?: date('Y-m-d'),
            'suggested_category' => $category,
            'raw_text_length'    => strlen($text),
            'confidence'         => $amount > 0 ? 'high' : 'medium',
        ];
    }

    /**
     * Extract Merchant / Store Name from the top lines of receipt
     */
    private function extractMerchant(array $lines): string
    {
        $knownMerchants = [
            'Indomaret', 'Alfamart', 'Alfamidi', 'Superindo', 'Hypermart',
            'Transmart', 'Carrefour', 'Hero', 'Lotte Mart', 'Starbucks',
            'Janji Jiwa', 'Kopi Kenangan', 'KFC', 'McDonald\'s', 'McD',
            'Burger King', 'A&W', 'Pizza Hut', 'Dominos', 'HokBen',
            'SPBU', 'Pertamina', 'Shell', 'BP AKR', 'Guardian', 'Watsons',
            'Gramedia', 'Ace Hardware', 'Informa', 'Uniqlo', 'H&M', 'Zara'
        ];

        // Check if any known merchant appears in the text
        foreach ($lines as $line) {
            foreach ($knownMerchants as $km) {
                if (stripos($line, $km) !== false) {
                    return $km;
                }
            }
        }

        // Otherwise, grab the first non-empty, non-numeric line with reasonable length
        foreach (array_slice($lines, 0, 5) as $line) {
            $cleaned = preg_replace('/[^a-zA-Z0-9\s\.\-]/', '', $line);
            if (strlen($cleaned) >= 3 && !is_numeric($cleaned) && !preg_match('/^(jl|jalan|no|telp|struk|nota|receipt|selamat)/i', $cleaned)) {
                return ucwords(strtolower(substr($cleaned, 0, 40)));
            }
        }

        return 'Belanja / Struk Toko';
    }

    /**
     * Extract Total Amount from receipt text
     */
    private function extractTotalAmount(string $rawText, array $lines): float
    {
        $amountCandidates = [];

        // Priority 1: Lines with TOTAL keywords
        $totalPatterns = [
            '/(?:GRAND\s*TOTAL|TOTAL\s*AKHIR|TOTAL\s*BAYAR|TOTAL\s*BELANJA|TOTAL\s*HARGA|TOTAL|JUMLAH|HARGA\s*TOTAL|TAGIHAN|BAYAR\s*TUNAI|BAYAR)\s*[:=]?\s*(?:RP\.?|IDR)?\s*([0-9\.\,]+)/i',
            '/(?:NETTO|NET\s*AMOUNT|SUBTOTAL)\s*[:=]?\s*(?:RP\.?|IDR)?\s*([0-9\.\,]+)/i',
        ];

        foreach ($lines as $line) {
            foreach ($totalPatterns as $pattern) {
                if (preg_match($pattern, $line, $matches)) {
                    $val = $this->cleanNumber($matches[1]);
                    if ($val > 0) {
                        return $val;
                    }
                }
            }
        }

        // Priority 2: Look for currency numbers preceded by Rp
        if (preg_match_all('/(?:Rp\.?|IDR)\s*([0-9\.\,]+)/i', $rawText, $allRpMatches)) {
            foreach ($allRpMatches[1] as $candidate) {
                $val = $this->cleanNumber($candidate);
                if ($val > 0) {
                    $amountCandidates[] = $val;
                }
            }
        }

        // Return largest sensible amount if candidate found
        if (!empty($amountCandidates)) {
            return (float) max($amountCandidates);
        }

        return 0.0;
    }

    /**
     * Clean parsed number string into float
     */
    private function cleanNumber(string $str): float
    {
        $clean = trim($str);
        // Remove trailing commas/periods
        $clean = rtrim($clean, '., ');

        // If Indonesian format like 150.000 or 150.000,00
        if (substr_count($clean, '.') > 0 && substr_count($clean, ',') === 1) {
            $clean = str_replace('.', '', $clean);
            $clean = str_replace(',', '.', $clean);
        } elseif (substr_count($clean, '.') > 0 && substr_count($clean, ',') === 0) {
            // Check if last period is decimals (e.g. 15.50) or thousand separator (e.g. 50.000)
            $parts = explode('.', $clean);
            if (strlen(end($parts)) === 3) {
                $clean = str_replace('.', '', $clean);
            }
        } else {
            $clean = str_replace([',', ' '], '', $clean);
        }

        return (float) $clean;
    }

    /**
     * Extract transaction date
     */
    private function extractDate(string $rawText): ?string
    {
        // DD/MM/YYYY or DD-MM-YYYY or YYYY-MM-DD
        if (preg_match('/(\d{2})[\/\-\.](\d{2})[\/\-\.](\d{4})/', $rawText, $m)) {
            $day = (int)$m[1];
            $mon = (int)$m[2];
            $yr = (int)$m[3];
            if ($day <= 31 && $mon <= 12) {
                return sprintf('%04d-%02d-%02d', $yr, $mon, $day);
            }
        }

        if (preg_match('/(\d{4})[\/\-\.](\d{2})[\/\-\.](\d{2})/', $rawText, $m)) {
            return sprintf('%04d-%02d-%02d', (int)$m[1], (int)$m[2], (int)$m[3]);
        }

        return null;
    }

    /**
     * Guess category based on merchant or keywords
     */
    private function guessCategory(string $merchant, string $text): string
    {
        $combined = strtolower($merchant . ' ' . $text);

        if (preg_match('/(spbu|pertamina|shell|bp akr|bensin|pertalite|pertamax|solar|parkir|toll|tol|grab|gojek)/i', $combined)) {
            return 'Transportasi';
        }
        if (preg_match('/(kfc|mcdonald|mcd|burger|kopi|coffee|cafe|resto|mie|bakso|warung|starbucks|pizza|hokben|bread|roti|kuliner)/i', $combined)) {
            return 'Makanan & Minuman';
        }
        if (preg_match('/(indomaret|alfamart|alfamidi|superindo|hypermart|sembako|sayur|buah|beras|grosir)/i', $combined)) {
            return 'Belanja Bulanan';
        }
        if (preg_match('/(pln|listrik|pdam|air|wifi|indihome|bpjs|telkom|pulsa|paket data)/i', $combined)) {
            return 'Tagihan & Utilitas';
        }
        if (preg_match('/(apotek|obat|dokter|klinik|hospital|kimia farma|k24|guardian|watsons)/i', $combined)) {
            return 'Kesehatan';
        }

        return 'Belanja';
    }
}
