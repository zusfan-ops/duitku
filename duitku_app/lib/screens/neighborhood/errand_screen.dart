import 'package:flutter/material.dart';
import '../../models/neighborhood.dart';
import '../../services/api_service.dart';

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

    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(24)),
        title: const Text('Buka Sesi Titip Belanja', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 16)),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            TextField(
              controller: storeCtrl,
              decoration: const InputDecoration(
                labelText: 'Toko / Pasar Tujuan',
                hintText: 'Misal: Pasar Pagi RW 02 / Superindo',
              ),
            ),
            const SizedBox(height: 8),
            TextField(
              controller: descCtrl,
              decoration: const InputDecoration(
                labelText: 'Catatan Rute / Batas Muatan',
                hintText: 'Misal: Berangkat jam 06.00 pakai motor',
              ),
            ),
          ],
        ),
        actions: [
          TextButton(onPressed: () => Navigator.pop(ctx), child: const Text('Batal')),
          ElevatedButton(
            style: ElevatedButton.styleFrom(
              backgroundColor: const Color(0xFF2563EB),
              foregroundColor: Colors.white,
            ),
            onPressed: () async {
              Navigator.pop(ctx);
              try {
                final res = await ApiService.instance.post('neighborhood/errands/store', {
                  'destination_store': storeCtrl.text.trim(),
                  'description': descCtrl.text.trim(),
                });
                if (res['success'] == true) {
                  ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Sesi belanja dibuka!')));
                  _loadData();
                }
              } catch (_) {}
            },
            child: const Text('Buka Sesi'),
          ),
        ],
      ),
    );
  }

  void _showAddItemDialog(Errand errand) {
    final itemCtrl = TextEditingController();
    final qtyCtrl = TextEditingController(text: '1');
    final estCtrl = TextEditingController(text: '25000');

    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(24)),
        title: Text('Titip ke ${errand.destinationStore}', style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 16)),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            TextField(
              controller: itemCtrl,
              decoration: const InputDecoration(labelText: 'Nama Barang', hintText: 'Misal: Telur 1kg / Sabun Mandi'),
            ),
            TextField(
              controller: qtyCtrl,
              keyboardType: TextInputType.number,
              decoration: const InputDecoration(labelText: 'Jumlah'),
            ),
            TextField(
              controller: estCtrl,
              keyboardType: TextInputType.number,
              decoration: const InputDecoration(labelText: 'Estimasi Harga (Rp)'),
            ),
          ],
        ),
        actions: [
          TextButton(onPressed: () => Navigator.pop(ctx), child: const Text('Batal')),
          ElevatedButton(
            style: ElevatedButton.styleFrom(
              backgroundColor: const Color(0xFF059669),
              foregroundColor: Colors.white,
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
                if (res['success'] == true) {
                  ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Titipan berhasil dikirim!')));
                  _loadData();
                }
              } catch (_) {}
            },
            child: const Text('Kirim Titipan'),
          ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Titip Belanja Tetangga', style: TextStyle(fontWeight: FontWeight.w800)),
        centerTitle: false,
        actions: [
          IconButton(
            icon: const Icon(Icons.add_shopping_cart),
            tooltip: 'Buka Sesi Belanja',
            onPressed: _showCreateErrandDialog,
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
                  // Sesi Belanja Aktif
                  const Text('Sesi Belanja Buka', style: TextStyle(fontSize: 13, fontWeight: FontWeight.bold, color: Colors.grey)),
                  const SizedBox(height: 8),
                  if (_errands.isEmpty)
                    Container(
                      padding: const EdgeInsets.all(24),
                      alignment: Alignment.center,
                      decoration: BoxDecoration(
                        color: const Color(0xFFF9FAFB),
                        borderRadius: BorderRadius.circular(16),
                      ),
                      child: const Text('Belum ada sesi belanja buka.', style: TextStyle(color: Colors.grey)),
                    )
                  else
                    ..._errands.map((e) => Container(
                      margin: const EdgeInsets.only(bottom: 10),
                      padding: const EdgeInsets.all(14),
                      decoration: BoxDecoration(
                        color: Colors.white,
                        borderRadius: BorderRadius.circular(18),
                        border: Border.all(color: const Color(0xFFE5E7EB)),
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
                                  color: const Color(0xFFEFF6FF),
                                  borderRadius: BorderRadius.circular(6),
                                ),
                                child: Text('Ke: ${e.destinationStore}', style: const TextStyle(color: Color(0xFF2563EB), fontWeight: FontWeight.bold, fontSize: 11)),
                              ),
                              const Text('BUKA', style: TextStyle(color: Colors.green, fontWeight: FontWeight.bold, fontSize: 11)),
                            ],
                          ),
                          const SizedBox(height: 8),
                          Text(e.description ?? 'Belanja kebutuhan warga', style: const TextStyle(fontWeight: FontWeight.bold)),
                          Text('Oleh: ${e.organizerName} (Rumah ${e.organizerHouse ?? "-"})', style: const TextStyle(fontSize: 11, color: Colors.grey)),
                          const SizedBox(height: 8),
                          SizedBox(
                            width: double.infinity,
                            child: ElevatedButton(
                              style: ElevatedButton.styleFrom(
                                backgroundColor: const Color(0xFF2563EB),
                                foregroundColor: Colors.white,
                                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                              ),
                              onPressed: () => _showAddItemDialog(e),
                              child: const Text('Titip Barang', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 12)),
                            ),
                          ),
                        ],
                      ),
                    )),
                  const SizedBox(height: 16),

                  // Titipan Saya
                  if (_myRequests.isNotEmpty) ...[
                    const Text('Titipan Belanja Saya', style: TextStyle(fontSize: 13, fontWeight: FontWeight.bold, color: Colors.grey)),
                    const SizedBox(height: 8),
                    ..._myRequests.map((r) => Container(
                      margin: const EdgeInsets.only(bottom: 8),
                      padding: const EdgeInsets.all(12),
                      decoration: BoxDecoration(
                        color: r.status == 'delivered' ? const Color(0xFFECFDF5) : const Color(0xFFFFFBEB),
                        borderRadius: BorderRadius.circular(16),
                      ),
                      child: Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text('${r.itemName} (${r.quantity.toStringAsFixed(0)} ${r.unit})', style: const TextStyle(fontWeight: FontWeight.bold)),
                              Text('Status: ${r.status.toUpperCase()}', style: const TextStyle(fontSize: 11, color: Colors.grey)),
                            ],
                          ),
                          Container(
                            padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                            decoration: BoxDecoration(
                              color: Colors.black87,
                              borderRadius: BorderRadius.circular(8),
                            ),
                            child: Text(
                              'Token: ${r.handoverToken}',
                              style: const TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 11),
                            ),
                          ),
                        ],
                      ),
                    )),
                  ],
                ],
              ),
            ),
    );
  }
}
