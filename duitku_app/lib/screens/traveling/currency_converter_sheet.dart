import 'package:flutter/material.dart';
import '../../services/api_service.dart';
import '../../theme.dart';
import '../../utils/format.dart';

class CurrencyConverterSheet extends StatefulWidget {
  const CurrencyConverterSheet({super.key});

  @override
  State<CurrencyConverterSheet> createState() => _CurrencyConverterSheetState();
}

class _CurrencyConverterSheetState extends State<CurrencyConverterSheet> {
  final _amountCtrl = TextEditingController(text: '100');
  String _fromCurr = 'USD';
  String _toCurr = 'IDR';
  double _converted = 1625000.0;
  double _rate = 16250.0;
  bool _loading = false;

  final List<Map<String, String>> _currencies = [
    {'code': 'USD', 'symbol': '\$', 'name': 'US Dollar', 'flag': '🇺🇸'},
    {'code': 'SGD', 'symbol': 'S\$', 'name': 'Singapore Dollar', 'flag': '🇸🇬'},
    {'code': 'MYR', 'symbol': 'RM', 'name': 'Ringgit Malaysia', 'flag': '🇲🇾'},
    {'code': 'SAR', 'symbol': 'SR', 'name': 'Saudi Riyal (Umroh)', 'flag': '🇸🇦'},
    {'code': 'JPY', 'symbol': '¥', 'name': 'Japanese Yen', 'flag': '🇯🇵'},
    {'code': 'EUR', 'symbol': '€', 'name': 'Euro', 'flag': '🇪🇺'},
    {'code': 'GBP', 'symbol': '£', 'name': 'British Pound', 'flag': '🇬🇧'},
    {'code': 'AUD', 'symbol': 'A\$', 'name': 'Australian Dollar', 'flag': '🇦🇺'},
    {'code': 'CNY', 'symbol': '¥', 'name': 'Chinese Yuan', 'flag': '🇨🇳'},
    {'code': 'KRW', 'symbol': '₩', 'name': 'South Korean Won', 'flag': '🇰🇷'},
    {'code': 'THB', 'symbol': '฿', 'name': 'Thai Baht', 'flag': '🇹🇭'},
    {'code': 'IDR', 'symbol': 'Rp', 'name': 'Rupiah Indonesia', 'flag': '🇮🇩'},
  ];

  @override
  void initState() {
    super.initState();
    _recalc();
  }

  @override
  void dispose() {
    _amountCtrl.dispose();
    super.dispose();
  }

  Future<void> _recalc() async {
    final amount = double.tryParse(_amountCtrl.text.replaceAll(',', '')) ?? 1.0;
    setState(() => _loading = true);

    try {
      final res = await ApiService.instance.convertCurrency(
        amount: amount,
        from: _fromCurr,
        to: _toCurr,
      );
      if (res['success'] == true && res['data'] != null) {
        final d = res['data'];
        setState(() {
          _converted = (d['converted_amount'] as num).toDouble();
          _rate = (d['rate'] as num).toDouble();
        });
      }
    } catch (_) {
      // Fallback calculation
      const rates = {
        'IDR': 1.0,
        'USD': 16250.0,
        'SGD': 12150.0,
        'MYR': 3650.0,
        'SAR': 4330.0,
        'JPY': 105.0,
        'EUR': 17600.0,
        'GBP': 20800.0,
        'AUD': 10600.0,
        'CNY': 2240.0,
        'KRW': 11.8,
        'THB': 465.0,
      };
      final rFrom = rates[_fromCurr] ?? 1.0;
      final rTo = rates[_toCurr] ?? 1.0;
      final idr = amount * rFrom;
      setState(() {
        _converted = idr / rTo;
        _rate = rFrom / rTo;
      });
    } finally {
      setState(() => _loading = false);
    }
  }

  void _swap() {
    setState(() {
      final tmp = _fromCurr;
      _fromCurr = _toCurr;
      _toCurr = tmp;
    });
    _recalc();
  }

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: EdgeInsets.only(
        left: 20,
        right: 20,
        top: 20,
        bottom: MediaQuery.of(context).viewInsets.bottom + 20,
      ),
      decoration: BoxDecoration(
        color: AppColors.card,
        borderRadius: const BorderRadius.vertical(top: Radius.circular(24)),
      ),
      child: Column(
        mainAxisSize: MainAxisSize.min,
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              const Row(
                children: [
                  Text('💱', style: TextStyle(fontSize: 20)),
                  SizedBox(width: 8),
                  Text(
                    'Kalkulator Kurs Valas',
                    style: TextStyle(fontSize: 17, fontWeight: FontWeight.w800),
                  ),
                ],
              ),
              IconButton(
                onPressed: () => Navigator.pop(context),
                icon: const Icon(Icons.close_rounded),
              ),
            ],
          ),
          const SizedBox(height: 16),

          // Input Amount
          TextField(
            controller: _amountCtrl,
            keyboardType: const TextInputType.numberWithOptions(decimal: true),
            onChanged: (_) => _recalc(),
            decoration: InputDecoration(
              labelText: 'NOMINAL VALAS',
              prefixIcon: Padding(
                padding: const EdgeInsets.all(12),
                child: Text(
                  _fromCurr,
                  style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 14),
                ),
              ),
              border: OutlineInputBorder(borderRadius: BorderRadius.circular(14)),
            ),
          ),
          const SizedBox(height: 14),

          // Currency Selectors & Swap
          Row(
            children: [
              Expanded(
                child: DropdownButtonFormField<String>(
                  initialValue: _fromCurr,
                  decoration: InputDecoration(
                    labelText: 'DARI',
                    border: OutlineInputBorder(borderRadius: BorderRadius.circular(14)),
                    contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
                  ),
                  items: _currencies.map((c) {
                    return DropdownMenuItem<String>(
                      value: c['code'],
                      child: Text('${c['flag']} ${c['code']}'),
                    );
                  }).toList(),
                  onChanged: (v) {
                    if (v != null) {
                      setState(() => _fromCurr = v);
                      _recalc();
                    }
                  },
                ),
              ),
              IconButton.filledTonal(
                onPressed: _swap,
                icon: const Icon(Icons.swap_horiz_rounded),
              ),
              Expanded(
                child: DropdownButtonFormField<String>(
                  initialValue: _toCurr,
                  decoration: InputDecoration(
                    labelText: 'KE',
                    border: OutlineInputBorder(borderRadius: BorderRadius.circular(14)),
                    contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
                  ),
                  items: _currencies.map((c) {
                    return DropdownMenuItem<String>(
                      value: c['code'],
                      child: Text('${c['flag']} ${c['code']}'),
                    );
                  }).toList(),
                  onChanged: (v) {
                    if (v != null) {
                      setState(() => _toCurr = v);
                      _recalc();
                    }
                  },
                ),
              ),
            ],
          ),
          const SizedBox(height: 18),

          // Result Card
          Container(
            width: double.infinity,
            padding: const EdgeInsets.all(16),
            decoration: BoxDecoration(
              gradient: LinearGradient(
                colors: [
                  AppColors.primary.withValues(alpha: .15),
                  AppColors.primarySubtle,
                ],
              ),
              borderRadius: BorderRadius.circular(16),
              border: Border.all(color: AppColors.primary.withValues(alpha: .3)),
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  'HASIL KONVERSI ($_toCurr)',
                  style: const TextStyle(
                    fontSize: 11,
                    fontWeight: FontWeight.w800,
                    color: AppColors.primary,
                    letterSpacing: 0.5,
                  ),
                ),
                const SizedBox(height: 4),
                if (_loading)
                  const Padding(
                    padding: EdgeInsets.symmetric(vertical: 8),
                    child: CircularProgressIndicator(),
                  )
                else
                  Text(
                    _toCurr == 'IDR'
                        ? Fmt.money0(_converted)
                        : Fmt.money(_converted, symbol: _toCurr),
                    style: const TextStyle(
                      fontSize: 24,
                      fontWeight: FontWeight.w900,
                      color: AppColors.textPrimary,
                    ),
                  ),
                const SizedBox(height: 6),
                Text(
                  '1 $_fromCurr ≈ ${_rate.toStringAsFixed(2)} $_toCurr',
                  style: const TextStyle(fontSize: 11.5, color: AppColors.textMuted),
                ),
              ],
            ),
          ),
          const SizedBox(height: 12),
        ],
      ),
    );
  }
}
