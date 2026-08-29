/// Hasil parsing notifikasi atau SMS bank / e-wallet
class ParsedBankNotification {
  final String rawText;
  final String providerName; // e.g. 'BCA', 'Mandiri', 'GoPay', 'DANA', etc.
  final String providerIcon; // emoji / short identifier
  final String type; // 'expense' (pengeluaran/debet) or 'income' (pemasukan/kredit)
  final double amount;
  final String? merchantOrRecipient;
  final String? accountOrCard;
  final String? refNumber;
  final DateTime? date;
  final String summary;

  ParsedBankNotification({
    required this.rawText,
    required this.providerName,
    required this.providerIcon,
    required this.type,
    required this.amount,
    this.merchantOrRecipient,
    this.accountOrCard,
    this.refNumber,
    this.date,
    required this.summary,
  });

  bool get isValid => amount > 0;
}

class BankNotificationParser {
  BankNotificationParser._();

  /// Parse teks notifikasi atau SMS menjadi model terstruktur
  static ParsedBankNotification parse(String text) {
    final clean = text.trim();
    if (clean.isEmpty) {
      return _empty(clean);
    }

    final lower = clean.toLowerCase();

    // 1. Deteksi Provider Bank / E-Wallet
    String provider = 'Umum';
    String icon = '💳';

    if (lower.contains('bca') || lower.contains('klikbca') || lower.contains('mybca')) {
      provider = 'BCA';
      icon = '🏦';
    } else if (lower.contains('mandiri') || lower.contains('livin')) {
      provider = 'Bank Mandiri';
      icon = '🏦';
    } else if (lower.contains('bri') || lower.contains('brimo')) {
      provider = 'Bank BRI';
      icon = '🏦';
    } else if (lower.contains('bni')) {
      provider = 'Bank BNI';
      icon = '🏦';
    } else if (lower.contains('bsi')) {
      provider = 'Bank BSI';
      icon = '🏦';
    } else if (lower.contains('jago')) {
      provider = 'Bank Jago';
      icon = '🏦';
    } else if (lower.contains('seabank')) {
      provider = 'SeaBank';
      icon = '🏦';
    } else if (lower.contains('gopay') || lower.contains('gojek')) {
      provider = 'GoPay';
      icon = '🟢';
    } else if (lower.contains('ovo')) {
      provider = 'OVO';
      icon = '🟣';
    } else if (lower.contains('dana')) {
      provider = 'DANA';
      icon = '🔵';
    } else if (lower.contains('shopeepay') || lower.contains('shopee')) {
      provider = 'ShopeePay';
      icon = '🟠';
    } else if (lower.contains('linkaja')) {
      provider = 'LinkAja';
      icon = '🔴';
    }

    // 2. Deteksi Tipe Transaksi (Pemasukan vs Pengeluaran)
    String type = 'expense'; // default pengeluaran

    final isIncomeKeywords = [
      'masuk', 'transfer dari', 'terima transfer', 'diterima dari', 'cr', 'kredit',
      'terkredit', 'top up berhasil', 'saldo bertambah', 'inward', 'received'
    ];

    final isExpenseKeywords = [
      'keluar', 'transfer ke', 'pembayaran', 'qris', 'debit', 'db', 'terdebet',
      'tarik tunai', 'bayar', 'payment', 'purchase', 'outward', 'belanja'
    ];

    bool hasIncomeMatch = isIncomeKeywords.any((k) => lower.contains(k));
    bool hasExpenseMatch = isExpenseKeywords.any((k) => lower.contains(k));

    if (hasIncomeMatch && !hasExpenseMatch) {
      type = 'income';
    } else if (hasExpenseMatch) {
      type = 'expense';
    }

    // 3. Deteksi Nominal Uang
    double amount = 0;

    // Pola 1: Rp 125.000 atau IDR 125,000.00 atau Rp.125.000
    final rpRegex = RegExp(
      r'(?:rp\.?|idr)\s*([\d\.,]+)',
      caseSensitive: false,
    );
    final rpMatches = rpRegex.allMatches(clean);

    for (final m in rpMatches) {
      final valStr = m.group(1);
      if (valStr != null) {
        final parsed = _parseIdrNumber(valStr);
        if (parsed > amount) {
          amount = parsed;
        }
      }
    }

    // Pola 2: Nominal tanpa awalan Rp jika belum ketemu (misal: "sebesar 50.000")
    if (amount == 0) {
      final numRegex = RegExp(r'(?:sebesar|jumlah|total|nominal|amount)\s*[:=]?\s*([\d\.,]+)', caseSensitive: false);
      final match = numRegex.firstMatch(clean);
      if (match != null && match.group(1) != null) {
        amount = _parseIdrNumber(match.group(1)!);
      }
    }

    // 4. Deteksi Merchant / Penerima / Pengirim
    String? merchant;
    final merchantPatterns = [
      RegExp(r'(?:ke|penerima|merchant|di|tujuan|kepada)\s*[:=]?\s*([A-Za-z0-9\s\.\-_&]+?)(?:\s+(?:pada|tgl|sebesar|rp|ref|\n|$))', caseSensitive: false),
      RegExp(r'(?:dari|pengirim|from)\s*[:=]?\s*([A-Za-z0-9\s\.\-_&]+?)(?:\s+(?:sebesar|ke|pada|tgl|rp|ref|\n|$))', caseSensitive: false),
      RegExp(r'qris\s*[:=]?\s*([A-Za-z0-9\s\.\-_&]+?)(?:\s+(?:sebesar|rp|\n|$))', caseSensitive: false),
    ];

    for (final pattern in merchantPatterns) {
      final m = pattern.firstMatch(clean);
      if (m != null && m.group(1) != null) {
        final candidate = m.group(1)!.trim();
        if (candidate.isNotEmpty && candidate.length > 2 && candidate.length < 50) {
          // Bersihkan kata sambung
          if (!candidate.toLowerCase().startsWith('rekening') && !candidate.toLowerCase().startsWith('rp')) {
            merchant = candidate;
            break;
          }
        }
      }
    }

    // Jika tidak ketemu merchant tapi ada provider spesifik
    if (merchant == null || merchant.isEmpty) {
      if (type == 'income') {
        merchant = 'Transfer Masuk ($provider)';
      } else {
        merchant = 'Transaksi $provider';
      }
    }

    // 5. Deteksi No Ref / No Transaksi
    String? refNumber;
    final refRegex = RegExp(r'(?:ref|reff|no\.?\s*trx|trx\s*id|kode)\s*[:=]?\s*([A-Za-z0-9]+)', caseSensitive: false);
    final refMatch = refRegex.firstMatch(clean);
    if (refMatch != null) {
      refNumber = refMatch.group(1);
    }

    return ParsedBankNotification(
      rawText: clean,
      providerName: provider,
      providerIcon: icon,
      type: type,
      amount: amount,
      merchantOrRecipient: merchant,
      refNumber: refNumber,
      date: DateTime.now(),
      summary: '$provider · ${type == 'income' ? 'Pemasukan' : 'Pengeluaran'} Rp ${_formatAmount(amount)}',
    );
  }

  static double _parseIdrNumber(String raw) {
    try {
      var s = raw.trim();
      // Hilangkan spasi
      s = s.replaceAll(' ', '');
      // Format 50.000,00 -> 50000
      if (s.contains(',') && s.contains('.')) {
        if (s.lastIndexOf(',') > s.lastIndexOf('.')) {
          // Format 50.000,00
          s = s.replaceAll('.', '').replaceAll(',', '.');
        } else {
          // Format 50,000.00
          s = s.replaceAll(',', '');
        }
      } else if (s.contains('.')) {
        // Cek apakah . sebagai pemisah ribuan (misal 50.000) atau desimal (50.5)
        final parts = s.split('.');
        if (parts.last.length == 3 || parts.length > 2) {
          s = s.replaceAll('.', '');
        }
      } else if (s.contains(',')) {
        // Cek apakah , sebagai pemisah ribuan (misal 50,000)
        final parts = s.split(',');
        if (parts.last.length == 3 || parts.length > 2) {
          s = s.replaceAll(',', '');
        } else {
          s = s.replaceAll(',', '.');
        }
      }
      return double.tryParse(s) ?? 0.0;
    } catch (_) {
      return 0.0;
    }
  }

  static String _formatAmount(double amount) {
    if (amount == 0) return '0';
    return amount.toStringAsFixed(0).replaceAllMapped(
          RegExp(r'(\d{1,3})(?=(\d{3})+(?!\d))'),
          (Match m) => '${m[1]}.',
        );
  }

  static ParsedBankNotification _empty(String raw) {
    return ParsedBankNotification(
      rawText: raw,
      providerName: 'Tidak Diketahui',
      providerIcon: '❓',
      type: 'expense',
      amount: 0,
      summary: 'Tidak dapat mendeteksi informasi transaksi',
    );
  }
}
