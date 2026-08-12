import 'package:flutter/material.dart';

import '../theme.dart';

void showCalculatorSheet(BuildContext context) {
  showModalBottomSheet(
    context: context,
    isScrollControlled: true,
    backgroundColor: AppColors.card,
    shape: const RoundedRectangleBorder(
      borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
    ),
    builder: (_) => const _CalculatorSheet(),
  );
}

class _CalculatorSheet extends StatefulWidget {
  const _CalculatorSheet();

  @override
  State<_CalculatorSheet> createState() => _CalculatorSheetState();
}

class _CalculatorSheetState extends State<_CalculatorSheet> {
  String _expr = '0';
  String _prev = '';
  bool _isDiscount = false;

  String get _result {
    final parts = _expr.split(' ').where((s) => s.isNotEmpty).toList();
    if (parts.length < 3) return _expr;
    final a = double.tryParse(parts[0]);
    final op = parts[1];
    final b = double.tryParse(parts[2]);
    if (a == null || b == null) return _expr;
    double r;
    switch (op) {
      case '+':
        r = a + b;
      case '-':
        r = a - b;
      case '×':
        r = a * b;
      case '÷':
        r = b == 0 ? 0 : a / b;
      default:
        return _expr;
    }
    return _fmt(r);
  }

  String _fmt(double v) {
    if (v == v.roundToDouble() && v.abs() < 1e15) return v.toInt().toString();
    return v.toString();
  }

  void _tap(String key) {
    setState(() {
      if (RegExp(r'^[0-9]$').hasMatch(key)) {
        if (_expr == '0') {
          _expr = key;
        } else if (_expr.split(' ').length == 3) {
          _expr = key;
        } else {
          _expr += key;
        }
      } else if (key == '.') {
        final parts = _expr.split(' ');
        if (!parts.last.contains('.')) _expr += '.';
      } else if (key == '+' || key == '-' || key == '×' || key == '÷') {
        final parts = _expr.split(' ').where((s) => s.isNotEmpty).toList();
        if (parts.length == 3) {
          _expr = '$_result $key';
        } else {
          _expr = parts.isEmpty ? '0' : parts.join(' ');
          _expr = '$_expr $key';
        }
      } else if (key == '=') {
        _prev = _expr;
        _expr = _result;
      } else if (key == 'C') {
        _expr = '0';
        _prev = '';
      } else if (key == '⌫') {
        _expr = _expr.length > 1 ? _expr.substring(0, _expr.length - 1).trim() : '0';
        if (_expr.isEmpty) _expr = '0';
      } else if (key == '%') {
        _isDiscount = !_isDiscount;
      }
    });
  }

  @override
  Widget build(BuildContext context) {
    final discountParts = _expr.split(' ');
    final discountOn = _isDiscount && discountParts.length == 3;

    return Padding(
      padding: EdgeInsets.only(bottom: MediaQuery.of(context).viewInsets.bottom),
      child: SingleChildScrollView(
        padding: const EdgeInsets.fromLTRB(20, 16, 20, 24),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            Center(
              child: Container(
                width: 40,
                height: 4,
                decoration: BoxDecoration(
                  color: AppColors.border,
                  borderRadius: BorderRadius.circular(2),
                ),
              ),
            ),
            const SizedBox(height: 12),
            const Text('🧮 Kalkulator',
                textAlign: TextAlign.center,
                style: TextStyle(fontSize: 17, fontWeight: FontWeight.w800, color: AppColors.textPrimary)),
            if (discountOn) ...[
              const SizedBox(height: 10),
              _DiscountResult(a: discountParts[0], op: discountParts[1], b: discountParts[2]),
            ],
            const SizedBox(height: 12),
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
              decoration: BoxDecoration(
                color: AppColors.bg,
                borderRadius: BorderRadius.circular(14),
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.end,
                children: [
                  if (_prev.isNotEmpty)
                    Text(_prev, style: const TextStyle(fontSize: 13, color: AppColors.textMuted)),
                  Text(_expr,
                      style: const TextStyle(
                          fontSize: 30, fontWeight: FontWeight.w800, color: AppColors.textPrimary)),
                ],
              ),
            ),
            const SizedBox(height: 14),
            const Divider(height: 1, color: AppColors.border),
            const SizedBox(height: 6),
            Wrap(
              spacing: 10,
              runSpacing: 10,
              children: [
                _btn('C', bg: AppColors.expense),
                _btn('⌫', bg: AppColors.textMuted),
                _btn('%', active: _isDiscount),
                _btn('÷'),
                _btn('7'),
                _btn('8'),
                _btn('9'),
                _btn('×'),
                _btn('4'),
                _btn('5'),
                _btn('6'),
                _btn('-'),
                _btn('1'),
                _btn('2'),
                _btn('3'),
                _btn('+'),
                _btn('0', wide: true),
                _btn('.'),
                _btn('=', bg: AppColors.primary),
              ],
            ),
          ],
        ),
      ),
    );
  }

  Widget _btn(String label, {Color? bg, bool wide = false, bool active = false}) {
    final bgColor = active
        ? AppColors.primary
        : bg ?? AppColors.bg;
    final fg = (active || bg == AppColors.expense || bg == AppColors.primary)
        ? Colors.white
        : AppColors.textPrimary;
    return InkWell(
      onTap: () => _tap(label),
      borderRadius: BorderRadius.circular(14),
      child: Container(
        width: wide ? 92 : 76,
        height: 52,
        alignment: Alignment.center,
        decoration: BoxDecoration(
          color: bgColor,
          borderRadius: BorderRadius.circular(14),
        ),
        child: Text(label,
            style: TextStyle(
                fontSize: label.length > 1 ? 15 : 20, fontWeight: FontWeight.w700, color: fg)),
      ),
    );
  }
}

class _DiscountResult extends StatelessWidget {
  final String a;
  final String op;
  final String b;
  const _DiscountResult({required this.a, required this.op, required this.b});

  @override
  Widget build(BuildContext context) {
    final price = double.tryParse(a) ?? 0;
    final pct = double.tryParse(b) ?? 0;
    final savings = (price * pct) / 100;
    final finalPrice = price - savings;
    return Container(
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: AppColors.primary.withValues(alpha: .08),
        borderRadius: BorderRadius.circular(12),
      ),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          const Text('Diskon',
              style: TextStyle(fontSize: 12, fontWeight: FontWeight.w700, color: AppColors.textSecondary)),
          Column(
            crossAxisAlignment: CrossAxisAlignment.end,
            children: [
              Text('Hemat Rp ${_num(savings)}',
                  style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w700, color: AppColors.expense)),
              Text('Harga Rp ${_num(finalPrice)}',
                  style: const TextStyle(fontSize: 14, fontWeight: FontWeight.w800, color: AppColors.primary)),
            ],
          ),
        ],
      ),
    );
  }

  String _num(double v) {
    final i = v.round();
    final s = i.toString();
    final buf = StringBuffer();
    for (var x = 0; x < s.length; x++) {
      buf.write(s[x]);
      final remaining = s.length - x - 1;
      if (remaining > 0 && remaining % 3 == 0) buf.write('.');
    }
    return buf.toString();
  }
}
