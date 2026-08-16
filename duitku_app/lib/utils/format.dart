import 'package:intl/intl.dart';

class Fmt {
  static final NumberFormat _idr = NumberFormat.currency(
    locale: 'id_ID',
    symbol: '',
    decimalDigits: 0,
  );

  static String money(dynamic amount, {String symbol = 'Rp'}) {
    final d = toDouble(amount);
    final formatted = _idr.format(d).trim();
    return '$symbol $formatted';
  }

  static String money0(dynamic amount) {
    final d = toDouble(amount);
    return _idr.format(d).trim();
  }

  static String monthLabel(String ym) {
    if (ym.length != 7) return ym;
    final y = int.parse(ym.substring(0, 4));
    final m = int.parse(ym.substring(5, 7));
    const names = [
      'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
      'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember',
    ];
    return '${names[m - 1]} $y';
  }

  static String shortMonthLabel(String ym) {
    if (ym.length != 7) return ym;
    final y = int.parse(ym.substring(0, 4));
    final m = int.parse(ym.substring(5, 7));
    const names = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
    return '${names[m - 1]} $y';
  }

  static String dateDay(String date) {
    final d = DateTime.tryParse(date);
    if (d == null) return date;
    return DateFormat('d MMM', 'id_ID').format(d);
  }

  static String parseAmount(String input) {
    // Hapus semua karakter selain digit (dari format "1.234" / "Rp 1.000")
    return input.replaceAll(RegExp(r'[^0-9]'), '');
  }

  static double toDouble(dynamic v) {
    if (v == null) return 0;
    if (v is num) return v.toDouble();
    return double.tryParse(v.toString()) ?? 0;
  }
}
