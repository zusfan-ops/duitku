import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
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
  int _currentUserId = 0;

  @override
  void initState() {
    super.initState();
    _loadData();
  }

  Future<void> _loadData() async {
    setState(() => _loading = true);
    try {
      final prof = await ApiService.instance.get('profile');
      if (prof['success'] == true && prof['user'] != null) {
        _currentUserId = int.tryParse(prof['user']['id']?.toString() ?? '0') ?? 0;
      }

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
    final cutoffCtrl = TextEditingController(
      text: DateTime.now().add(const Duration(hours: 3)).toIso8601String().substring(0, 16).replaceAll('T', ' '),
    );
    bool isSubmitting = false;

    showModalBottomSheet(
      context: context,
      useRootNavigator: true,
      isScrollControlled: true,
      backgroundColor: AppColors.card,
      shape: const RoundedRectangleBorder(borderRadius: BorderRadius.vertical(top: Radius.circular(24))),
      builder: (ctx) => StatefulBuilder(
        builder: (ctx, setDlgState) => SafeArea(
          child: Padding(
            padding: EdgeInsets.only(
              bottom: MediaQuery.of(ctx).viewInsets.bottom,
              left: 20,
              right: 20,
              top: 20,
            ),
            child: SingleChildScrollView(
              physics: const BouncingScrollPhysics(),
              padding: const EdgeInsets.only(bottom: 24),
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
                  const Text('Buka Sesi Titip Belanja', textAlign: TextAlign.center, style: TextStyle(fontSize: 18, fontWeight: FontWeight.w900)),
                  const SizedBox(height: 4),
                  const Text('Mau ke pasar atau toko? Buka sesi belanja untuk membantu tetangga sekitar.', textAlign: TextAlign.center, style: TextStyle(fontSize: 12, color: AppColors.textMuted)),
                  const SizedBox(height: 18),
                  TextField(
                    controller: storeCtrl,
                    decoration: InputDecoration(
                      labelText: 'Tujuan Belanja (Pasar / Toko / Supermarket)',
                      hintText: 'Misal: Pasar Pagi Subuh / Superindo',
                      prefixIcon: const Icon(Icons.storefront_outlined, size: 20),
                      filled: true,
                      fillColor: AppColors.bg,
                      border: OutlineInputBorder(borderRadius: BorderRadius.circular(14), borderSide: const BorderSide(color: AppColors.border)),
                      enabledBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(14), borderSide: const BorderSide(color: AppColors.border)),
                    ),
                  ),
                  const SizedBox(height: 12),
                  TextField(
                    controller: descCtrl,
                    maxLines: 2,
                    decoration: InputDecoration(
                      labelText: 'Catatan / Rute Perjalanan',
                      hintText: 'Misal: Berangkat jam 06.00 pakai motor, hanya muat barang kecil-sedang.',
                      filled: true,
                      fillColor: AppColors.bg,
                      border: OutlineInputBorder(borderRadius: BorderRadius.circular(14), borderSide: const BorderSide(color: AppColors.border)),
                      enabledBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(14), borderSide: const BorderSide(color: AppColors.border)),
                    ),
                  ),
                  const SizedBox(height: 12),
                  TextField(
                    controller: cutoffCtrl,
                    decoration: InputDecoration(
                      labelText: 'Batas Akhir Warga Menitip (Cutoff Time)',
                      hintText: 'YYYY-MM-DD HH:MM',
                      prefixIcon: const Icon(Icons.alarm_outlined, size: 20),
                      filled: true,
                      fillColor: AppColors.bg,
                      border: OutlineInputBorder(borderRadius: BorderRadius.circular(14), borderSide: const BorderSide(color: AppColors.border)),
                      enabledBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(14), borderSide: const BorderSide(color: AppColors.border)),
                    ),
                  ),
                  const SizedBox(height: 20),
                  ElevatedButton(
                    style: ElevatedButton.styleFrom(
                      backgroundColor: const Color(0xFF059669),
                      foregroundColor: Colors.white,
                      padding: const EdgeInsets.symmetric(vertical: 14),
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(30)),
                      elevation: 0,
                    ),
                    onPressed: isSubmitting
                        ? null
                        : () async {
                            if (storeCtrl.text.trim().isEmpty) {
                              ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Tujuan belanja wajib diisi.')));
                              return;
                            }

                            setDlgState(() => isSubmitting = true);
                            try {
                              final res = await ApiService.instance.post('neighborhood/errands/store', {
                                'destination_store': storeCtrl.text.trim(),
                                'description': descCtrl.text.trim(),
                                'cutoff_time': cutoffCtrl.text.trim(),
                              });
                              if (!mounted) return;
                              if (ctx.mounted) Navigator.pop(ctx);
                              if (res['success'] == true) {
                                ScaffoldMessenger.of(context).showSnackBar(SnackBar(
                                  content: Text(res['message']?.toString() ?? 'Sesi belanja berhasil dibuka!'),
                                  backgroundColor: const Color(0xFF059669),
                                ));
                                _loadData();
                              } else {
                                ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(res['message']?.toString() ?? 'Gagal membuka sesi belanja.')));
                              }
                            } catch (e) {
                              if (!mounted) return;
                              setDlgState(() => isSubmitting = false);
                              ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Error: $e')));
                            }
                          },
                    child: isSubmitting
                        ? const SizedBox(height: 20, width: 20, child: CircularProgressIndicator(strokeWidth: 2.2, color: Colors.white))
                        : const Text('Buka Sesi Belanja Sekarang →', style: TextStyle(fontWeight: FontWeight.w800, fontSize: 14)),
                  ),
                ],
              ),
            ),
          ),
        ),
      ),
    );
  }

  void _showAddItemDialog(Errand errand) {
    final itemCtrl = TextEditingController();
    final qtyCtrl = TextEditingController(text: '1');
    final unitCtrl = TextEditingController(text: 'kg / pcs');
    final estCtrl = TextEditingController(text: '20000');
    final feeCtrl = TextEditingController(text: '5000');
    final notesCtrl = TextEditingController();
    bool isSubmitting = false;

    showModalBottomSheet(
      context: context,
      useRootNavigator: true,
      isScrollControlled: true,
      backgroundColor: AppColors.card,
      shape: const RoundedRectangleBorder(borderRadius: BorderRadius.vertical(top: Radius.circular(24))),
      builder: (ctx) => StatefulBuilder(
        builder: (ctx, setDlgState) => SafeArea(
          child: Padding(
            padding: EdgeInsets.only(
              bottom: MediaQuery.of(ctx).viewInsets.bottom,
              left: 20,
              right: 20,
              top: 20,
            ),
            child: SingleChildScrollView(
              physics: const BouncingScrollPhysics(),
              padding: const EdgeInsets.only(bottom: 24),
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
                  Text('Titip ke ${errand.destinationStore}', textAlign: TextAlign.center, style: const TextStyle(fontSize: 18, fontWeight: FontWeight.w900)),
                  const SizedBox(height: 4),
                  Text('Dibelanjakan oleh ${errand.organizerName}', textAlign: TextAlign.center, style: const TextStyle(fontSize: 12, color: AppColors.textMuted)),
                  const SizedBox(height: 18),
                  TextField(
                    controller: itemCtrl,
                    decoration: InputDecoration(
                      labelText: 'Nama Barang Titipan',
                      hintText: 'Misal: Telur Ayam 1 Kg / Bumbu Dapur',
                      prefixIcon: const Icon(Icons.shopping_bag_outlined, size: 20),
                      filled: true,
                      fillColor: AppColors.bg,
                      border: OutlineInputBorder(borderRadius: BorderRadius.circular(14), borderSide: const BorderSide(color: AppColors.border)),
                      enabledBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(14), borderSide: const BorderSide(color: AppColors.border)),
                    ),
                  ),
                  const SizedBox(height: 12),
                  Row(
                    children: [
                      Expanded(
                        child: TextField(
                          controller: qtyCtrl,
                          keyboardType: const TextInputType.numberWithOptions(decimal: true),
                          decoration: InputDecoration(
                            labelText: 'Jumlah',
                            filled: true,
                            fillColor: AppColors.bg,
                            border: OutlineInputBorder(borderRadius: BorderRadius.circular(14), borderSide: const BorderSide(color: AppColors.border)),
                            enabledBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(14), borderSide: const BorderSide(color: AppColors.border)),
                          ),
                        ),
                      ),
                      const SizedBox(width: 10),
                      Expanded(
                        child: TextField(
                          controller: unitCtrl,
                          decoration: InputDecoration(
                            labelText: 'Satuan',
                            hintText: 'kg / bungkus / ikat',
                            filled: true,
                            fillColor: AppColors.bg,
                            border: OutlineInputBorder(borderRadius: BorderRadius.circular(14), borderSide: const BorderSide(color: AppColors.border)),
                            enabledBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(14), borderSide: const BorderSide(color: AppColors.border)),
                          ),
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 12),
                  Row(
                    children: [
                      Expanded(
                        child: TextField(
                          controller: estCtrl,
                          keyboardType: TextInputType.number,
                          decoration: InputDecoration(
                            labelText: 'Estimasi Harga (Rp)',
                            hintText: '20000',
                            filled: true,
                            fillColor: AppColors.bg,
                            border: OutlineInputBorder(borderRadius: BorderRadius.circular(14), borderSide: const BorderSide(color: AppColors.border)),
                            enabledBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(14), borderSide: const BorderSide(color: AppColors.border)),
                          ),
                        ),
                      ),
                      const SizedBox(width: 10),
                      Expanded(
                        child: TextField(
                          controller: feeCtrl,
                          keyboardType: TextInputType.number,
                          decoration: InputDecoration(
                            labelText: 'Ongkos Jasa Titip (Rp)',
                            hintText: '5000',
                            filled: true,
                            fillColor: AppColors.bg,
                            border: OutlineInputBorder(borderRadius: BorderRadius.circular(14), borderSide: const BorderSide(color: AppColors.border)),
                            enabledBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(14), borderSide: const BorderSide(color: AppColors.border)),
                          ),
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 12),
                  TextField(
                    controller: notesCtrl,
                    maxLines: 2,
                    decoration: InputDecoration(
                      labelText: 'Catatan Khusus (Merek / Preferensi)',
                      hintText: 'Misal: Pilih yang segar, kalau habis ganti telur bebek',
                      filled: true,
                      fillColor: AppColors.bg,
                      border: OutlineInputBorder(borderRadius: BorderRadius.circular(14), borderSide: const BorderSide(color: AppColors.border)),
                      enabledBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(14), borderSide: const BorderSide(color: AppColors.border)),
                    ),
                  ),
                  const SizedBox(height: 20),
                  ElevatedButton(
                    style: ElevatedButton.styleFrom(
                      backgroundColor: const Color(0xFF059669),
                      foregroundColor: Colors.white,
                      padding: const EdgeInsets.symmetric(vertical: 14),
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(30)),
                      elevation: 0,
                    ),
                    onPressed: isSubmitting
                        ? null
                        : () async {
                            if (itemCtrl.text.trim().isEmpty) {
                              ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Nama barang wajib diisi.')));
                              return;
                            }

                            setDlgState(() => isSubmitting = true);
                            try {
                              final res = await ApiService.instance.post('neighborhood/errands/item', {
                                'errand_id': errand.id,
                                'item_name': itemCtrl.text.trim(),
                                'quantity': double.tryParse(qtyCtrl.text.trim()) ?? 1.0,
                                'unit': unitCtrl.text.trim(),
                                'estimated_price': double.tryParse(estCtrl.text.replaceAll(RegExp(r'[^0-9]'), '')) ?? 0.0,
                                'service_fee': double.tryParse(feeCtrl.text.replaceAll(RegExp(r'[^0-9]'), '')) ?? 5000.0,
                                'notes': notesCtrl.text.trim(),
                              });
                              if (!mounted) return;
                              if (ctx.mounted) Navigator.pop(ctx);
                              if (res['success'] == true) {
                                ScaffoldMessenger.of(context).showSnackBar(SnackBar(
                                  content: Text(res['message']?.toString() ?? 'Barang titipan berhasil dikirim!'),
                                  backgroundColor: const Color(0xFF059669),
                                ));
                                _loadData();
                              } else {
                                ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(res['message']?.toString() ?? 'Gagal mengirim titipan.')));
                              }
                            } catch (e) {
                              if (!mounted) return;
                              setDlgState(() => isSubmitting = false);
                              ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Error: $e')));
                            }
                          },
                    child: isSubmitting
                        ? const SizedBox(height: 20, width: 20, child: CircularProgressIndicator(strokeWidth: 2.2, color: Colors.white))
                        : const Text('Kirim Daftar Titipan →', style: TextStyle(fontWeight: FontWeight.w800, fontSize: 14)),
                  ),
                ],
              ),
            ),
          ),
        ),
      ),
    );
  }

  void _showDeliverDialog(ErrandItem item) {
    final tokenCtrl = TextEditingController();
    final actualPriceCtrl = TextEditingController(text: item.estimatedPrice.toInt().toString());
    bool isSubmitting = false;

    showModalBottomSheet(
      context: context,
      useRootNavigator: true,
      isScrollControlled: true,
      backgroundColor: AppColors.card,
      shape: const RoundedRectangleBorder(borderRadius: BorderRadius.vertical(top: Radius.circular(24))),
      builder: (ctx) => StatefulBuilder(
        builder: (ctx, setDlgState) => SafeArea(
          child: Padding(
            padding: EdgeInsets.only(
              bottom: MediaQuery.of(ctx).viewInsets.bottom,
              left: 20,
              right: 20,
              top: 20,
            ),
            child: SingleChildScrollView(
              physics: const BouncingScrollPhysics(),
              padding: const EdgeInsets.only(bottom: 24),
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
                  const Text('Konfirmasi Penyerahan Barang', textAlign: TextAlign.center, style: TextStyle(fontSize: 18, fontWeight: FontWeight.w900)),
                  const SizedBox(height: 4),
                  Text('Serahkan ${item.itemName} ke pemesan dan masukkan token verifikasi', textAlign: TextAlign.center, style: const TextStyle(fontSize: 12, color: AppColors.textMuted)),
                  const SizedBox(height: 18),
                  TextField(
                    controller: actualPriceCtrl,
                    keyboardType: TextInputType.number,
                    style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 16),
                    decoration: InputDecoration(
                      labelText: 'Total Harga Asli Belanjaan (Rp)',
                      hintText: 'Misal: 22000',
                      prefixIcon: const Icon(Icons.receipt_outlined, size: 20),
                      filled: true,
                      fillColor: AppColors.bg,
                      border: OutlineInputBorder(borderRadius: BorderRadius.circular(14), borderSide: const BorderSide(color: AppColors.border)),
                      enabledBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(14), borderSide: const BorderSide(color: AppColors.border)),
                    ),
                  ),
                  const SizedBox(height: 12),
                  TextField(
                    controller: tokenCtrl,
                    textCapitalization: TextCapitalization.characters,
                    style: const TextStyle(fontWeight: FontWeight.w900, letterSpacing: 2, fontSize: 16),
                    decoration: InputDecoration(
                      labelText: 'Token Serah Terima (6 Digit)',
                      hintText: 'Misal: TK-123456',
                      prefixIcon: const Icon(Icons.vpn_key_outlined, size: 20),
                      filled: true,
                      fillColor: AppColors.bg,
                      border: OutlineInputBorder(borderRadius: BorderRadius.circular(14), borderSide: const BorderSide(color: AppColors.border)),
                      enabledBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(14), borderSide: const BorderSide(color: AppColors.border)),
                    ),
                  ),
                  const SizedBox(height: 20),
                  ElevatedButton(
                    style: ElevatedButton.styleFrom(
                      backgroundColor: const Color(0xFF059669),
                      foregroundColor: Colors.white,
                      padding: const EdgeInsets.symmetric(vertical: 14),
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(30)),
                      elevation: 0,
                    ),
                    onPressed: isSubmitting
                        ? null
                        : () async {
                            final token = tokenCtrl.text.trim();
                            if (token.isEmpty) {
                              ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Token serah terima wajib diisi.')));
                              return;
                            }

                            final price = double.tryParse(actualPriceCtrl.text.replaceAll(RegExp(r'[^0-9]'), '')) ?? 0.0;

                            setDlgState(() => isSubmitting = true);
                            try {
                              final res = await ApiService.instance.post('neighborhood/errands/deliver', {
                                'item_id': item.id,
                                'handover_token': token,
                                'actual_price': price,
                              });
                              if (!mounted) return;
                              if (ctx.mounted) Navigator.pop(ctx);
                              if (res['success'] == true) {
                                ScaffoldMessenger.of(context).showSnackBar(SnackBar(
                                  content: Text(res['message']?.toString() ?? 'Penyerahan barang berhasil dikonfirmasi!'),
                                  backgroundColor: const Color(0xFF059669),
                                ));
                                _loadData();
                              } else {
                                ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(res['message']?.toString() ?? 'Gagal konfirmasi penyerahan.')));
                              }
                            } catch (e) {
                              if (!mounted) return;
                              setDlgState(() => isSubmitting = false);
                              ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Error: $e')));
                            }
                          },
                    child: isSubmitting
                        ? const SizedBox(height: 20, width: 20, child: CircularProgressIndicator(strokeWidth: 2.2, color: Colors.white))
                        : const Text('Konfirmasi & Selesaikan Titipan', style: TextStyle(fontWeight: FontWeight.w800, fontSize: 14)),
                  ),
                ],
              ),
            ),
          ),
        ),
      ),
    );
  }

  void _showErrandDetailSheet(Errand errand) async {
    showModalBottomSheet(
      context: context,
      useRootNavigator: true,
      isScrollControlled: true,
      backgroundColor: AppColors.card,
      shape: const RoundedRectangleBorder(borderRadius: BorderRadius.vertical(top: Radius.circular(24))),
      builder: (ctx) => FutureBuilder<Map<String, dynamic>>(
        future: ApiService.instance.get('neighborhood/errands/${errand.id}'),
        builder: (ctx, snapshot) {
          if (snapshot.connectionState == ConnectionState.waiting) {
            return const SizedBox(
              height: 300,
              child: Center(child: CircularProgressIndicator()),
            );
          }

          final errandData = snapshot.data?['errand'];
          final itemsList = (errandData?['items'] as List? ?? [])
              .map((it) => ErrandItem.fromJson(it as Map<String, dynamic>))
              .toList();

          final isOrganizer = errand.organizerUserId == _currentUserId;

          return SafeArea(
            child: ConstrainedBox(
              constraints: BoxConstraints(maxHeight: MediaQuery.of(context).size.height * 0.88),
              child: Padding(
                padding: const EdgeInsets.fromLTRB(20, 16, 20, 24),
                child: Column(
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
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(errand.destinationStore, style: const TextStyle(fontWeight: FontWeight.w900, fontSize: 18)),
                              Text('Belanja oleh: ${errand.organizerName}', style: const TextStyle(fontSize: 12, color: AppColors.textMuted)),
                            ],
                          ),
                        ),
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                          decoration: BoxDecoration(
                            color: errand.status == 'open' ? const Color(0xFFECFDF5) : Colors.grey.withValues(alpha: 0.1),
                            borderRadius: BorderRadius.circular(10),
                            border: Border.all(color: errand.status == 'open' ? const Color(0xFFA7F3D0) : Colors.grey.shade300),
                          ),
                          child: Text(
                            errand.status == 'open' ? '🟢 Terbuka' : 'Selesai',
                            style: TextStyle(
                              fontSize: 11,
                              fontWeight: FontWeight.bold,
                              color: errand.status == 'open' ? const Color(0xFF047857) : Colors.grey,
                            ),
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 12),
                    if (errand.description != null && errand.description!.isNotEmpty)
                      Container(
                        padding: const EdgeInsets.all(12),
                        decoration: BoxDecoration(color: AppColors.bg, borderRadius: BorderRadius.circular(12)),
                        child: Text(errand.description!, style: const TextStyle(fontSize: 12, color: AppColors.textSecondary)),
                      ),
                    const SizedBox(height: 16),
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        const Text('Daftar Barang Titipan Tetangga', style: TextStyle(fontWeight: FontWeight.w800, fontSize: 13.5)),
                        Text('${itemsList.length} Item', style: const TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: AppColors.primary)),
                      ],
                    ),
                    const SizedBox(height: 10),
                    Expanded(
                      child: itemsList.isEmpty
                          ? Center(
                              child: Column(
                                mainAxisAlignment: MainAxisAlignment.center,
                                children: const [
                                  Text('🛍️', style: TextStyle(fontSize: 36)),
                                  SizedBox(height: 8),
                                  Text('Belum ada tetangga yang menitip barang.', style: TextStyle(color: AppColors.textMuted, fontSize: 12)),
                                ],
                              ),
                            )
                          : ListView.builder(
                              physics: const BouncingScrollPhysics(),
                              itemCount: itemsList.length,
                              itemBuilder: (ctx, i) {
                                final item = itemsList[i];
                                final isDelivered = item.status == 'delivered';

                                return Container(
                                  margin: const EdgeInsets.only(bottom: 8),
                                  padding: const EdgeInsets.all(12),
                                  decoration: BoxDecoration(
                                    color: AppColors.bg,
                                    borderRadius: BorderRadius.circular(14),
                                    border: Border.all(color: AppColors.border),
                                  ),
                                  child: Row(
                                    crossAxisAlignment: CrossAxisAlignment.start,
                                    children: [
                                      Expanded(
                                        child: Column(
                                          crossAxisAlignment: CrossAxisAlignment.start,
                                          children: [
                                            Text(item.itemName, style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 13.5)),
                                            Text(
                                              'Pemesan: ${item.requesterName ?? "Warga"} • ${item.quantity} ${item.unit}',
                                              style: const TextStyle(fontSize: 11.5, color: AppColors.textMuted),
                                            ),
                                            Text(
                                              'Est: ${Fmt.money(item.estimatedPrice)} • Fee: ${Fmt.money(item.serviceFee)}',
                                              style: const TextStyle(fontSize: 11, fontWeight: FontWeight.bold, color: Color(0xFF059669)),
                                            ),
                                            if (item.notes != null && item.notes!.isNotEmpty)
                                              Text('Catatan: ${item.notes}', style: const TextStyle(fontSize: 10.5, fontStyle: FontStyle.italic, color: AppColors.textSecondary)),
                                          ],
                                        ),
                                      ),
                                      Column(
                                        crossAxisAlignment: CrossAxisAlignment.end,
                                        children: [
                                          Container(
                                            padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                                            decoration: BoxDecoration(
                                              color: isDelivered ? const Color(0xFFECFDF5) : Colors.orange.withValues(alpha: 0.1),
                                              borderRadius: BorderRadius.circular(8),
                                            ),
                                            child: Text(
                                              isDelivered ? '✓ Diterima' : '⌛ Proses',
                                              style: TextStyle(
                                                fontSize: 10.5,
                                                fontWeight: FontWeight.bold,
                                                color: isDelivered ? const Color(0xFF047857) : Colors.orange[800],
                                              ),
                                            ),
                                          ),
                                          if (isOrganizer && !isDelivered) ...[
                                            const SizedBox(height: 6),
                                            ElevatedButton(
                                              style: ElevatedButton.styleFrom(
                                                backgroundColor: const Color(0xFF059669),
                                                foregroundColor: Colors.white,
                                                padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                                                minimumSize: Size.zero,
                                                tapTargetSize: MaterialTapTargetSize.shrinkWrap,
                                                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                                                elevation: 0,
                                              ),
                                              onPressed: () {
                                                Navigator.pop(ctx);
                                                _showDeliverDialog(item);
                                              },
                                              child: const Text('Serahkan ✓', style: TextStyle(fontSize: 11, fontWeight: FontWeight.bold)),
                                            ),
                                          ],
                                        ],
                                      ),
                                    ],
                                  ),
                                );
                              },
                            ),
                    ),
                    const SizedBox(height: 12),
                    if (errand.status == 'open')
                      ElevatedButton(
                        style: ElevatedButton.styleFrom(
                          backgroundColor: const Color(0xFF059669),
                          foregroundColor: Colors.white,
                          padding: const EdgeInsets.symmetric(vertical: 12),
                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(24)),
                        ),
                        onPressed: () {
                          Navigator.pop(ctx);
                          _showAddItemDialog(errand);
                        },
                        child: const Text('+ Titip Barang ke Sesi Ini', style: TextStyle(fontWeight: FontWeight.w800, fontSize: 13.5)),
                      ),
                  ],
                ),
              ),
            ),
          );
        },
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
        backgroundColor: const Color(0xFF059669),
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
                  // ── Hero Banner ──
                  Container(
                    padding: const EdgeInsets.all(20),
                    decoration: BoxDecoration(
                      gradient: const LinearGradient(
                        colors: [Color(0xFF064E3B), Color(0xFF059669), Color(0xFF10B981)],
                        begin: Alignment.topLeft,
                        end: Alignment.bottomRight,
                      ),
                      borderRadius: BorderRadius.circular(24),
                      boxShadow: [
                        BoxShadow(
                          color: const Color(0xFF059669).withValues(alpha: 0.3),
                          blurRadius: 16,
                          offset: const Offset(0, 6),
                        ),
                      ],
                    ),
                    child: Row(
                      children: [
                        Container(
                          width: 52,
                          height: 52,
                          decoration: BoxDecoration(
                            color: Colors.white.withValues(alpha: 0.2),
                            borderRadius: BorderRadius.circular(16),
                          ),
                          child: const Center(child: Text('🛍️', style: TextStyle(fontSize: 26))),
                        ),
                        const SizedBox(width: 14),
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: const [
                              Text('Titip Belanja Tetangga', style: TextStyle(color: Colors.white, fontSize: 17, fontWeight: FontWeight.w900)),
                              SizedBox(height: 2),
                              Text('Saling bantu belanja pasar bareng tetangga dengan ongkir sukarela.', style: TextStyle(color: Colors.white70, fontSize: 11.5)),
                            ],
                          ),
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(height: 16),

                  // ── My Requests Section ──
                  if (_myRequests.isNotEmpty) ...[
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        const Text('TITIPAN SAYA', style: TextStyle(fontSize: 11, fontWeight: FontWeight.w800, color: AppColors.textMuted, letterSpacing: 0.5)),
                        Text('${_myRequests.length} Barang', style: const TextStyle(fontSize: 11, fontWeight: FontWeight.bold, color: Color(0xFF059669))),
                      ],
                    ),
                    const SizedBox(height: 8),
                    ..._myRequests.map((req) {
                      final isDelivered = req.status == 'delivered';
                      return Container(
                        margin: const EdgeInsets.only(bottom: 10),
                        padding: const EdgeInsets.all(14),
                        decoration: BoxDecoration(
                          color: AppColors.card,
                          borderRadius: BorderRadius.circular(18),
                          border: Border.all(color: AppColors.border),
                          boxShadow: AppColors.cardShadow,
                        ),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Row(
                              mainAxisAlignment: MainAxisAlignment.spaceBetween,
                              children: [
                                Expanded(
                                  child: Column(
                                    crossAxisAlignment: CrossAxisAlignment.start,
                                    children: [
                                      Text(req.itemName, style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 13.5)),
                                      Text('${req.quantity} ${req.unit} • Est: ${Fmt.money(req.estimatedPrice)} • Fee: ${Fmt.money(req.serviceFee)}',
                                          style: const TextStyle(fontSize: 11, color: AppColors.textMuted)),
                                    ],
                                  ),
                                ),
                                Container(
                                  padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                                  decoration: BoxDecoration(
                                    color: isDelivered ? const Color(0xFFECFDF5) : Colors.orange.withValues(alpha: 0.1),
                                    borderRadius: BorderRadius.circular(10),
                                  ),
                                  child: Text(
                                    isDelivered ? '✓ Diterima' : '⌛ Menunggu',
                                    style: TextStyle(
                                      fontSize: 11,
                                      fontWeight: FontWeight.bold,
                                      color: isDelivered ? const Color(0xFF047857) : Colors.orange[800],
                                    ),
                                  ),
                                ),
                              ],
                            ),
                            if (!isDelivered && req.handoverToken.isNotEmpty) ...[
                              const SizedBox(height: 10),
                              Container(
                                padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                                decoration: BoxDecoration(
                                  color: const Color(0xFFFEF3C7),
                                  borderRadius: BorderRadius.circular(12),
                                  border: Border.all(color: const Color(0xFFFDE68A)),
                                ),
                                child: Row(
                                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                  children: [
                                    Row(
                                      children: [
                                        const Icon(Icons.vpn_key_rounded, size: 16, color: Color(0xFF92400E)),
                                        const SizedBox(width: 6),
                                        Text('Token Serah Terima: ${req.handoverToken}',
                                            style: const TextStyle(fontWeight: FontWeight.w900, fontSize: 12, color: Color(0xFF92400E))),
                                      ],
                                    ),
                                    GestureDetector(
                                      onTap: () {
                                        Clipboard.setData(ClipboardData(text: req.handoverToken));
                                        ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Token disalin!')));
                                      },
                                      child: const Icon(Icons.copy_rounded, size: 16, color: Color(0xFF92400E)),
                                    ),
                                  ],
                                ),
                              ),
                            ],
                          ],
                        ),
                      );
                    }),
                    const SizedBox(height: 14),
                  ],

                  // ── Open Errand Runs ──
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      const Text('SESI BELANJA TERBUKA (TETANGGA)', style: TextStyle(fontSize: 11, fontWeight: FontWeight.w800, color: AppColors.textMuted, letterSpacing: 0.5)),
                      Text('${_errands.length} Sesi', style: const TextStyle(fontSize: 11, fontWeight: FontWeight.bold, color: Color(0xFF059669))),
                    ],
                  ),
                  const SizedBox(height: 8),
                  if (_errands.isEmpty)
                    Container(
                      padding: const EdgeInsets.all(32),
                      decoration: BoxDecoration(
                        color: AppColors.card,
                        borderRadius: BorderRadius.circular(20),
                        border: Border.all(color: AppColors.border),
                      ),
                      child: Column(
                        children: const [
                          Text('🛒', style: TextStyle(fontSize: 36)),
                          SizedBox(height: 8),
                          Text('Belum ada tetangga yang membuka sesi belanja.', style: TextStyle(color: AppColors.textMuted, fontSize: 12.5)),
                        ],
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
                                        color: const Color(0xFFECFDF5),
                                        borderRadius: BorderRadius.circular(12),
                                      ),
                                      child: const Text('🛍️', style: TextStyle(fontSize: 18)),
                                    ),
                                    const SizedBox(width: 10),
                                    Column(
                                      crossAxisAlignment: CrossAxisAlignment.start,
                                      children: [
                                        Text(e.destinationStore, style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 14.5)),
                                        Text('Oleh: ${e.organizerName.isNotEmpty ? e.organizerName : "Tetangga"}',
                                            style: const TextStyle(fontSize: 11.5, color: AppColors.textMuted)),
                                      ],
                                    ),
                                  ],
                                ),
                                Container(
                                  padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                                  decoration: BoxDecoration(
                                    color: isOpen ? const Color(0xFFECFDF5) : Colors.grey.withValues(alpha: 0.1),
                                    borderRadius: BorderRadius.circular(10),
                                    border: Border.all(color: isOpen ? const Color(0xFFA7F3D0) : Colors.grey.shade300),
                                  ),
                                  child: Text(
                                    isOpen ? '🟢 Terbuka' : 'Selesai',
                                    style: TextStyle(
                                      fontSize: 11,
                                      fontWeight: FontWeight.bold,
                                      color: isOpen ? const Color(0xFF047857) : Colors.grey,
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
                            Row(
                              children: [
                                Expanded(
                                  child: OutlinedButton(
                                    style: OutlinedButton.styleFrom(
                                      foregroundColor: AppColors.primary,
                                      side: const BorderSide(color: AppColors.primary),
                                      padding: const EdgeInsets.symmetric(vertical: 10),
                                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                                    ),
                                    onPressed: () => _showErrandDetailSheet(e),
                                    child: const Text('Lihat Detail', style: TextStyle(fontSize: 12, fontWeight: FontWeight.w800)),
                                  ),
                                ),
                                if (isOpen) ...[
                                  const SizedBox(width: 8),
                                  Expanded(
                                    child: ElevatedButton(
                                      style: ElevatedButton.styleFrom(
                                        backgroundColor: const Color(0xFF059669),
                                        foregroundColor: Colors.white,
                                        padding: const EdgeInsets.symmetric(vertical: 10),
                                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                                        elevation: 0,
                                      ),
                                      onPressed: () => _showAddItemDialog(e),
                                      child: const Text('Titip Barang +', style: TextStyle(fontSize: 12, fontWeight: FontWeight.w800)),
                                    ),
                                  ),
                                ],
                              ],
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
