import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:provider/provider.dart';

import '../../providers/app_data_provider.dart';
import '../../services/bank_notification_parser.dart';
import '../../theme.dart';
import '../../utils/format.dart';
import '../transaction_sheet.dart';

class NotificationDetectorScreen extends StatefulWidget {
  final String? initialText;

  const NotificationDetectorScreen({super.key, this.initialText});

  @override
  State<NotificationDetectorScreen> createState() => _NotificationDetectorScreenState();
}

class _NotificationDetectorScreenState extends State<NotificationDetectorScreen> {
  final _textCtrl = TextEditingController();
  ParsedBankNotification? _parsed;
  int? _selectedWalletId;
  int? _selectedCategoryId;

  @override
  void initState() {
    super.initState();
    if (widget.initialText != null && widget.initialText!.isNotEmpty) {
      _textCtrl.text = widget.initialText!;
      _parseText(widget.initialText!);
    } else {
      // Auto-read clipboard on screen open
      _checkClipboard();
    }
  }

  @override
  void dispose() {
    _textCtrl.dispose();
    super.dispose();
  }

  Future<void> _checkClipboard() async {
    try {
      final data = await Clipboard.getData(Clipboard.kTextPlain);
      if (data != null && data.text != null && data.text!.trim().isNotEmpty) {
        final txt = data.text!.trim();
        // Cek apakah ada pola bank atau nominal uang
        if (txt.toLowerCase().contains('rp') ||
            txt.toLowerCase().contains('bca') ||
            txt.toLowerCase().contains('mandiri') ||
            txt.toLowerCase().contains('transfer') ||
            txt.toLowerCase().contains('gopay') ||
            txt.toLowerCase().contains('ovo') ||
            txt.toLowerCase().contains('dana')) {
          setState(() {
            _textCtrl.text = txt;
            _parseText(txt);
          });
        }
      }
    } catch (_) {}
  }

  void _parseText(String text) {
    final result = BankNotificationParser.parse(text);
    setState(() {
      _parsed = result;
    });
    _autoMatchWallet(result.providerName);
  }

  void _autoMatchWallet(String providerName) {
    final appData = context.read<AppDataProvider>();
    final wallets = appData.wallets;
    if (wallets.isEmpty) return;

    final lowerProvider = providerName.toLowerCase();
    for (final w in wallets) {
      final lowerW = w.name.toLowerCase();
      if (lowerW.contains(lowerProvider) || lowerProvider.contains(lowerW)) {
        setState(() {
          _selectedWalletId = w.id;
        });
        return;
      }
    }

    // Default ke dompet default
    if (_selectedWalletId == null) {
      final defW = wallets.firstWhere((w) => w.isDefault, orElse: () => wallets.first);
      setState(() {
        _selectedWalletId = defW.id;
      });
    }
  }

  void _setSample(String sampleText) {
    _textCtrl.text = sampleText;
    _parseText(sampleText);
  }

  void _openTransactionSheet() {
    if (_parsed == null || _parsed!.amount <= 0) return;

    final appData = context.read<AppDataProvider>();
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (_) => TransactionSheet(
        categories: appData.categories,
        wallets: appData.wallets,
        initialAmount: _parsed!.amount,
        initialNote: _parsed!.merchantOrRecipient,
        initialType: _parsed!.type,
        initialWalletId: _selectedWalletId,
        initialCategoryId: _selectedCategoryId,
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final appData = context.watch<AppDataProvider>();
    final wallets = appData.wallets;
    final categories = appData.categories;

    return Scaffold(
      backgroundColor: AppColors.bg,
      appBar: AppBar(
        title: const Text('Auto-Detect Bank & E-Wallet'),
        actions: [
          IconButton(
            icon: const Icon(Icons.paste_rounded),
            tooltip: 'Tempel dari Clipboard',
            onPressed: () async {
              final data = await Clipboard.getData(Clipboard.kTextPlain);
              if (data != null && data.text != null) {
                _textCtrl.text = data.text!;
                _parseText(data.text!);
              }
            },
          ),
        ],
      ),
      body: ListView(
        padding: const EdgeInsets.fromLTRB(16, 12, 16, 40),
        children: [
          // Banner Penjelasan
          Container(
            padding: const EdgeInsets.all(14),
            decoration: BoxDecoration(
              gradient: const LinearGradient(
                colors: [Color(0xFF1E3A8A), Color(0xFF2563EB)],
                begin: Alignment.topLeft,
                end: Alignment.bottomRight,
              ),
              borderRadius: BorderRadius.circular(18),
              boxShadow: AppColors.blueGlowShadow,
            ),
            child: Row(
              children: [
                Container(
                  padding: const EdgeInsets.all(10),
                  decoration: const BoxDecoration(
                    color: Colors.white24,
                    shape: BoxShape.circle,
                  ),
                  child: const Icon(Icons.auto_awesome_rounded, color: Colors.white, size: 24),
                ),
                const SizedBox(width: 14),
                const Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        'Pencatatan Otomatis dari Notifikasi',
                        style: TextStyle(fontSize: 13.5, fontWeight: FontWeight.w800, color: Colors.white),
                      ),
                      SizedBox(height: 3),
                      Text(
                        'Salin pesan SMS / notifikasi transaksi BCA, Mandiri, BRI, GoPay, OVO, DANA, dll.',
                        style: TextStyle(fontSize: 11, color: Colors.white70),
                      ),
                    ],
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(height: 16),

          // Area Input Teks / Paste
          Container(
            decoration: BoxDecoration(
              color: AppColors.card,
              borderRadius: BorderRadius.circular(18),
              border: Border.all(color: AppColors.border),
              boxShadow: AppColors.cardShadow,
            ),
            padding: const EdgeInsets.all(14),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    const Text(
                      'Teks Notifikasi / SMS',
                      style: TextStyle(fontSize: 12, fontWeight: FontWeight.w800, color: AppColors.textSecondary),
                    ),
                    if (_textCtrl.text.isNotEmpty)
                      InkWell(
                        onTap: () {
                          _textCtrl.clear();
                          setState(() => _parsed = null);
                        },
                        child: const Text('Hapus', style: TextStyle(fontSize: 12, color: Colors.red, fontWeight: FontWeight.w600)),
                      ),
                  ],
                ),
                const SizedBox(height: 8),
                TextField(
                  controller: _textCtrl,
                  maxLines: 4,
                  onChanged: _parseText,
                  decoration: const InputDecoration(
                    hintText: 'Tempel (paste) pesan SMS bank atau notifikasi e-wallet di sini...',
                    hintStyle: TextStyle(fontSize: 13, color: AppColors.textMuted),
                    border: InputBorder.none,
                    enabledBorder: InputBorder.none,
                    focusedBorder: InputBorder.none,
                    contentPadding: EdgeInsets.zero,
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(height: 12),

          // Tombol Sampel Notifikasi Cepat
          const Text(
            'Contoh Notifikasi Cepat:',
            style: TextStyle(fontSize: 11.5, fontWeight: FontWeight.w700, color: AppColors.textMuted),
          ),
          const SizedBox(height: 6),
          SingleChildScrollView(
            scrollDirection: Axis.horizontal,
            child: Row(
              children: [
                _buildSampleChip('BCA QRIS', 'BCA: Transaksi QRIS Rp 45.000 di Kopi Kenangan berhasil. No Ref 849204.'),
                _buildSampleChip('Livin Mandiri', 'Mandiri: Transfer keluar Rp 250.000 ke Bpk Budi berhasil.'),
                _buildSampleChip('GoPay', 'Pembayaran GoPay sebesar Rp 28.500 ke Indomaret berhasil.'),
                _buildSampleChip('BRImo Masuk', 'BRImo: Transfer masuk Rp 1.500.000 dari PT Maju Jaya. Saldo bertambah.'),
                _buildSampleChip('DANA Kirim', 'DANA: Berhasil kirim uang Rp 100.000 ke Rekening BCA.'),
              ],
            ),
          ),
          const SizedBox(height: 16),

          // Hasil Deteksi Parser
          if (_parsed != null && _parsed!.isValid) ...[
            Container(
              padding: const EdgeInsets.all(18),
              decoration: BoxDecoration(
                color: AppColors.card,
                borderRadius: BorderRadius.circular(20),
                border: Border.all(color: AppColors.primary.withValues(alpha: 0.5), width: 1.5),
                boxShadow: AppColors.cardShadow,
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Row(
                        children: [
                          Text(_parsed!.providerIcon, style: const TextStyle(fontSize: 20)),
                          const SizedBox(width: 8),
                          Text(
                            _parsed!.providerName,
                            style: const TextStyle(fontSize: 15, fontWeight: FontWeight.w800, color: AppColors.textPrimary),
                          ),
                        ],
                      ),
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                        decoration: BoxDecoration(
                          color: _parsed!.type == 'income' ? AppColors.incomeSubtle : AppColors.expenseSubtle,
                          borderRadius: BorderRadius.circular(10),
                        ),
                        child: Text(
                          _parsed!.type == 'income' ? 'Pemasukan (+)' : 'Pengeluaran (-)',
                          style: TextStyle(
                            fontSize: 11,
                            fontWeight: FontWeight.w800,
                            color: _parsed!.type == 'income' ? AppColors.income : AppColors.expense,
                          ),
                        ),
                      ),
                    ],
                  ),
                  const Divider(height: 24),
                  const Text('Nominal Terdeteksi', style: TextStyle(fontSize: 11, color: AppColors.textMuted, fontWeight: FontWeight.w600)),
                  const SizedBox(height: 2),
                  Text(
                    'Rp ${Fmt.money0(_parsed!.amount)}',
                    style: TextStyle(
                      fontSize: 26,
                      fontWeight: FontWeight.w900,
                      color: _parsed!.type == 'income' ? AppColors.income : AppColors.textPrimary,
                      letterSpacing: -0.5,
                    ),
                  ),
                  const SizedBox(height: 12),
                  Row(
                    children: [
                      const Icon(Icons.storefront_rounded, size: 16, color: AppColors.textMuted),
                      const SizedBox(width: 6),
                      Expanded(
                        child: Text(
                          _parsed!.merchantOrRecipient ?? 'Transaksi ${_parsed!.providerName}',
                          style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w700, color: AppColors.textSecondary),
                        ),
                      ),
                    ],
                  ),
                  if (_parsed!.refNumber != null) ...[
                    const SizedBox(height: 6),
                    Row(
                      children: [
                        const Icon(Icons.tag_rounded, size: 16, color: AppColors.textMuted),
                        const SizedBox(width: 6),
                        Text(
                          'Ref: ${_parsed!.refNumber}',
                          style: const TextStyle(fontSize: 11.5, color: AppColors.textMuted),
                        ),
                      ],
                    ),
                  ],
                ],
              ),
            ),
            const SizedBox(height: 16),

            // Pilihan Dompet & Kategori
            Container(
              padding: const EdgeInsets.all(16),
              decoration: BoxDecoration(
                color: AppColors.card,
                borderRadius: BorderRadius.circular(18),
                border: Border.all(color: AppColors.border),
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const Text('Tentukan Dompet & Kategori', style: TextStyle(fontSize: 13, fontWeight: FontWeight.w800)),
                  const SizedBox(height: 12),
                  DropdownButtonFormField<int>(
                    initialValue: _selectedWalletId,
                    decoration: const InputDecoration(
                      labelText: 'Pilih Dompet Akun',
                      prefixIcon: Icon(Icons.account_balance_wallet_rounded, size: 20),
                    ),
                    items: wallets.map((w) => DropdownMenuItem(value: w.id, child: Text(w.name))).toList(),
                    onChanged: (v) => setState(() => _selectedWalletId = v),
                  ),
                  const SizedBox(height: 12),
                  DropdownButtonFormField<int>(
                    initialValue: _selectedCategoryId,
                    decoration: const InputDecoration(
                      labelText: 'Pilih Kategori (Opsional)',
                      prefixIcon: Icon(Icons.category_rounded, size: 20),
                    ),
                    items: categories.map((c) => DropdownMenuItem(value: c.id, child: Text(c.name))).toList(),
                    onChanged: (v) => setState(() => _selectedCategoryId = v),
                  ),
                ],
              ),
            ),
            const SizedBox(height: 20),

            // Tombol Simpan ke Transaksi
            FilledButton.icon(
              onPressed: _openTransactionSheet,
              icon: const Icon(Icons.check_circle_rounded),
              label: const Text('Catat Sebagai Transaksi'),
              style: FilledButton.styleFrom(
                backgroundColor: AppColors.primary,
                minimumSize: const Size.fromHeight(52),
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
              ),
            ),
          ] else if (_textCtrl.text.isNotEmpty) ...[
            Container(
              padding: const EdgeInsets.all(20),
              decoration: BoxDecoration(
                color: AppColors.card,
                borderRadius: BorderRadius.circular(18),
                border: Border.all(color: AppColors.border),
              ),
              child: const Column(
                children: [
                  Icon(Icons.search_off_rounded, size: 36, color: AppColors.textMuted),
                  SizedBox(height: 8),
                  Text(
                    'Nominal transaksi belum dapat ditemukan dalam teks.',
                    textAlign: TextAlign.center,
                    style: TextStyle(fontSize: 13, fontWeight: FontWeight.w600, color: AppColors.textSecondary),
                  ),
                  SizedBox(height: 4),
                  Text(
                    'Pastikan teks mengandung format nominal uang (contoh: "Rp 50.000").',
                    textAlign: TextAlign.center,
                    style: TextStyle(fontSize: 11.5, color: AppColors.textMuted),
                  ),
                ],
              ),
            ),
          ],
        ],
      ),
    );
  }

  Widget _buildSampleChip(String label, String content) {
    return Padding(
      padding: const EdgeInsets.only(right: 8),
      child: ActionChip(
        label: Text(label, style: const TextStyle(fontSize: 11.5, fontWeight: FontWeight.w700)),
        backgroundColor: AppColors.card,
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(12),
          side: const BorderSide(color: AppColors.border),
        ),
        onPressed: () => _setSample(content),
      ),
    );
  }
}
