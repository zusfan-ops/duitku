import 'package:flutter/material.dart';
import '../../models/neighborhood.dart';
import '../../services/api_service.dart';
import '../../theme.dart';
import '../../utils/format.dart';

class ErrandScreen extends StatefulWidget {
  const ErrandScreen({super.key});

  @override
  State<ErrandScreen> createState() => _ErrandScreenState();
}

class _ErrandScreenState extends State<ErrandScreen> {
  bool _loading = true;
  List<Errand> _errands = [];
  List<ErrandItem> _myRequests = [];

  @override
  void initState() {
    super.initState();
    _loadData();
  }

  Future<void> _loadData() async {
    setState(() => _loading = true);
    try {
      final res = await ApiService.instance.get('neighborhood/errands');
      if (res['success'] == true) {
        _errands = (res['errands'] as List? ?? [])
            .map((e) => Errand.fromJson(e as Map<String, dynamic>))
            .toList();
      }

      final myRes = await ApiService.instance.get('neighborhood/errands/my-requests');
      if (myRes['success'] == true) {
        _myRequests = (myRes['requests'] as List? ?? [])
            .map((i) => ErrandItem.fromJson(i as Map<String, dynamic>))
            .toList();
      }
    } catch (_) {}
    if (mounted) setState(() => _loading = false);
  }

  void _showCreateErrandDialog() {
    final storeCtrl = TextEditingController();
    final descCtrl = TextEditingController();

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
              const Text('Buka Sesi Titip Belanja', textAlign: TextAlign.center, style: TextStyle(fontSize: 17, fontWeight: FontWeight.w900)),
              const SizedBox(height: 16),
              TextField(
                controller: storeCtrl,
                decoration: const InputDecoration(labelText: 'Toko / Pasar Tujuan', hintText: 'Misal: Pasar Pagi RW 02 / Superindo'),
              ),
              const SizedBox(height: 10),
              TextField(
                controller: descCtrl,
                decoration: const InputDecoration(labelText: 'Catatan Rute / Batas Muatan', hintText: 'Misal: Berangkat jam 06.00 pakai motor'),
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
                    final res = await ApiService.instance.post('neighborhood/errands/store', {
                      'destination_store': storeCtrl.text.trim(),
                      'description': descCtrl.text.trim(),
                    });
                    if (!mounted) return;
                    if (res['success'] == true) {
                      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Sesi belanja dibuka!')));
                      _loadData();
                    }
                  } catch (_) {}
                },
                child: const Text('Buka Sesi Belanja Sekarang', style: TextStyle(fontWeight: FontWeight.w800)),
              ),
              const SizedBox(height: 24),
            ],
          ),
        ),
      ),
    );
  }

  void _showAddItemDialog(Errand errand) {
    final itemCtrl = TextEditingController();
    final qtyCtrl = TextEditingController(text: '1');
    final estCtrl = TextEditingController(text: '25000');

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
              Text('Titip ke ${errand.destinationStore}', textAlign: TextAlign.center, style: const TextStyle(fontSize: 17, fontWeight: FontWeight.w900)),
              const SizedBox(height: 16),
              TextField(
                controller: itemCtrl,
                decoration: const InputDecoration(labelText: 'Nama Barang', hintText: 'Misal: Telur 1kg / Sabun Mandi'),
              ),
              const SizedBox(height: 10),
              Row(
                children: [
                  Expanded(
                    child: TextField(
                      controller: qtyCtrl,
                      keyboardType: TextInputType.number,
                      decoration: const InputDecoration(labelText: 'Jumlah'),
                    ),
                  ),
                  const SizedBox(width: 10),
                  Expanded(
                    child: TextField(
                      controller: estCtrl,
                      keyboardType: TextInputType.number,
                      decoration: const InputDecoration(labelText: 'Estimasi Harga (Rp)'),
                    ),
                  ),
                ],
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
                    final res = await ApiService.instance.post('neighborhood/errands/item', {
                      'errand_id': errand.id,
                      'item_name': itemCtrl.text.trim(),
                      'quantity': double.tryParse(qtyCtrl.text) ?? 1,
                      'estimated_price': double.tryParse(estCtrl.text) ?? 0,
                      'service_fee': 5000,
                    });
                    if (!mounted) return;
                    if (res['success'] == true) {
                      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Titipan berhasil dikirim!')));
                      _loadData();
                    }
                  } catch (_) {}
                },
                child: const Text('Kirim Daftar Titipan →', style: TextStyle(fontWeight: FontWeight.w800)),
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
        title: const Text('Titip Belanja Warga', style: TextStyle(fontWeight: FontWeight.w900, fontSize: 18)),
      ),
      floatingActionButton: FloatingActionButton.extended(
        heroTag: 'fab_errand',
        onPressed: _showCreateErrandDialog,
        backgroundColor: AppColors.primary,
        icon: const Icon(Icons.add_shopping_cart_rounded, color: Colors.white),
        label: const Text('Buka Sesi Belanja', style: TextStyle(fontWeight: FontWeight.w800, color: Colors.white)),
      ),
      body: _loading
          ? const Center(child: CircularProgressIndicator())
          : RefreshIndicator(
              onRefresh: _loadData,
              child: ListView(
                padding: const EdgeInsets.fromLTRB(16, 8, 16, 120),
                children: [
                  // My Requests Section
                  if (_myRequests.isNotEmpty) ...[
                    const Text('TITIPAN SAYA', style: TextStyle(fontSize: 11, fontWeight: FontWeight.w800, color: AppColors.textMuted, letterSpacing: 0.5)),
                    const SizedBox(height: 8),
                    ..._myRequests.map((req) => Container(
                          margin: const EdgeInsets.only(bottom: 10),
                          padding: const EdgeInsets.all(14),
                          decoration: BoxDecoration(
                            color: AppColors.card,
                            borderRadius: BorderRadius.circular(18),
                            border: Border.all(color: AppColors.border),
                            boxShadow: AppColors.cardShadow,
                          ),
                          child: Row(
                            mainAxisAlignment: MainAxisAlignment.spaceBetween,
                            children: [
                              Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Text(req.itemName, style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 13.5)),
                                  Text('Est: ${Fmt.money(req.estimatedPrice)} • Fee: ${Fmt.money(req.serviceFee)}', style: const TextStyle(fontSize: 11, color: AppColors.textMuted)),
                                ],
                              ),
                              Container(
                                padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                                decoration: BoxDecoration(
                                  color: req.status == 'delivered' ? AppColors.primary.withValues(alpha: 0.1) : Colors.orange.withValues(alpha: 0.1),
                                  borderRadius: BorderRadius.circular(10),
                                ),
                                child: Text(
                                  req.status == 'delivered' ? '✓ Diterima' : '⌛ Menunggu',
                                  style: TextStyle(
                                    fontSize: 11,
                                    fontWeight: FontWeight.bold,
                                    color: req.status == 'delivered' ? AppColors.primary : Colors.orange,
                                  ),
                                ),
                              ),
                            ],
                          ),
                        )),
                    const SizedBox(height: 14),
                  ],

                  // Open Errand Runs
                  const Text('SESI BELANJA TERBUKA DARI TETANGGA', style: TextStyle(fontSize: 11, fontWeight: FontWeight.w800, color: AppColors.textMuted, letterSpacing: 0.5)),
                  const SizedBox(height: 8),
                  if (_errands.isEmpty)
                    Container(
                      padding: const EdgeInsets.all(32),
                      decoration: BoxDecoration(
                        color: AppColors.card,
                        borderRadius: BorderRadius.circular(20),
                        border: Border.all(color: AppColors.border),
                      ),
                      child: const Center(
                        child: Text('Belum ada sesi belanja terbuka dari tetangga saat ini.', style: TextStyle(color: AppColors.textMuted, fontSize: 12.5)),
                      ),
                    )
                  else
                    ..._errands.map((e) {
                      final isOpen = e.status == 'open';
                      return Container(
                        margin: const EdgeInsets.only(bottom: 12),
                        padding: const EdgeInsets.all(16),
                        decoration: BoxDecoration(
                          color: AppColors.card,
                          borderRadius: BorderRadius.circular(20),
                          border: Border.all(color: AppColors.border),
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
                                    Container(
                                      padding: const EdgeInsets.all(8),
                                      decoration: BoxDecoration(
                                        color: const Color(0xFFEFF6FF),
                                        borderRadius: BorderRadius.circular(12),
                                      ),
                                      child: const Text('🛍️', style: TextStyle(fontSize: 18)),
                                    ),
                                    const SizedBox(width: 10),
                                    Column(
                                      crossAxisAlignment: CrossAxisAlignment.start,
                                      children: [
                                        Text(e.destinationStore, style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 14)),
                                        Text('Oleh: ${e.organizerName.isNotEmpty ? e.organizerName : "Tetangga"}', style: const TextStyle(fontSize: 11.5, color: AppColors.textMuted)),
                                      ],
                                    ),
                                  ],
                                ),
                                Container(
                                  padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                                  decoration: BoxDecoration(
                                    color: isOpen ? AppColors.primary.withValues(alpha: 0.1) : Colors.grey.withValues(alpha: 0.1),
                                    borderRadius: BorderRadius.circular(10),
                                  ),
                                  child: Text(
                                    isOpen ? '🟢 Terbuka' : 'Selesai',
                                    style: TextStyle(
                                      fontSize: 11,
                                      fontWeight: FontWeight.bold,
                                      color: isOpen ? AppColors.primary : Colors.grey,
                                    ),
                                  ),
                                ),
                              ],
                            ),
                            if (e.description != null && e.description!.isNotEmpty) ...[
                              const SizedBox(height: 10),
                              Text(e.description!, style: const TextStyle(fontSize: 12.5, color: AppColors.textSecondary)),
                            ],
                            const SizedBox(height: 14),
                            if (isOpen)
                              SizedBox(
                                width: double.infinity,
                                child: ElevatedButton.icon(
                                  style: ElevatedButton.styleFrom(
                                    backgroundColor: AppColors.primary,
                                    foregroundColor: Colors.white,
                                    padding: const EdgeInsets.symmetric(vertical: 10),
                                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                                  ),
                                  icon: const Icon(Icons.add, size: 16),
                                  label: const Text('Titip Belanjaan ke Toko Ini', style: TextStyle(fontSize: 12.5, fontWeight: FontWeight.w800)),
                                  onPressed: () => _showAddItemDialog(e),
                                ),
                              ),
                          ],
                        ),
                      );
                    }),
                ],
              ),
            ),
    );
  }
}
