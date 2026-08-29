import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../../providers/app_data_provider.dart';
import '../../theme.dart';
import '../../utils/format.dart';
import '../transaction_sheet.dart';

class ZakatPajakScreen extends StatefulWidget {
  final double initialBalance;
  final double initialMonthlyIncome;
  final String symbol;

  const ZakatPajakScreen({
    super.key,
    this.initialBalance = 0,
    this.initialMonthlyIncome = 0,
    this.symbol = 'Rp',
  });

  @override
  State<ZakatPajakScreen> createState() => _ZakatPajakScreenState();
}

class _ZakatPajakScreenState extends State<ZakatPajakScreen> with SingleTickerProviderStateMixin {
  late TabController _tabController;

  // ── 1. Zakat Maal State ─────────────────────────────────────
  final _goldPriceCtrl = TextEditingController(text: '1400000'); // Rp 1.400.000 / gram
  final _otherAssetsCtrl = TextEditingController(text: '0');
  final _shortDebtCtrl = TextEditingController(text: '0');
  bool _maalReachedHaul = true;

  // ── 2. Zakat Profesi State ──────────────────────────────────
  late TextEditingController _salaryCtrl;
  final _otherIncomeCtrl = TextEditingController(text: '0');
  final _livingExpenseCtrl = TextEditingController(text: '0');
  final _ricePriceCtrl = TextEditingController(text: '15000'); // Rp 15.000 / kg

  // ── 3. Zakat Fitrah State ───────────────────────────────────
  int _fitrahPersonCount = 1;
  final _fitrahPricePerPersonCtrl = TextEditingController(text: '45000'); // Rp 45.000 / jiwa

  // ── 4. Pajak State ──────────────────────────────────────────
  int _taxMode = 0; // 0: PPh Final UMKM 0.5%, 1: PPh 21 Pribadi
  final _umkmOmsetCtrl = TextEditingController(text: '10000000'); // Omset 10 Juta/bln
  bool _umkmBelow500M = true; // Fasilitas bebas pajak s.d 500jt/thn untuk OP

  final _pph21SalaryCtrl = TextEditingController(text: '8000000'); // Gaji 8 Juta/bln
  String _ptkpStatus = 'TK/0';

  @override
  void initState() {
    super.initState();
    _tabController = TabController(length: 4, vsync: this);
    _salaryCtrl = TextEditingController(
      text: widget.initialMonthlyIncome > 0 ? '${widget.initialMonthlyIncome.toInt()}' : '5000000',
    );
  }

  @override
  void dispose() {
    _tabController.dispose();
    _goldPriceCtrl.dispose();
    _otherAssetsCtrl.dispose();
    _shortDebtCtrl.dispose();
    _salaryCtrl.dispose();
    _otherIncomeCtrl.dispose();
    _livingExpenseCtrl.dispose();
    _ricePriceCtrl.dispose();
    _fitrahPricePerPersonCtrl.dispose();
    _umkmOmsetCtrl.dispose();
    _pph21SalaryCtrl.dispose();
    super.dispose();
  }

  void _recordExpense(double amount, String note) {
    if (amount <= 0) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Nominal harus lebih dari 0 untuk dicatat.')),
      );
      return;
    }

    final appData = context.read<AppDataProvider>();
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (_) => TransactionSheet(
        categories: appData.categories,
        wallets: appData.wallets,
        initialAmount: amount,
        initialNote: note,
        initialType: 'expense',
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.bg,
      appBar: AppBar(
        title: const Text('Kalkulator Zakat & Pajak'),
        bottom: TabBar(
          controller: _tabController,
          isScrollable: true,
          labelColor: AppColors.primary,
          unselectedLabelColor: AppColors.textMuted,
          indicatorColor: AppColors.primary,
          indicatorWeight: 3,
          labelStyle: const TextStyle(fontWeight: FontWeight.w800, fontSize: 13),
          tabs: const [
            Tab(text: 'Zakat Maal'),
            Tab(text: 'Zakat Profesi'),
            Tab(text: 'Zakat Fitrah'),
            Tab(text: 'Pajak (PPh)'),
          ],
        ),
      ),
      body: TabBarView(
        controller: _tabController,
        children: [
          _buildZakatMaalTab(),
          _buildZakatProfesiTab(),
          _buildZakatFitrahTab(),
          _buildPajakTab(),
        ],
      ),
    );
  }

  // ═════════════════════════════════════════════════════════════════
  // 1. TAB: ZAKAT MAAL
  // ═════════════════════════════════════════════════════════════════
  Widget _buildZakatMaalTab() {
    final goldPrice = Fmt.toDouble(_goldPriceCtrl.text);
    final nisabMaal = 85 * goldPrice; // 85 gram emas
    final otherAssets = Fmt.toDouble(_otherAssetsCtrl.text);
    final shortDebt = Fmt.toDouble(_shortDebtCtrl.text);
    final totalWealth = (widget.initialBalance + otherAssets) - shortDebt;

    final isWajib = totalWealth >= nisabMaal && _maalReachedHaul;
    final zakatAmount = isWajib ? totalWealth * 0.025 : 0.0;

    return ListView(
      padding: const EdgeInsets.all(16),
      children: [
        _buildInfoBanner(
          icon: '💰',
          title: 'Zakat Maal (Harta & Tabungan)',
          subtitle: 'Wajib dikeluarkan sebesar 2.5% jika total harta tersimpan mencapai nisab 85 gram emas dan telah mengendap 1 tahun (haul).',
        ),
        const SizedBox(height: 16),

        _buildCardWrapper(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              const Text('Data Harta & Nisab', style: TextStyle(fontSize: 14, fontWeight: FontWeight.w800)),
              const SizedBox(height: 12),
              _buildReadOnlyRow('Total Saldo Dompet Aktif', Fmt.money(widget.initialBalance, symbol: widget.symbol)),
              const SizedBox(height: 10),
              TextField(
                controller: _otherAssetsCtrl,
                keyboardType: TextInputType.number,
                onChanged: (_) => setState(() {}),
                decoration: const InputDecoration(
                  labelText: 'Aset Likuid Lainnya (Emas/Tabungan Lain)',
                  prefixText: 'Rp ',
                ),
              ),
              const SizedBox(height: 10),
              TextField(
                controller: _shortDebtCtrl,
                keyboardType: TextInputType.number,
                onChanged: (_) => setState(() {}),
                decoration: const InputDecoration(
                  labelText: 'Hutang Jatuh Tempo (Pengurang)',
                  prefixText: 'Rp ',
                ),
              ),
              const SizedBox(height: 10),
              TextField(
                controller: _goldPriceCtrl,
                keyboardType: TextInputType.number,
                onChanged: (_) => setState(() {}),
                decoration: const InputDecoration(
                  labelText: 'Harga Emas / Gram Saat Ini',
                  prefixText: 'Rp ',
                  helperText: 'Nisab 85 gram emas',
                ),
              ),
              const SizedBox(height: 12),
              SwitchListTile(
                contentPadding: EdgeInsets.zero,
                title: const Text('Harta Telah Mengendap 1 Tahun (Haul)', style: TextStyle(fontSize: 13, fontWeight: FontWeight.w700)),
                value: _maalReachedHaul,
                activeThumbColor: AppColors.primary,
                onChanged: (v) => setState(() => _maalReachedHaul = v),
              ),
            ],
          ),
        ),
        const SizedBox(height: 16),

        // Hasil Kalkulasi
        _buildResultCard(
          title: isWajib ? 'WAJIB ZAKAT MAAL (2.5%)' : 'BELUM WAJIB ZAKAT',
          isEligible: isWajib,
          amount: isWajib ? zakatAmount : 0,
          description: isWajib
              ? 'Total harta bersih Rp ${Fmt.money0(totalWealth)} telah melewati nisab Rp ${Fmt.money0(nisabMaal)}.'
              : 'Total harta Rp ${Fmt.money0(totalWealth)} belum mencapai nisab 85g emas (Rp ${Fmt.money0(nisabMaal)}).',
          onRecord: isWajib ? () => _recordExpense(zakatAmount, 'Pembayaran Zakat Maal') : null,
        ),
      ],
    );
  }

  // ═════════════════════════════════════════════════════════════════
  // 2. TAB: ZAKAT PROFESI / PENGHASILAN
  // ═════════════════════════════════════════════════════════════════
  Widget _buildZakatProfesiTab() {
    final salary = Fmt.toDouble(_salaryCtrl.text);
    final otherIncome = Fmt.toDouble(_otherIncomeCtrl.text);
    final livingExpense = Fmt.toDouble(_livingExpenseCtrl.text);
    final ricePrice = Fmt.toDouble(_ricePriceCtrl.text);

    final netIncome = (salary + otherIncome) - livingExpense;
    // Nisab zakat profesi setara 524 kg beras / bulan (SK BAZNAS)
    final nisabProfesi = 524 * ricePrice;
    final isWajib = netIncome >= nisabProfesi;
    final zakatProfesi = isWajib ? netIncome * 0.025 : 0.0;

    return ListView(
      padding: const EdgeInsets.all(16),
      children: [
        _buildInfoBanner(
          icon: '💼',
          title: 'Zakat Penghasilan / Profesi',
          subtitle: 'Dikeluarkan dari penghasilan rutin bulanan sebesar 2.5% jika telah mencapai nisab setara 524 kg beras per bulan (standar BAZNAS).',
        ),
        const SizedBox(height: 16),

        _buildCardWrapper(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              const Text('Data Pemasukan & Pengeluaran', style: TextStyle(fontSize: 14, fontWeight: FontWeight.w800)),
              const SizedBox(height: 12),
              TextField(
                controller: _salaryCtrl,
                keyboardType: TextInputType.number,
                onChanged: (_) => setState(() {}),
                decoration: const InputDecoration(labelText: 'Gaji Pokok / Omset Bulanan', prefixText: 'Rp '),
              ),
              const SizedBox(height: 10),
              TextField(
                controller: _otherIncomeCtrl,
                keyboardType: TextInputType.number,
                onChanged: (_) => setState(() {}),
                decoration: const InputDecoration(labelText: 'Bonus / Pendapatan Lain', prefixText: 'Rp '),
              ),
              const SizedBox(height: 10),
              TextField(
                controller: _livingExpenseCtrl,
                keyboardType: TextInputType.number,
                onChanged: (_) => setState(() {}),
                decoration: const InputDecoration(
                  labelText: 'Kebutuhan Pokok / Hutang Bulanan',
                  prefixText: 'Rp ',
                  helperText: 'Pengurang penghasilan kotor (metode zakat netto)',
                ),
              ),
              const SizedBox(height: 10),
              TextField(
                controller: _ricePriceCtrl,
                keyboardType: TextInputType.number,
                onChanged: (_) => setState(() {}),
                decoration: const InputDecoration(
                  labelText: 'Harga Beras / Kg',
                  prefixText: 'Rp ',
                  helperText: 'Nisab setara 524 kg beras/bulan',
                ),
              ),
            ],
          ),
        ),
        const SizedBox(height: 16),

        _buildResultCard(
          title: isWajib ? 'ZAKAT PROFESI BULANAN (2.5%)' : 'BELUM MENCAPAI NISAB',
          isEligible: isWajib,
          amount: isWajib ? zakatProfesi : 0,
          description: isWajib
              ? 'Penghasilan bersih Rp ${Fmt.money0(netIncome)}/bln melewati nisab 524 kg beras (Rp ${Fmt.money0(nisabProfesi)}).'
              : 'Penghasilan bersih Rp ${Fmt.money0(netIncome)}/bln belum mencapai nisab (Rp ${Fmt.money0(nisabProfesi)}).',
          onRecord: isWajib ? () => _recordExpense(zakatProfesi, 'Pembayaran Zakat Profesi') : null,
        ),
      ],
    );
  }

  // ═════════════════════════════════════════════════════════════════
  // 3. TAB: ZAKAT FITRAH
  // ═════════════════════════════════════════════════════════════════
  Widget _buildZakatFitrahTab() {
    final pricePerPerson = Fmt.toDouble(_fitrahPricePerPersonCtrl.text);
    final totalFitrah = _fitrahPersonCount * pricePerPerson;

    return ListView(
      padding: const EdgeInsets.all(16),
      children: [
        _buildInfoBanner(
          icon: '🌾',
          title: 'Zakat Fitrah',
          subtitle: 'Kewajiban zakat bagi setiap muslim di bulan Ramadhan setara 2.5 kg / 3.5 liter beras atau uang tunai per jiwa.',
        ),
        const SizedBox(height: 16),

        _buildCardWrapper(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              const Text('Jumlah Jiwa & Tarif', style: TextStyle(fontSize: 14, fontWeight: FontWeight.w800)),
              const SizedBox(height: 14),
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  const Text('Jumlah Anggota Keluarga (Jiwa):', style: TextStyle(fontSize: 13, fontWeight: FontWeight.w700)),
                  Row(
                    children: [
                      IconButton.outlined(
                        icon: const Icon(Icons.remove, size: 18),
                        onPressed: _fitrahPersonCount > 1 ? () => setState(() => _fitrahPersonCount--) : null,
                      ),
                      Padding(
                        padding: const EdgeInsets.symmetric(horizontal: 14),
                        child: Text('$_fitrahPersonCount', style: const TextStyle(fontSize: 18, fontWeight: FontWeight.w900)),
                      ),
                      IconButton.outlined(
                        icon: const Icon(Icons.add, size: 18),
                        onPressed: () => setState(() => _fitrahPersonCount++),
                      ),
                    ],
                  ),
                ],
              ),
              const SizedBox(height: 14),
              TextField(
                controller: _fitrahPricePerPersonCtrl,
                keyboardType: TextInputType.number,
                onChanged: (_) => setState(() {}),
                decoration: const InputDecoration(
                  labelText: 'Nominal Beras/Uang Per Jiwa',
                  prefixText: 'Rp ',
                  helperText: 'Standar BAZNAS: Rp 45.000 - Rp 55.000 / jiwa',
                ),
              ),
            ],
          ),
        ),
        const SizedBox(height: 16),

        _buildResultCard(
          title: 'TOTAL ZAKAT FITRAH',
          isEligible: true,
          amount: totalFitrah,
          description: 'Total untuk $_fitrahPersonCount jiwa × Rp ${Fmt.money0(pricePerPerson)}/jiwa.',
          onRecord: () => _recordExpense(totalFitrah, 'Pembayaran Zakat Fitrah ($_fitrahPersonCount Jiwa)'),
        ),
      ],
    );
  }

  // ═════════════════════════════════════════════════════════════════
  // 4. TAB: PAJAK (PPH FINAL UMKM & PPH 21)
  // ═════════════════════════════════════════════════════════════════
  Widget _buildPajakTab() {
    double taxAmount = 0;
    String taxDescription = '';

    if (_taxMode == 0) {
      // PPh Final UMKM 0.5% (PP 55/2022)
      final monthlyOmset = Fmt.toDouble(_umkmOmsetCtrl.text);
      final yearlyOmset = monthlyOmset * 12;

      if (_umkmBelow500M && yearlyOmset <= 500000000) {
        taxAmount = 0;
        taxDescription = 'Bebas pajak karena omset tahunan (Rp ${Fmt.money0(yearlyOmset)}) di bawah Rp 500 Juta/tahun untuk OP UMKM.';
      } else {
        // Kena 0.5%
        taxAmount = monthlyOmset * 0.005;
        taxDescription = 'PPh Final 0.5% atas omset bulanan Rp ${Fmt.money0(monthlyOmset)} = Rp ${Fmt.money0(taxAmount)}/bulan.';
      }
    } else {
      // PPh 21 Pribadi Sederhana
      final salaryMonthly = Fmt.toDouble(_pph21SalaryCtrl.text);
      final salaryYearly = salaryMonthly * 12;

      // PTKP Map
      final ptkpValues = {
        'TK/0': 54000000.0,
        'TK/1': 58500000.0,
        'K/0': 58500000.0,
        'K/1': 63000000.0,
        'K/2': 67500000.0,
        'K/3': 72000000.0,
      };
      final ptkp = ptkpValues[_ptkpStatus] ?? 54000000.0;
      final pkp = (salaryYearly - ptkp).clamp(0.0, double.infinity);

      // Tarif Progresif UU HPP
      double yearlyTax = 0;
      if (pkp > 0) {
        if (pkp <= 60000000) {
          yearlyTax = pkp * 0.05;
        } else if (pkp <= 250000000) {
          yearlyTax = (60000000 * 0.05) + ((pkp - 60000000) * 0.15);
        } else if (pkp <= 500000000) {
          yearlyTax = (60000000 * 0.05) + (190000000 * 0.15) + ((pkp - 250000000) * 0.25);
        } else {
          yearlyTax = (60000000 * 0.05) + (190000000 * 0.15) + (250000000 * 0.25) + ((pkp - 500000000) * 0.30);
        }
      }
      taxAmount = yearlyTax / 12;
      taxDescription = 'Penghasilan Kena Pajak (PKP) = Rp ${Fmt.money0(pkp)}/thn setelah PTKP $_ptkpStatus (Rp ${Fmt.money0(ptkp)}).';
    }

    return ListView(
      padding: const EdgeInsets.all(16),
      children: [
        // Mode Selector (UMKM vs PPh 21)
        SegmentedButton<int>(
          segments: const [
            ButtonSegment(value: 0, label: Text('PPh Final UMKM 0.5%')),
            ButtonSegment(value: 1, label: Text('PPh 21 Karyawan/Pribadi')),
          ],
          selected: {_taxMode},
          onSelectionChanged: (set) => setState(() => _taxMode = set.first),
        ),
        const SizedBox(height: 16),

        if (_taxMode == 0) ...[
          _buildInfoBanner(
            icon: '🏪',
            title: 'PPh Final PP 55/2022 (0.5%)',
            subtitle: 'Tarif PPh Final 0.5% untuk pelaku usaha UMKM / warung kasir POS.',
          ),
          const SizedBox(height: 16),

          _buildCardWrapper(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Text('Data Omset Usaha', style: TextStyle(fontSize: 14, fontWeight: FontWeight.w800)),
                const SizedBox(height: 12),
                TextField(
                  controller: _umkmOmsetCtrl,
                  keyboardType: TextInputType.number,
                  onChanged: (_) => setState(() {}),
                  decoration: const InputDecoration(labelText: 'Omset / Penjualan Bulanan', prefixText: 'Rp '),
                ),
                const SizedBox(height: 10),
                SwitchListTile(
                  contentPadding: EdgeInsets.zero,
                  title: const Text('Wajib Pajak Orang Pribadi (Bebas s.d Rp 500 Juta/thn)', style: TextStyle(fontSize: 12.5, fontWeight: FontWeight.w700)),
                  subtitle: const Text('Omset sampai 500 juta setahun tidak dikenai pajak 0.5%', style: TextStyle(fontSize: 11)),
                  value: _umkmBelow500M,
                  activeThumbColor: AppColors.primary,
                  onChanged: (v) => setState(() => _umkmBelow500M = v),
                ),
              ],
            ),
          ),
        ] else ...[
          _buildInfoBanner(
            icon: '📑',
            title: 'Simulasi PPh 21 Karyawan (UU HPP)',
            subtitle: 'Kalkulasi pajak penghasilan tahunan berdasarkan PTKP dan tarif progresif (5% - 35%).',
          ),
          const SizedBox(height: 16),

          _buildCardWrapper(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Text('Data Gaji & Status PTKP', style: TextStyle(fontSize: 14, fontWeight: FontWeight.w800)),
                const SizedBox(height: 12),
                TextField(
                  controller: _pph21SalaryCtrl,
                  keyboardType: TextInputType.number,
                  onChanged: (_) => setState(() {}),
                  decoration: const InputDecoration(labelText: 'Gaji Bruto Bulanan', prefixText: 'Rp '),
                ),
                const SizedBox(height: 12),
                DropdownButtonFormField<String>(
                  initialValue: _ptkpStatus,
                  decoration: const InputDecoration(labelText: 'Status Perkawinan & Tanggungan (PTKP)'),
                  items: const [
                    DropdownMenuItem(value: 'TK/0', child: Text('TK/0 — Tidak Kawin (Rp 54 Juta)')),
                    DropdownMenuItem(value: 'TK/1', child: Text('TK/1 — 1 Tanggungan (Rp 58.5 Juta)')),
                    DropdownMenuItem(value: 'K/0', child: Text('K/0 — Kawin Tanpa Anak (Rp 58.5 Juta)')),
                    DropdownMenuItem(value: 'K/1', child: Text('K/1 — Kawin 1 Anak (Rp 63 Juta)')),
                    DropdownMenuItem(value: 'K/2', child: Text('K/2 — Kawin 2 Anak (Rp 67.5 Juta)')),
                    DropdownMenuItem(value: 'K/3', child: Text('K/3 — Kawin 3 Anak (Rp 72 Juta)')),
                  ],
                  onChanged: (v) => setState(() => _ptkpStatus = v ?? 'TK/0'),
                ),
              ],
            ),
          ),
        ],
        const SizedBox(height: 16),

        _buildResultCard(
          title: _taxMode == 0 ? 'PPH FINAL UMKM BULANAN' : 'ESTIMASI PPH 21 PER BULAN',
          isEligible: taxAmount > 0,
          amount: taxAmount,
          description: taxDescription,
          onRecord: taxAmount > 0 ? () => _recordExpense(taxAmount, _taxMode == 0 ? 'Pajak PPh Final UMKM 0.5%' : 'Pajak PPh 21') : null,
        ),
      ],
    );
  }

  // ═════════════════════════════════════════════════════════════════
  // REUSABLE HELPER WIDGETS
  // ═════════════════════════════════════════════════════════════════
  Widget _buildInfoBanner({required String icon, required String title, required String subtitle}) {
    return Container(
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: const Color(0xFFEFF6FF),
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: const Color(0xFFBFDBFE)),
      ),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(icon, style: const TextStyle(fontSize: 24)),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(title, style: const TextStyle(fontSize: 13.5, fontWeight: FontWeight.w800, color: Color(0xFF1E3A8A))),
                const SizedBox(height: 2),
                Text(subtitle, style: const TextStyle(fontSize: 11, color: Color(0xFF3B82F6), height: 1.3)),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildCardWrapper({required Widget child}) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: AppColors.card,
        borderRadius: BorderRadius.circular(18),
        border: Border.all(color: AppColors.border),
        boxShadow: AppColors.cardShadow,
      ),
      child: child,
    );
  }

  Widget _buildReadOnlyRow(String label, String value) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
      decoration: BoxDecoration(
        color: AppColors.bg,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: AppColors.borderLight),
      ),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Text(label, style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w600, color: AppColors.textSecondary)),
          Text(value, style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w800, color: AppColors.textPrimary)),
        ],
      ),
    );
  }

  Widget _buildResultCard({
    required String title,
    required bool isEligible,
    required double amount,
    required String description,
    VoidCallback? onRecord,
  }) {
    return Container(
      padding: const EdgeInsets.all(18),
      decoration: BoxDecoration(
        color: AppColors.card,
        borderRadius: BorderRadius.circular(20),
        border: Border.all(
          color: isEligible ? AppColors.primary : AppColors.border,
          width: isEligible ? 1.5 : 1,
        ),
        boxShadow: AppColors.cardShadow,
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Text(
                title,
                style: TextStyle(
                  fontSize: 11.5,
                  fontWeight: FontWeight.w800,
                  color: isEligible ? AppColors.primary : AppColors.textMuted,
                  letterSpacing: 0.5,
                ),
              ),
              if (isEligible)
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                  decoration: BoxDecoration(
                    color: AppColors.incomeSubtle,
                    borderRadius: BorderRadius.circular(8),
                  ),
                  child: const Text('Wajib / Terutang', style: TextStyle(fontSize: 10.5, fontWeight: FontWeight.w800, color: AppColors.income)),
                ),
            ],
          ),
          const SizedBox(height: 8),
          Text(
            Fmt.money(amount, symbol: widget.symbol),
            style: TextStyle(
              fontSize: 26,
              fontWeight: FontWeight.w900,
              color: isEligible ? AppColors.textPrimary : AppColors.textMuted,
              letterSpacing: -0.5,
            ),
          ),
          const SizedBox(height: 6),
          Text(
            description,
            style: const TextStyle(fontSize: 11.5, color: AppColors.textSecondary, height: 1.3),
          ),
          if (onRecord != null) ...[
            const SizedBox(height: 16),
            FilledButton.icon(
              onPressed: onRecord,
              icon: const Icon(Icons.receipt_long_rounded, size: 18),
              label: const Text('Catat Sebagai Pengeluaran'),
              style: FilledButton.styleFrom(
                backgroundColor: AppColors.primary,
                minimumSize: const Size.fromHeight(48),
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
              ),
            ),
          ],
        ],
      ),
    );
  }
}
