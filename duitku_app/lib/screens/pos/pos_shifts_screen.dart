import 'package:flutter/material.dart';

import '../../services/api_service.dart';
import '../../theme.dart';
import '../../utils/format.dart';

class PosShiftsScreen extends StatefulWidget {
  const PosShiftsScreen({super.key});

  @override
  State<PosShiftsScreen> createState() => _PosShiftsScreenState();
}

class _PosShiftsScreenState extends State<PosShiftsScreen> {
  bool _loading = true;
  String? _error;
  Map<String, dynamic>? _activeShift;
  List<dynamic> _shiftHistory = [];
  double _currentCashSales = 0;
  double _currentExpectedCash = 0;
  String _symbol = 'Rp';

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() {
      _loading = true;
      _error = null;
    });
    try {
      final res = await ApiService.instance.getPosShifts();
      if (!mounted) return;
      setState(() {
        _activeShift = res['active_shift'] as Map<String, dynamic>?;
        _shiftHistory = (res['shift_history'] as List?) ?? [];
        _currentCashSales = double.tryParse('${res['current_cash_sales']}') ?? 0;
        _currentExpectedCash = double.tryParse('${res['current_expected_cash']}') ?? 0;
        _symbol = res['symbol']?.toString() ?? 'Rp';
        _loading = false;
      });
    } catch (e) {
      if (!mounted) return;
      setState(() {
        _error = e.toString();
        _loading = false;
      });
    }
  }

  void _openOpenShiftDialog() {
    final cashierCtrl = TextEditingController(text: 'Kasir');
    final startingCashCtrl = TextEditingController();
    final notesCtrl = TextEditingController();

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: AppColors.card,
      shape: const RoundedRectangleBorder(borderRadius: BorderRadius.vertical(top: Radius.circular(20))),
      builder: (ctx) => Padding(
        padding: EdgeInsets.only(
          bottom: MediaQuery.of(ctx).viewInsets.bottom + 20,
          left: 20,
          right: 20,
          top: 16,
        ),
        child: SingleChildScrollView(
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  const Text('🔓 Buka Shift Kasir Baru', style: TextStyle(fontWeight: FontWeight.w800, fontSize: 16)),
                  IconButton(icon: const Icon(Icons.close), onPressed: () => Navigator.pop(ctx)),
                ],
              ),
              const SizedBox(height: 12),
              TextField(
                controller: cashierCtrl,
                decoration: const InputDecoration(labelText: 'Nama Kasir Bertugas'),
              ),
              const SizedBox(height: 10),
              TextField(
                controller: startingCashCtrl,
                keyboardType: TextInputType.number,
                decoration: InputDecoration(
                  labelText: 'Modal Awal Kas (Laci)',
                  prefixText: '$_symbol ',
                  hintText: '0',
                ),
              ),
              const SizedBox(height: 10),
              TextField(
                controller: notesCtrl,
                decoration: const InputDecoration(labelText: 'Catatan (Opsional)'),
              ),
              const SizedBox(height: 16),
              FilledButton.icon(
                onPressed: () async {
                  final startingCash = double.tryParse(startingCashCtrl.text.replaceAll('.', '').replaceAll(',', '')) ?? 0;
                  try {
                    await ApiService.instance.openPosShift(
                      cashierName: cashierCtrl.text.trim(),
                      startingCash: startingCash,
                      notes: notesCtrl.text.trim(),
                    );
                    if (ctx.mounted) Navigator.pop(ctx);
                    _load();
                  } catch (e) {
                    if (ctx.mounted) {
                      ScaffoldMessenger.of(ctx).showSnackBar(SnackBar(content: Text('Gagal: $e')));
                    }
                  }
                },
                icon: const Icon(Icons.check_circle_rounded),
                label: const Text('Buka Shift Sekarang'),
                style: FilledButton.styleFrom(minimumSize: const Size.fromHeight(44)),
              ),
            ],
          ),
        ),
      ),
    );
  }

  void _openCloseShiftDialog() {
    if (_activeShift == null) return;
    final actualCashCtrl = TextEditingController();
    final notesCtrl = TextEditingController();

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: AppColors.card,
      shape: const RoundedRectangleBorder(borderRadius: BorderRadius.vertical(top: Radius.circular(20))),
      builder: (ctx) => Padding(
        padding: EdgeInsets.only(
          bottom: MediaQuery.of(ctx).viewInsets.bottom + 20,
          left: 20,
          right: 20,
          top: 16,
        ),
        child: SingleChildScrollView(
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  const Text('🔒 Tutup Shift & Rekonsiliasi Kas', style: TextStyle(fontWeight: FontWeight.w800, fontSize: 16)),
                  IconButton(icon: const Icon(Icons.close), onPressed: () => Navigator.pop(ctx)),
                ],
              ),
              const SizedBox(height: 8),
              Container(
                padding: const EdgeInsets.all(12),
                decoration: BoxDecoration(
                  color: AppColors.borderLight,
                  borderRadius: BorderRadius.circular(12),
                ),
                child: Column(
                  children: [
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        const Text('Modal Awal Kas:', style: TextStyle(fontSize: 12, color: AppColors.textMuted)),
                        Text('$_symbol ${Fmt.money0(double.tryParse('${_activeShift!['starting_cash']}') ?? 0)}',
                            style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 12)),
                      ],
                    ),
                    const SizedBox(height: 4),
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        const Text('Penjualan Tunai Shift Ini:', style: TextStyle(fontSize: 12, color: AppColors.textMuted)),
                        Text('+ $_symbol ${Fmt.money0(_currentCashSales)}',
                            style: const TextStyle(fontWeight: FontWeight.bold, color: Colors.green, fontSize: 12)),
                      ],
                    ),
                    const Divider(height: 12),
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        const Text('Total Kas Seharusnya di Laci:', style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold)),
                        Text('$_symbol ${Fmt.money0(_currentExpectedCash)}',
                            style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 14, color: AppColors.primary)),
                      ],
                    ),
                  ],
                ),
              ),
              const SizedBox(height: 12),
              TextField(
                controller: actualCashCtrl,
                keyboardType: TextInputType.number,
                decoration: InputDecoration(
                  labelText: 'Hitungan Uang Fisik Nyata di Laci',
                  prefixText: '$_symbol ',
                  hintText: '0',
                ),
              ),
              const SizedBox(height: 10),
              TextField(
                controller: notesCtrl,
                decoration: const InputDecoration(labelText: 'Catatan Selisih / Serah Terima (Opsional)'),
              ),
              const SizedBox(height: 16),
              FilledButton.icon(
                onPressed: () async {
                  final actualCash = double.tryParse(actualCashCtrl.text.replaceAll('.', '').replaceAll(',', '')) ?? 0;
                  try {
                    await ApiService.instance.closePosShift(
                      shiftId: int.tryParse('${_activeShift!['id']}'),
                      actualCash: actualCash,
                      notes: notesCtrl.text.trim(),
                    );
                    if (ctx.mounted) Navigator.pop(ctx);
                    _load();
                  } catch (e) {
                    if (ctx.mounted) {
                      ScaffoldMessenger.of(ctx).showSnackBar(SnackBar(content: Text('Gagal: $e')));
                    }
                  }
                },
                icon: const Icon(Icons.lock_clock_rounded),
                label: const Text('Tutup Shift & Simpan Rekonsiliasi'),
                style: FilledButton.styleFrom(
                  minimumSize: const Size.fromHeight(44),
                  backgroundColor: Colors.red.shade700,
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.bg,
      appBar: AppBar(
        title: const Text('Shift Kasir & Laci Uang'),
        actions: [
          IconButton(icon: const Icon(Icons.refresh), onPressed: _load),
        ],
      ),
      body: _loading
          ? const Center(child: CircularProgressIndicator())
          : _error != null
              ? Center(child: Text('Error: $_error'))
              : ListView(
                  padding: const EdgeInsets.fromLTRB(16, 12, 16, 100),
                  children: [
                    // Active Shift Card
                    if (_activeShift != null) ...[
                      Container(
                        padding: const EdgeInsets.all(18),
                        decoration: BoxDecoration(
                          gradient: const LinearGradient(
                            colors: [Color(0xFF0F172A), Color(0xFF1E293B)],
                            begin: Alignment.topLeft,
                            end: Alignment.bottomRight,
                          ),
                          borderRadius: BorderRadius.circular(18),
                          border: Border.all(color: const Color(0xFFEA580C), width: 2),
                          boxShadow: const [
                            BoxShadow(color: Color(0x22EA580C), blurRadius: 16, offset: Offset(0, 6)),
                          ],
                        ),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Row(
                              mainAxisAlignment: MainAxisAlignment.spaceBetween,
                              children: [
                                Container(
                                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                                  decoration: BoxDecoration(
                                    color: const Color(0xFF10B981),
                                    borderRadius: BorderRadius.circular(6),
                                  ),
                                  child: const Text('● SHIFT AKTIF',
                                      style: TextStyle(color: Colors.white, fontSize: 10, fontWeight: FontWeight.w800)),
                                ),
                                Text(
                                  'Estimasi Laci: $_symbol ${Fmt.money0(_currentExpectedCash)}',
                                  style: const TextStyle(color: Color(0xFFFB923C), fontWeight: FontWeight.w800, fontSize: 13),
                                ),
                              ],
                            ),
                            const SizedBox(height: 10),
                            Text('Kasir: ${_activeShift!['cashier_name'] ?? 'Kasir'}',
                                style: const TextStyle(fontSize: 17, fontWeight: FontWeight.w800, color: Colors.white)),
                            Text('Dibuka: ${_activeShift!['opened_at'] ?? '-'}',
                                style: const TextStyle(fontSize: 11, color: Color(0xFF94A3B8))),
                            const SizedBox(height: 14),
                            Row(
                              children: [
                                Expanded(
                                  child: Container(
                                    padding: const EdgeInsets.all(10),
                                    decoration: BoxDecoration(
                                      color: Colors.white.withValues(alpha: .06),
                                      borderRadius: BorderRadius.circular(10),
                                    ),
                                    child: Column(
                                      crossAxisAlignment: CrossAxisAlignment.start,
                                      children: [
                                        const Text('Modal Awal:', style: TextStyle(fontSize: 10, color: Color(0xFF94A3B8))),
                                        Text('$_symbol ${Fmt.money0(double.tryParse('${_activeShift!['starting_cash']}') ?? 0)}',
                                            style: const TextStyle(fontSize: 13, fontWeight: FontWeight.bold, color: Colors.white)),
                                      ],
                                    ),
                                  ),
                                ),
                                const SizedBox(width: 8),
                                Expanded(
                                  child: Container(
                                    padding: const EdgeInsets.all(10),
                                    decoration: BoxDecoration(
                                      color: Colors.white.withValues(alpha: .06),
                                      borderRadius: BorderRadius.circular(10),
                                    ),
                                    child: Column(
                                      crossAxisAlignment: CrossAxisAlignment.start,
                                      children: [
                                        const Text('Penjualan Tunai:', style: TextStyle(fontSize: 10, color: Color(0xFF94A3B8))),
                                        Text('+ $_symbol ${Fmt.money0(_currentCashSales)}',
                                            style: const TextStyle(fontSize: 13, fontWeight: FontWeight.bold, color: Color(0xFF34D399))),
                                      ],
                                    ),
                                  ),
                                ),
                              ],
                            ),
                            const SizedBox(height: 14),
                            FilledButton.icon(
                              onPressed: _openCloseShiftDialog,
                              icon: const Icon(Icons.lock_clock_rounded, size: 16),
                              label: const Text('Tutup Shift & Rekonsiliasi Kas'),
                              style: FilledButton.styleFrom(
                                minimumSize: const Size.fromHeight(42),
                                backgroundColor: const Color(0xFFDC2626),
                              ),
                            ),
                          ],
                        ),
                      ),
                    ] else ...[
                      Container(
                        padding: const EdgeInsets.all(20),
                        decoration: BoxDecoration(
                          color: AppColors.card,
                          borderRadius: BorderRadius.circular(18),
                          border: Border.all(color: AppColors.border),
                        ),
                        child: Column(
                          children: [
                            const Icon(Icons.work_off_rounded, size: 40, color: AppColors.textMuted),
                            const SizedBox(height: 8),
                            const Text('Tidak ada shift kasir yang sedang aktif',
                                style: TextStyle(fontWeight: FontWeight.bold, fontSize: 14)),
                            const SizedBox(height: 4),
                            const Text('Buka shift baru sebelum kasir melayani transaksi.',
                                style: TextStyle(fontSize: 12, color: AppColors.textMuted)),
                            const SizedBox(height: 14),
                            FilledButton.icon(
                              onPressed: _openOpenShiftDialog,
                              icon: const Icon(Icons.lock_open_rounded, size: 16),
                              label: const Text('Buka Shift Kasir Baru'),
                              style: FilledButton.styleFrom(minimumSize: const Size.fromHeight(42)),
                            ),
                          ],
                        ),
                      ),
                    ],

                    const SizedBox(height: 24),
                    const Text('Riwayat Shift Sebelumnya', style: TextStyle(fontWeight: FontWeight.w800, fontSize: 15)),
                    const SizedBox(height: 10),

                    if (_shiftHistory.isEmpty)
                      const Padding(
                        padding: EdgeInsets.all(20),
                        child: Center(child: Text('Belum ada riwayat shift.', style: TextStyle(color: AppColors.textMuted))),
                      )
                    else
                      ..._shiftHistory.map((s) {
                        final diff = double.tryParse('${s['difference']}') ?? 0;
                        final isClosed = s['status'] == 'closed';
                        return Container(
                          margin: const EdgeInsets.only(bottom: 10),
                          padding: const EdgeInsets.all(14),
                          decoration: BoxDecoration(
                            color: AppColors.card,
                            borderRadius: BorderRadius.circular(14),
                            border: Border.all(color: AppColors.border),
                          ),
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Row(
                                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                children: [
                                  Text(s['cashier_name'] ?? 'Kasir', style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14)),
                                  Container(
                                    padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                                    decoration: BoxDecoration(
                                      color: isClosed ? Colors.grey.withValues(alpha: .15) : Colors.green.withValues(alpha: .15),
                                      borderRadius: BorderRadius.circular(6),
                                    ),
                                    child: Text(
                                      isClosed ? 'DITUTUP' : 'AKTIF',
                                      style: TextStyle(fontSize: 9, fontWeight: FontWeight.bold, color: isClosed ? Colors.grey.shade700 : Colors.green.shade700),
                                    ),
                                  ),
                                ],
                              ),
                              const SizedBox(height: 4),
                              Text('Waktu: ${s['opened_at'] ?? ''} s/d ${s['closed_at'] ?? 'Sekarang'}',
                                  style: const TextStyle(fontSize: 11, color: AppColors.textMuted)),
                              const Divider(height: 16),
                              Row(
                                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                children: [
                                  Text('Modal: $_symbol ${Fmt.money0(double.tryParse('${s['starting_cash']}') ?? 0)}', style: const TextStyle(fontSize: 12)),
                                  Text('Penjualan: $_symbol ${Fmt.money0(double.tryParse('${s['total_sales']}') ?? 0)}', style: const TextStyle(fontSize: 12, fontWeight: FontWeight.bold)),
                                ],
                              ),
                              if (isClosed) ...[
                                const SizedBox(height: 4),
                                Row(
                                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                  children: [
                                    Text('Kas Fisik: $_symbol ${Fmt.money0(double.tryParse('${s['actual_cash']}') ?? 0)}', style: const TextStyle(fontSize: 12)),
                                    Text(
                                      diff == 0 ? '✓ Sesuai (0)' : (diff > 0 ? '+ $_symbol ${Fmt.money0(diff)} (Lebih)' : '- $_symbol ${Fmt.money0(diff.abs())} (Kurang)'),
                                      style: TextStyle(fontSize: 12, fontWeight: FontWeight.w800, color: diff == 0 ? Colors.green : (diff > 0 ? Colors.blue : Colors.red)),
                                    ),
                                  ],
                                ),
                              ],
                            ],
                          ),
                        );
                      }),
                  ],
                ),
    );
  }
}
