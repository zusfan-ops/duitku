import 'package:flutter_test/flutter_test.dart';

import 'package:duitku_app/utils/format.dart';

void main() {
  test('Fmt.parseAmount menghilangkan pemisah ribuan', () {
    expect(Fmt.parseAmount('Rp 1.234.567'), '1234567');
    expect(Fmt.parseAmount('0'), '0');
  });

  test('Fmt.monthLabel memformat YYYY-MM', () {
    expect(Fmt.monthLabel('2026-08'), 'Agustus 2026');
  });

  test('Fmt.shortMonthLabel memformat YYYY-MM singkat', () {
    expect(Fmt.shortMonthLabel('2026-08'), 'Agu 2026');
  });
}
