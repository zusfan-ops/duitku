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
                          const Text('Biaya Sewa:', style: TextStyle(fontSize: 12.5, color: AppColors.textSecondary)),
                          Text(tool.rentalFee > 0 ? Fmt.money(tool.rentalFee) : 'Gratis', style: const TextStyle(fontWeight: FontWeight.w800, color: AppColors.primary)),
                        ],
                      ),
                      if (tool.depositAmount > 0) ...[
                        const SizedBox(height: 6),
                        Row(
                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                          children: [
                            const Text('Uang Jaminan (Refund):', style: TextStyle(fontSize: 12.5, color: AppColors.textSecondary)),
                            Text(Fmt.money(tool.depositAmount), style: const TextStyle(fontWeight: FontWeight.w800, color: Color(0xFFD97706))),
                          ],
                        ),
                      ],
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
              Text(isHandover ? 'Validasi Serah Terima' : 'Validasi Pengembalian', textAlign: TextAlign.center, style: const TextStyle(fontSize: 17, fontWeight: FontWeight.w900)),
              const SizedBox(height: 16),
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
                decoration: InputDecoration(labelText: isHandover ? 'Token Serah Terima (6 Digit)' : 'Token Pengembalian (6 Digit)'),
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
                child: const Text('Validasi Token', style: TextStyle(fontWeight: FontWeight.w800)),
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
        title: const Text('Pinjam Alat Warga', style: TextStyle(fontWeight: FontWeight.w900, fontSize: 18)),
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
                                ],
                              ),
                              Container(
                                padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                                decoration: BoxDecoration(
                                  color: AppColors.textPrimary,
                                  borderRadius: BorderRadius.circular(12),
                                ),
                                child: Text(
                                  r.status == 'borrowed' ? '🔑 Kembalikan: ${r.returnToken}' : '🔑 Token: ${r.handoverToken}',
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
                        child: Text('Belum ada alat yang didaftarkan di katalog RT.', style: TextStyle(color: AppColors.textMuted, fontSize: 12.5)),
                      ),
                    )
                  else
                    GridView.builder(
                      shrinkWrap: true,
                      physics: const NeverScrollableScrollPhysics(),
                      gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
                        crossAxisCount: 2,
                        crossAxisSpacing: 12,
                        mainAxisSpacing: 12,
                        childAspectRatio: 0.82,
                      ),
                      itemCount: _tools.length,
                      itemBuilder: (ctx, i) {
                        final t = _tools[i];
                        final isAvail = t.status == 'available';

                        return Container(
                          padding: const EdgeInsets.all(12),
                          decoration: BoxDecoration(
                            color: AppColors.card,
                            borderRadius: BorderRadius.circular(20),
                            border: Border.all(color: AppColors.border),
                            boxShadow: AppColors.cardShadow,
                          ),
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            mainAxisAlignment: MainAxisAlignment.spaceBetween,
                            children: [
                              Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Container(
                                    height: 64,
                                    width: double.infinity,
                                    decoration: BoxDecoration(
                                      color: AppColors.bg,
                                      borderRadius: BorderRadius.circular(14),
                                    ),
                                    child: const Center(child: Text('🔨', style: TextStyle(fontSize: 28))),
                                  ),
                                  const SizedBox(height: 8),
                                  Text(t.name, style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 13), maxLines: 1, overflow: TextOverflow.ellipsis),
                                  const SizedBox(height: 2),
                                  Text(
                                    t.rentalFee > 0 ? Fmt.money(t.rentalFee) : 'Gratis',
                                    style: const TextStyle(fontSize: 12, color: AppColors.primary, fontWeight: FontWeight.w800),
                                  ),
                                ],
                              ),
                              SizedBox(
                                width: double.infinity,
                                child: ElevatedButton(
                                  style: ElevatedButton.styleFrom(
                                    backgroundColor: isAvail ? AppColors.primary : AppColors.border,
                                    foregroundColor: isAvail ? Colors.white : AppColors.textMuted,
                                    elevation: 0,
                                    padding: const EdgeInsets.symmetric(vertical: 8),
                                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                                  ),
                                  onPressed: isAvail ? () => _showRentDialog(t) : null,
                                  child: Text(isAvail ? 'Pinjam' : 'Dipinjam', style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w800)),
                                ),
                              ),
                            ],
                          ),
                        );
                      },
                    ),
                ],
              ),
            ),
    );
  }
}
