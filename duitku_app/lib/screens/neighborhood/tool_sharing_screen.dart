import 'package:flutter/material.dart';
import '../../models/neighborhood.dart';
import '../../services/api_service.dart';

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

    showDialog(
      context: context,
      builder: (ctx) => StatefulBuilder(
        builder: (ctx, setDlgState) => AlertDialog(
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(24)),
          title: Text('Pinjam ${tool.name}', style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 16)),
          content: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                tool.rentalFee > 0 ? 'Sewa: Rp ${tool.rentalFee.toStringAsFixed(0)} / sesi' : 'Biaya Sewa: Gratis',
                style: const TextStyle(fontWeight: FontWeight.bold, color: Colors.green),
              ),
              if (tool.depositAmount > 0)
                Text('Uang Jaminan: Rp ${tool.depositAmount.toStringAsFixed(0)}', style: const TextStyle(fontSize: 12, color: Colors.grey)),
              const SizedBox(height: 12),
              const Text('Durasi Peminjaman (Hari):', style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold)),
              Row(
                children: [
                  IconButton(
                    icon: const Icon(Icons.remove_circle_outline),
                    onPressed: days > 1 ? () => setDlgState(() => days--) : null,
                  ),
                  Text('$days Hari', style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 16)),
                  IconButton(
                    icon: const Icon(Icons.add_circle_outline),
                    onPressed: days < tool.maxRentDays ? () => setDlgState(() => days++) : null,
                  ),
                ],
              ),
              TextField(
                controller: noteCtrl,
                decoration: const InputDecoration(
                  labelText: 'Catatan Keperluan',
                  hintText: 'Misal: Untuk pasang kanopi rumah',
                ),
              ),
            ],
          ),
          actions: [
            TextButton(onPressed: () => Navigator.pop(ctx), child: const Text('Batal')),
            ElevatedButton(
              style: ElevatedButton.styleFrom(
                backgroundColor: const Color(0xFF059669),
                foregroundColor: Colors.white,
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
              ),
              onPressed: () async {
                Navigator.pop(ctx);
                try {
                  final res = await ApiService.instance.post('neighborhood/tools/rent', {
                    'tool_id': tool.id,
                    'rental_days': days,
                    'borrower_note': noteCtrl.text.trim(),
                  });
                  if (res['success'] == true) {
                    ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(res['message']?.toString() ?? 'Peminjaman diajukan.')));
                    _loadData();
                  }
                } catch (_) {}
              },
              child: const Text('Ajukan Pinjam'),
            ),
          ],
        ),
      ),
    );
  }

  void _showTokenValidationDialog(bool isHandover) {
    final rentalIdCtrl = TextEditingController();
    final tokenCtrl = TextEditingController();

    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(24)),
        title: Text(isHandover ? 'Validasi Serah Terima' : 'Validasi Pengembalian', style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 16)),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            TextField(
              controller: rentalIdCtrl,
              keyboardType: TextInputType.number,
              decoration: const InputDecoration(labelText: 'ID Rental / Peminjaman'),
            ),
            TextField(
              controller: tokenCtrl,
              textCapitalization: TextCapitalization.characters,
              decoration: InputDecoration(labelText: isHandover ? 'Token Serah Terima (6 Digit)' : 'Token Pengembalian (6 Digit)'),
            ),
          ],
        ),
        actions: [
          TextButton(onPressed: () => Navigator.pop(ctx), child: const Text('Batal')),
          ElevatedButton(
            style: ElevatedButton.styleFrom(
              backgroundColor: isHandover ? const Color(0xFF2563EB) : const Color(0xFF059669),
              foregroundColor: Colors.white,
            ),
            onPressed: () async {
              Navigator.pop(ctx);
              try {
                final path = isHandover ? 'neighborhood/tools/handover' : 'neighborhood/tools/return';
                final body = isHandover
                    ? {'rental_id': int.tryParse(rentalIdCtrl.text) ?? 0, 'handover_token': tokenCtrl.text.trim()}
                    : {'rental_id': int.tryParse(rentalIdCtrl.text) ?? 0, 'return_token': tokenCtrl.text.trim()};

                final res = await ApiService.instance.post(path, body);
                ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(res['message']?.toString() ?? 'Sukses.')));
                _loadData();
              } catch (e) {
                ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Error: $e')));
              }
            },
            child: const Text('Validasi'),
          ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Pinjam Alat Warga', style: TextStyle(fontWeight: FontWeight.w800)),
        centerTitle: false,
        actions: [
          IconButton(
            icon: const Icon(Icons.qr_code_scanner),
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
                padding: const EdgeInsets.all(16),
                children: [
                  // Active Rentals
                  if (_myRentals.isNotEmpty) ...[
                    const Text('Peminjaman Saya', style: TextStyle(fontSize: 13, fontWeight: FontWeight.bold, color: Colors.grey)),
                    const SizedBox(height: 8),
                    ..._myRentals.map((r) => Container(
                      margin: const EdgeInsets.only(bottom: 8),
                      padding: const EdgeInsets.all(14),
                      decoration: BoxDecoration(
                        color: r.status == 'borrowed' ? const Color(0xFFECFDF5) : const Color(0xFFFFFBEB),
                        borderRadius: BorderRadius.circular(16),
                      ),
                      child: Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(r.toolName, style: const TextStyle(fontWeight: FontWeight.bold)),
                              Text('Jatuh tempo: ${r.dueDate}', style: const TextStyle(fontSize: 11, color: Colors.grey)),
                            ],
                          ),
                          Container(
                            padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                            decoration: BoxDecoration(
                              color: Colors.black87,
                              borderRadius: BorderRadius.circular(8),
                            ),
                            child: Text(
                              r.status == 'borrowed' ? 'Kembali: ${r.returnToken}' : 'Token: ${r.handoverToken}',
                              style: const TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 11),
                            ),
                          ),
                        ],
                      ),
                    )),
                    const SizedBox(height: 16),
                  ],

                  // Tool Catalog Grid
                  const Text('Katalog Alat Tersedia', style: TextStyle(fontSize: 13, fontWeight: FontWeight.bold, color: Colors.grey)),
                  const SizedBox(height: 8),
                  GridView.builder(
                    shrinkWrap: true,
                    physics: const NeverScrollableScrollPhysics(),
                    gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
                      crossAxisCount: 2,
                      crossAxisSpacing: 10,
                      mainAxisSpacing: 10,
                      childAspectRatio: 0.85,
                    ),
                    itemCount: _tools.length,
                    itemBuilder: (ctx, i) {
                      final t = _tools[i];
                      final isAvail = t.status == 'available';

                      return Container(
                        padding: const EdgeInsets.all(12),
                        decoration: BoxDecoration(
                          color: Colors.white,
                          borderRadius: BorderRadius.circular(18),
                          border: Border.all(color: const Color(0xFFE5E7EB)),
                        ),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                          children: [
                            Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Container(
                                  height: 60,
                                  width: double.infinity,
                                  decoration: BoxDecoration(
                                    color: const Color(0xFFF3F4F6),
                                    borderRadius: BorderRadius.circular(12),
                                  ),
                                  child: const Center(child: Text('🔨', style: TextStyle(fontSize: 28))),
                                ),
                                const SizedBox(height: 8),
                                Text(t.name, style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13), maxLines: 1, overflow: TextOverflow.ellipsis),
                                Text(t.rentalFee > 0 ? 'Sewa: Rp ${t.rentalFee.toStringAsFixed(0)}' : 'Gratis', style: const TextStyle(fontSize: 11, color: Colors.green, fontWeight: FontWeight.bold)),
                              ],
                            ),
                            SizedBox(
                              width: double.infinity,
                              child: ElevatedButton(
                                style: ElevatedButton.styleFrom(
                                  backgroundColor: isAvail ? const Color(0xFF059669) : Colors.grey,
                                  foregroundColor: Colors.white,
                                  padding: const EdgeInsets.symmetric(vertical: 4),
                                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                                ),
                                onPressed: isAvail ? () => _showRentDialog(t) : null,
                                child: Text(isAvail ? 'Pinjam' : 'Dipinjam', style: const TextStyle(fontSize: 11, fontWeight: FontWeight.bold)),
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
