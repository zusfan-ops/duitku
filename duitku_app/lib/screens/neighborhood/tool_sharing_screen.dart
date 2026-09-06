import 'package:flutter/material.dart';
import '../../models/neighborhood.dart';
import '../../services/api_service.dart';
import '../../theme.dart';
import '../../utils/format.dart';

class ToolSharingScreen extends StatefulWidget {
  const ToolSharingScreen({super.key});

  @override
  State<ToolSharingScreen> createState() => _ToolSharingScreenState();
}

class _ToolSharingScreenState extends State<ToolSharingScreen> {
  bool _loading = true;
  List<CommunityTool> _tools = [];
  List<ToolRental> _myRentals = [];
  double _toolRentalFee = 2000.0;
  double _totalKasCollected = 0.0;
  List<dynamic> _kasRecords = [];
  bool _isRtAdmin = false;

  @override
  void initState() {
    super.initState();
    _loadData();
  }

  Future<void> _loadData() async {
    setState(() => _loading = true);
    try {
      final res = await ApiService.instance.get('neighborhood/tools');
      if (res['success'] == true) {
        _tools = (res['tools'] as List? ?? [])
            .map((t) => CommunityTool.fromJson(t as Map<String, dynamic>))
            .toList();
        if (res['tool_rental_fee'] != null) {
          _toolRentalFee = (res['tool_rental_fee'] as num).toDouble();
        }
        if (res['kas_summary'] != null) {
          final ks = res['kas_summary'] as Map<String, dynamic>;
          _totalKasCollected = (ks['total_kas_collected'] as num? ?? 0).toDouble();
          _kasRecords = (ks['records'] as List? ?? []);
        }
      }

      // Periksa role user dari status RT
      final nbRes = await ApiService.instance.get('neighborhood');
      if (nbRes['success'] == true) {
        _isRtAdmin = nbRes['is_rt_admin'] == true;
      }

      final rentRes = await ApiService.instance.get('neighborhood/tools/my-rentals');
      if (rentRes['success'] == true) {
        _myRentals = (rentRes['rentals'] as List? ?? [])
            .map((r) => ToolRental.fromJson(r as Map<String, dynamic>))
            .toList();
      }
    } catch (_) {}
    if (mounted) setState(() => _loading = false);
  }

  void _showRentDialog(CommunityTool tool) {
    int days = 1;
    final noteCtrl = TextEditingController();

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: AppColors.card,
      shape: const RoundedRectangleBorder(borderRadius: BorderRadius.vertical(top: Radius.circular(24))),
      builder: (ctx) => StatefulBuilder(
        builder: (ctx, setDlgState) => Padding(
          padding: EdgeInsets.only(bottom: MediaQuery.of(ctx).viewInsets.bottom, left: 20, right: 20, top: 20),
          child: SingleChildScrollView(
            child: Column(
              mainAxisSize: MainAxisSize.min,
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [
                Center(
                  child: Container(
                    width: 40,
                    height: 4,
                    decoration: BoxDecoration(color: AppColors.border, borderRadius: BorderRadius.circular(2)),
                  ),
                ),
                const SizedBox(height: 14),
                Text('Pinjam ${tool.name}', textAlign: TextAlign.center, style: const TextStyle(fontSize: 17, fontWeight: FontWeight.w900)),
                const SizedBox(height: 16),
                Container(
                  padding: const EdgeInsets.all(14),
                  decoration: BoxDecoration(color: AppColors.bg, borderRadius: BorderRadius.circular(16), border: Border.all(color: AppColors.border)),
                  child: Column(
                    children: [
                      Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          const Text('🏛️ Kas RT (Bayar Tunai):', style: TextStyle(fontSize: 12.5, fontWeight: FontWeight.w700, color: Color(0xFF065F46))),
                          Text(Fmt.money(_toolRentalFee), style: const TextStyle(fontWeight: FontWeight.w900, color: Color(0xFF059669))),
                        ],
                      ),
                      const SizedBox(height: 6),
                      Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          const Text('👤 Sewa Pemilik:', style: TextStyle(fontSize: 12.5, color: AppColors.textSecondary)),
                          Text(tool.rentalFee > 0 ? Fmt.money(tool.rentalFee) : 'Gratis', style: const TextStyle(fontWeight: FontWeight.w800, color: AppColors.primary)),
                        ],
                      ),
                      if (tool.depositAmount > 0) ...[
                        const SizedBox(height: 6),
                        Row(
                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                          children: [
                            const Text('🛡️ Uang Jaminan (Refund):', style: TextStyle(fontSize: 12.5, color: AppColors.textSecondary)),
                            Text(Fmt.money(tool.depositAmount), style: const TextStyle(fontWeight: FontWeight.w800, color: Color(0xFFD97706))),
                          ],
                        ),
                      ],
                      const Divider(height: 16),
                      const Text(
                        '💡 Biaya Kas RT sepenuhnya masuk ke pembukuan Kas RT untuk kepentingan dan fasilitas bersama warga.',
                        style: TextStyle(fontSize: 11, color: Color(0xFF065F46), height: 1.3),
                      ),
                    ],
                  ),
                ),
                const SizedBox(height: 14),
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    const Text('Durasi Pinjam:', style: TextStyle(fontSize: 13, fontWeight: FontWeight.w800)),
                    Row(
                      children: [
                        IconButton(
                          icon: const Icon(Icons.remove_circle_outline, color: AppColors.primary),
                          onPressed: days > 1 ? () => setDlgState(() => days--) : null,
                        ),
                        Text('$days Hari', style: const TextStyle(fontWeight: FontWeight.w900, fontSize: 16)),
                        IconButton(
                          icon: const Icon(Icons.add_circle_outline, color: AppColors.primary),
                          onPressed: days < tool.maxRentDays ? () => setDlgState(() => days++) : null,
                        ),
                      ],
                    ),
                  ],
                ),
                const SizedBox(height: 10),
                TextField(
                  controller: noteCtrl,
                  decoration: const InputDecoration(labelText: 'Catatan Keperluan (Opsional)', hintText: 'Misal: Untuk pasang kanopi rumah'),
                ),
                const SizedBox(height: 20),
                ElevatedButton(
                  style: ElevatedButton.styleFrom(
                    backgroundColor: AppColors.primary,
                    foregroundColor: Colors.white,
                    padding: const EdgeInsets.symmetric(vertical: 14),
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(30)),
                  ),
                  onPressed: () async {
                    Navigator.pop(ctx);
                    try {
                      final res = await ApiService.instance.post('neighborhood/tools/rent', {
                        'tool_id': tool.id,
                        'rental_days': days,
                        'borrower_note': noteCtrl.text.trim(),
                      });
                      if (!mounted) return;
                      if (res['success'] == true) {
                        ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(res['message']?.toString() ?? 'Peminjaman diajukan.')));
                        _loadData();
                      }
                    } catch (_) {}
                  },
                  child: const Text('Ajukan Peminjaman Sekarang →', style: TextStyle(fontWeight: FontWeight.w800)),
                ),
                const SizedBox(height: 24),
              ],
            ),
          ),
        ),
      ),
    );
  }

  void _showFeeSettingDialog() {
    final feeCtrl = TextEditingController(text: _toolRentalFee.toInt().toString());

    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
        title: const Text('⚙️ Atur Tarif Kas RT', style: TextStyle(fontWeight: FontWeight.w900, fontSize: 17)),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text('Tentukan tarif kas RT per peminjaman alat:', style: TextStyle(fontSize: 12.5, color: AppColors.textSecondary)),
            const SizedBox(height: 12),
            TextField(
              controller: feeCtrl,
              keyboardType: TextInputType.number,
              decoration: const InputDecoration(
                labelText: 'Tarif Kas RT (Rp)',
                prefixText: 'Rp ',
                hintText: '2000',
              ),
            ),
            const SizedBox(height: 8),
            const Text('Default Rp 2.000. Uang diterima tunai oleh RT dan masuk pembukuan kas.', style: TextStyle(fontSize: 11, color: AppColors.textMuted)),
          ],
        ),
        actions: [
          TextButton(onPressed: () => Navigator.pop(ctx), child: const Text('Batal')),
          ElevatedButton(
            style: ElevatedButton.styleFrom(backgroundColor: AppColors.primary, foregroundColor: Colors.white),
            onPressed: () async {
              Navigator.pop(ctx);
              final newFee = double.tryParse(feeCtrl.text.replaceAll('.', '').replaceAll(',', '')) ?? 2000.0;
              try {
                final res = await ApiService.instance.post('neighborhood/tools/fee-setting', {
                  'tool_rental_fee': newFee,
                });
                if (!mounted) return;
                ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(res['message']?.toString() ?? 'Tarif diperbarui.')));
                _loadData();
              } catch (_) {}
            },
            child: const Text('Simpan'),
          ),
        ],
      ),
    );
  }

  void _showKasHistoryDialog() {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: AppColors.card,
      shape: const RoundedRectangleBorder(borderRadius: BorderRadius.vertical(top: Radius.circular(24))),
      builder: (ctx) => Padding(
        padding: const EdgeInsets.all(20),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            Center(
              child: Container(
                width: 40,
                height: 4,
                decoration: BoxDecoration(color: AppColors.border, borderRadius: BorderRadius.circular(2)),
              ),
            ),
            const SizedBox(height: 14),
            const Text('📜 Buku Kas Peminjaman Alat', textAlign: TextAlign.center, style: TextStyle(fontSize: 17, fontWeight: FontWeight.w900)),
            const SizedBox(height: 12),
            Container(
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(color: const Color(0xFFECFDF5), borderRadius: BorderRadius.circular(14), border: Border.all(color: const Color(0xFFA7F3D0))),
              child: Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  const Text('Total Kas Terkumpul:', style: TextStyle(fontSize: 12.5, fontWeight: FontWeight.w700, color: Color(0xFF065F46))),
                  Text(Fmt.money(_totalKasCollected), style: const TextStyle(fontWeight: FontWeight.w900, color: Color(0xFF059669), fontSize: 16)),
                ],
              ),
            ),
            const SizedBox(height: 14),
            if (_kasRecords.isEmpty)
              const Padding(
                padding: EdgeInsets.symmetric(vertical: 32),
                child: Center(child: Text('Belum ada data peminjaman yang diserahterimakan.', style: TextStyle(color: AppColors.textMuted, fontSize: 12.5))),
              )
            else
              ConstrainedBox(
                constraints: BoxConstraints(maxHeight: MediaQuery.of(context).size.height * 0.45),
                child: ListView.separated(
                  shrinkWrap: true,
                  itemCount: _kasRecords.length,
                  separatorBuilder: (context, index) => const Divider(height: 12),
                  itemBuilder: (ctx, i) {
                    final rec = _kasRecords[i] as Map<String, dynamic>;
                    final feeVal = (rec['rt_fee_amount'] as num? ?? _toolRentalFee).toDouble();
                    return ListTile(
                      contentPadding: EdgeInsets.zero,
                      leading: Container(
                        width: 38,
                        height: 38,
                        decoration: BoxDecoration(color: const Color(0xFFECFDF5), borderRadius: BorderRadius.circular(10)),
                        alignment: Alignment.center,
                        child: const Text('🏛️', style: TextStyle(fontSize: 18)),
                      ),
                      title: Text(rec['tool_name']?.toString() ?? 'Alat RT', style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 13.5)),
                      subtitle: Text('Peminjam: ${rec['borrower_name'] ?? 'Warga'} • ${rec['start_date'] ?? ''}', style: const TextStyle(fontSize: 11, color: AppColors.textSecondary)),
                      trailing: Text('+ ${Fmt.money(feeVal)}', style: const TextStyle(fontWeight: FontWeight.w900, color: Color(0xFF059669), fontSize: 13)),
                    );
                  },
                ),
              ),
            const SizedBox(height: 20),
          ],
        ),
      ),
    );
  }

  void _showTokenValidationDialog(bool isHandover) {
    final rentalIdCtrl = TextEditingController();
    final tokenCtrl = TextEditingController();

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: AppColors.card,
      shape: const RoundedRectangleBorder(borderRadius: BorderRadius.vertical(top: Radius.circular(24))),
      builder: (ctx) => Padding(
        padding: EdgeInsets.only(bottom: MediaQuery.of(ctx).viewInsets.bottom, left: 20, right: 20, top: 20),
        child: SingleChildScrollView(
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              Center(
                child: Container(
                  width: 40,
                  height: 4,
                  decoration: BoxDecoration(color: AppColors.border, borderRadius: BorderRadius.circular(2)),
                ),
              ),
              const SizedBox(height: 14),
              Text(isHandover ? 'Validasi Serah Terima & Terima Kas' : 'Validasi Pengembalian & Refund', textAlign: TextAlign.center, style: const TextStyle(fontSize: 17, fontWeight: FontWeight.w900)),
              const SizedBox(height: 16),
              if (isHandover)
                Container(
                  padding: const EdgeInsets.all(12),
                  margin: const EdgeInsets.only(bottom: 12),
                  decoration: BoxDecoration(color: const Color(0xFFFEF3C7), borderRadius: BorderRadius.circular(12), border: Border.all(color: const Color(0xFFFDE68A))),
                  child: Row(
                    children: [
                      const Text('💵', style: TextStyle(fontSize: 20)),
                      const SizedBox(width: 8),
                      Expanded(
                        child: Text(
                          'Pastikan menerima uang kas RT tunai sebesar ${Fmt.money(_toolRentalFee)} dari peminjam.',
                          style: const TextStyle(fontSize: 11.5, color: Color(0xFF92400E), fontWeight: FontWeight.w700),
                        ),
                      ),
                    ],
                  ),
                ),
              TextField(
                controller: rentalIdCtrl,
                keyboardType: TextInputType.number,
                decoration: const InputDecoration(labelText: 'ID Rental / Peminjaman'),
              ),
              const SizedBox(height: 10),
              TextField(
                controller: tokenCtrl,
                textCapitalization: TextCapitalization.characters,
                style: const TextStyle(fontWeight: FontWeight.w800, letterSpacing: 1.5),
                decoration: InputDecoration(labelText: isHandover ? 'Token Serah Terima (Dari HP Peminjam)' : 'Token Pengembalian (Dari HP Peminjam)'),
              ),
              const SizedBox(height: 20),
              ElevatedButton(
                style: ElevatedButton.styleFrom(
                  backgroundColor: isHandover ? const Color(0xFF2563EB) : const Color(0xFF059669),
                  foregroundColor: Colors.white,
                  padding: const EdgeInsets.symmetric(vertical: 14),
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(30)),
                ),
                onPressed: () async {
                  Navigator.pop(ctx);
                  try {
                    final path = isHandover ? 'neighborhood/tools/handover' : 'neighborhood/tools/return';
                    final body = isHandover
                        ? {'rental_id': int.tryParse(rentalIdCtrl.text) ?? 0, 'handover_token': tokenCtrl.text.trim()}
                        : {'rental_id': int.tryParse(rentalIdCtrl.text) ?? 0, 'return_token': tokenCtrl.text.trim()};

                    final res = await ApiService.instance.post(path, body);
                    if (!mounted) return;
                    ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(res['message']?.toString() ?? 'Sukses.')));
                    _loadData();
                  } catch (e) {
                    if (!mounted) return;
                    ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Error: $e')));
                  }
                },
                child: Text(isHandover ? 'Validasi & Catat Kas RT' : 'Validasi & Refund Deposit', style: const TextStyle(fontWeight: FontWeight.w800)),
              ),
              const SizedBox(height: 24),
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
        titleSpacing: 0,
        title: const Text('Pinjam Alat Bersama RT', style: TextStyle(fontWeight: FontWeight.w900, fontSize: 18)),
        actions: [
          IconButton(
            icon: const Icon(Icons.qr_code_scanner_rounded),
            tooltip: 'Validasi Token',
            onPressed: () => _showTokenValidationDialog(true),
          ),
        ],
      ),
      body: _loading
          ? const Center(child: CircularProgressIndicator())
          : RefreshIndicator(
              onRefresh: _loadData,
              child: ListView(
                padding: const EdgeInsets.fromLTRB(16, 8, 16, 100),
                children: [
                  // ── KAS RT DARI PEMINJAMAN ALAT HERO CARD ──
                  Container(
                    margin: const EdgeInsets.only(bottom: 14),
                    padding: const EdgeInsets.all(18),
                    decoration: BoxDecoration(
                      gradient: const LinearGradient(
                        colors: [Color(0xFF064E3B), Color(0xFF065F46), Color(0xFF047857)],
                        begin: Alignment.topLeft,
                        end: Alignment.bottomRight,
                      ),
                      borderRadius: BorderRadius.circular(22),
                      boxShadow: const [
                        BoxShadow(color: Color(0x59064E3B), blurRadius: 16, offset: Offset(0, 6)),
                      ],
                    ),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Row(
                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                          children: [
                            Container(
                              padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                              decoration: BoxDecoration(color: const Color(0x33FFFFFF), borderRadius: BorderRadius.circular(20)),
                              child: const Text('🏛️ Kas RT & Inventaris Bersama', style: TextStyle(color: Colors.white, fontSize: 11, fontWeight: FontWeight.w800)),
                            ),
                            Container(
                              padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                              decoration: BoxDecoration(color: const Color(0xFFFBBF24), borderRadius: BorderRadius.circular(20)),
                              child: Text('Tarif: ${Fmt.money(_toolRentalFee)}', style: const TextStyle(color: Color(0xFF78350F), fontSize: 11, fontWeight: FontWeight.w900)),
                            ),
                          ],
                        ),
                        const SizedBox(height: 12),
                        Text(Fmt.money(_totalKasCollected), style: const TextStyle(fontSize: 26, fontWeight: FontWeight.w900, color: Colors.white, letterSpacing: -0.5)),
                        const SizedBox(height: 2),
                        Text('Total Kas Masuk dari ${_kasRecords.length}x Peminjaman Alat', style: const TextStyle(fontSize: 11.5, color: Color(0xD9FFFFFF))),
                        const SizedBox(height: 14),
                        Row(
                          children: [
                            Expanded(
                              child: OutlinedButton.icon(
                                style: OutlinedButton.styleFrom(
                                  foregroundColor: Colors.white,
                                  side: const BorderSide(color: Color(0x66FFFFFF)),
                                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
                                  padding: const EdgeInsets.symmetric(vertical: 8),
                                ),
                                icon: const Icon(Icons.receipt_long_rounded, size: 16),
                                label: const Text('Buku Kas Alat', style: TextStyle(fontSize: 12, fontWeight: FontWeight.w800)),
                                onPressed: _showKasHistoryDialog,
                              ),
                            ),
                            if (_isRtAdmin) ...[
                              const SizedBox(width: 8),
                              Expanded(
                                child: ElevatedButton.icon(
                                  style: ElevatedButton.styleFrom(
                                    backgroundColor: Colors.white,
                                    foregroundColor: const Color(0xFF065F46),
                                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
                                    padding: const EdgeInsets.symmetric(vertical: 8),
                                  ),
                                  icon: const Icon(Icons.tune_rounded, size: 16),
                                  label: const Text('Atur Tarif', style: TextStyle(fontSize: 12, fontWeight: FontWeight.w800)),
                                  onPressed: _showFeeSettingDialog,
                                ),
                              ),
                            ],
                          ],
                        ),
                      ],
                    ),
                  ),

                  // ── TRANSPARENCY NOTICE ──
                  Container(
                    margin: const EdgeInsets.only(bottom: 16),
                    padding: const EdgeInsets.all(12),
                    decoration: BoxDecoration(
                      color: const Color(0xFFF0FDF4),
                      borderRadius: BorderRadius.circular(16),
                      border: Border.all(color: const Color(0xFF86EFAC)),
                    ),
                    child: Row(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        const Text('💡', style: TextStyle(fontSize: 18)),
                        const SizedBox(width: 8),
                        Expanded(
                          child: Text(
                            'Biaya administrasi pinjam alat sebesar ${Fmt.money(_toolRentalFee)} dibayarkan tunai ke Bendahara/Ketua RT dan 100% masuk ke Kas RT untuk kepentingan bersama.',
                            style: const TextStyle(fontSize: 11.5, color: Color(0xFF166534), height: 1.35),
                          ),
                        ),
                      ],
                    ),
                  ),

                  // Active Rentals Banner
                  if (_myRentals.isNotEmpty) ...[
                    const Text('PINJAMAN AKTIF SAYA', style: TextStyle(fontSize: 11, fontWeight: FontWeight.w800, color: AppColors.textMuted, letterSpacing: 0.5)),
                    const SizedBox(height: 8),
                    ..._myRentals.map((r) => Container(
                          margin: const EdgeInsets.only(bottom: 10),
                          padding: const EdgeInsets.all(16),
                          decoration: BoxDecoration(
                            color: r.status == 'borrowed' ? const Color(0xFFECFDF5) : const Color(0xFFFFFBEB),
                            borderRadius: BorderRadius.circular(18),
                            border: Border.all(color: r.status == 'borrowed' ? const Color(0xFFA7F3D0) : const Color(0xFFFDE68A)),
                            boxShadow: AppColors.cardShadow,
                          ),
                          child: Row(
                            mainAxisAlignment: MainAxisAlignment.spaceBetween,
                            children: [
                              Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Text(r.toolName, style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 14)),
                                  const SizedBox(height: 2),
                                  Text('Batas Kembali: ${r.dueDate}', style: const TextStyle(fontSize: 11.5, color: AppColors.textSecondary)),
                                  const SizedBox(height: 4),
                                  Text('🏛️ Kas RT: ${Fmt.money(r.rtFeeAmount > 0 ? r.rtFeeAmount : _toolRentalFee)} (Tunai)', style: const TextStyle(fontSize: 11, color: Color(0xFF059669), fontWeight: FontWeight.w700)),
                                ],
                              ),
                              Container(
                                padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                                decoration: BoxDecoration(
                                  color: AppColors.textPrimary,
                                  borderRadius: BorderRadius.circular(12),
                                ),
                                child: Text(
                                  r.status == 'borrowed' ? '🔑 Return: ${r.returnToken}' : '🔑 Token: ${r.handoverToken}',
                                  style: const TextStyle(color: Colors.white, fontWeight: FontWeight.w900, fontSize: 11.5, letterSpacing: 0.5),
                                ),
                              ),
                            ],
                          ),
                        )),
                    const SizedBox(height: 14),
                  ],

                  // Tool Catalog Grid
                  const Text('KATALOG ALAT RT TERSEDIA', style: TextStyle(fontSize: 11, fontWeight: FontWeight.w800, color: AppColors.textMuted, letterSpacing: 0.5)),
                  const SizedBox(height: 8),
                  if (_tools.isEmpty)
                    Container(
                      padding: const EdgeInsets.all(32),
                      decoration: BoxDecoration(
                        color: AppColors.card,
                        borderRadius: BorderRadius.circular(20),
                        border: Border.all(color: AppColors.border),
                      ),
                      child: const Center(
                        child: Column(
                          children: [
                            Text('🔨', style: TextStyle(fontSize: 40)),
                            SizedBox(height: 8),
                            Text('Belum ada alat terdaftar', style: TextStyle(fontWeight: FontWeight.w800)),
                            SizedBox(height: 4),
                            Text('Jadilah yang pertama meminjamkan alat untuk tetangga Anda!', textAlign: TextAlign.center, style: TextStyle(fontSize: 12, color: AppColors.textSecondary)),
                          ],
                        ),
                      ),
                    )
                  else
                    ..._tools.map((t) => Container(
                          margin: const EdgeInsets.only(bottom: 12),
                          padding: const EdgeInsets.all(14),
                          decoration: BoxDecoration(
                            color: AppColors.card,
                            borderRadius: BorderRadius.circular(20),
                            border: Border.all(color: AppColors.border),
                            boxShadow: AppColors.cardShadow,
                          ),
                          child: Row(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Container(
                                width: 70,
                                height: 70,
                                decoration: BoxDecoration(
                                  color: AppColors.bg,
                                  borderRadius: BorderRadius.circular(14),
                                ),
                                alignment: Alignment.center,
                                child: const Text('🔨', style: TextStyle(fontSize: 32)),
                              ),
                              const SizedBox(width: 14),
                              Expanded(
                                child: Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    Container(
                                      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                                      decoration: BoxDecoration(color: AppColors.bg, borderRadius: BorderRadius.circular(8)),
                                      child: Text(t.category, style: const TextStyle(fontSize: 10, fontWeight: FontWeight.w700, color: AppColors.textSecondary)),
                                    ),
                                    const SizedBox(height: 4),
                                    Text(t.name, style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 14.5)),
                                    const SizedBox(height: 4),
                                    Row(
                                      children: [
                                        Container(
                                          padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                                          decoration: BoxDecoration(color: const Color(0xFFECFDF5), borderRadius: BorderRadius.circular(6)),
                                          child: Text('🏛️ Kas RT: ${Fmt.money(_toolRentalFee)}', style: const TextStyle(fontSize: 11, fontWeight: FontWeight.w800, color: Color(0xFF065F46))),
                                        ),
                                      ],
                                    ),
                                  ],
                                ),
                              ),
                              ElevatedButton(
                                style: ElevatedButton.styleFrom(
                                  backgroundColor: t.status == 'available' ? AppColors.primary : AppColors.border,
                                  foregroundColor: Colors.white,
                                  padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
                                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
                                ),
                                onPressed: t.status == 'available' ? () => _showRentDialog(t) : null,
                                child: Text(t.status == 'available' ? 'Pinjam' : 'Dipinjam', style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w800)),
                              ),
                            ],
                          ),
                        )),
                ],
              ),
            ),
    );
  }
}
