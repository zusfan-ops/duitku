import 'package:flutter/material.dart';

import '../../models/vehicle.dart';
import '../../services/api_service.dart';
import '../../theme.dart';
import '../../utils/format.dart';

class VehicleScreen extends StatefulWidget {
  const VehicleScreen({super.key});

  @override
  State<VehicleScreen> createState() => _VehicleScreenState();
}

class _VehicleScreenState extends State<VehicleScreen> {
  bool _loading = true;
  List<Vehicle> _vehicles = [];
  List<VehicleLog> _recentLogs = [];
  String _symbol = 'Rp';

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() => _loading = true);
    try {
      final res = await Future.wait([
        ApiService.instance.vehicles(),
        ApiService.instance.vehicleLogs(),
        ApiService.instance.settings(),
      ]);
      if (!mounted) return;
      setState(() {
        _vehicles = (res[0]['vehicles'] as List<dynamic>? ?? [])
            .map((e) => Vehicle.fromJson(e as Map<String, dynamic>))
            .toList();
        _recentLogs = (res[1]['logs'] as List<dynamic>? ?? [])
            .map((e) => VehicleLog.fromJson(e as Map<String, dynamic>))
            .toList();
        _symbol = res[2]['symbol']?.toString() ?? 'Rp';
        _loading = false;
      });
    } catch (_) {
      if (mounted) setState(() => _loading = false);
    }
  }

  String _typeIcon(String type) => switch (type) {
        'mobil' => '🚗',
        'truk' => '🚚',
        'lainnya' => '🚜',
        _ => '🏍️',
      };

  String _logTypeLabel(String type) => switch (type) {
        'ganti_oli' => '🛢️ Ganti Oli',
        'service_rutin' => '🔧 Servis Rutin',
        'pajak_tahunan' => '📑 Pajak PKB',
        'pajak_5tahunan' => '📑 Pajak 5 Tahun',
        'ganti_ban' => '🚗 Ganti Ban/Part',
        'bbm' => '⛽ BBM',
        _ => '🛠️ Perawatan',
      };

  Future<void> _showAddVehicleSheet([Vehicle? editV]) async {
    final isEdit = editV != null;
    String type = editV?.type ?? 'motor';
    final nameCtrl = TextEditingController(text: editV?.name ?? '');
    final plateCtrl = TextEditingController(text: editV?.licensePlate ?? '');
    final brandCtrl = TextEditingController(text: editV?.brand ?? '');
    final yearCtrl = TextEditingController(text: editV?.modelYear ?? '');
    final odoCtrl = TextEditingController(
      text: editV != null && editV.odometer > 0 ? Fmt.money0(editV.odometer.toDouble()) : '',
    );
    DateTime? taxAnnual = editV?.taxAnnualDate != null && editV!.taxAnnualDate!.isNotEmpty
        ? DateTime.tryParse(editV.taxAnnualDate!)
        : null;
    DateTime? tax5Year = editV?.tax5yearDate != null && editV!.tax5yearDate!.isNotEmpty
        ? DateTime.tryParse(editV.tax5yearDate!)
        : null;

    final saved = await showModalBottomSheet<bool>(
      context: context,
      isScrollControlled: true,
      backgroundColor: AppColors.card,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
      ),
      builder: (ctx) => StatefulBuilder(
        builder: (ctx, setSS) => Padding(
          padding: EdgeInsets.only(bottom: MediaQuery.of(ctx).viewInsets.bottom),
          child: SingleChildScrollView(
            padding: const EdgeInsets.fromLTRB(20, 16, 20, 28),
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
                const SizedBox(height: 12),
                Text(isEdit ? 'Edit Kendaraan' : 'Tambah Kendaraan Baru',
                    textAlign: TextAlign.center,
                    style: const TextStyle(fontSize: 17, fontWeight: FontWeight.w800, color: AppColors.textPrimary)),
                const SizedBox(height: 16),
                Row(
                  children: [
                    Expanded(child: _vTypeBtn('Motor', 'motor', '🏍️', type, () => setSS(() => type = 'motor'))),
                    const SizedBox(width: 8),
                    Expanded(child: _vTypeBtn('Mobil', 'mobil', '🚗', type, () => setSS(() => type = 'mobil'))),
                    const SizedBox(width: 8),
                    Expanded(child: _vTypeBtn('Lainnya', 'lainnya', '🚚', type, () => setSS(() => type = 'lainnya'))),
                  ],
                ),
                const SizedBox(height: 14),
                TextField(controller: nameCtrl, decoration: const InputDecoration(labelText: 'NAMA KENDARAAN', hintText: 'cth. Vario 160 Harian')),
                const SizedBox(height: 14),
                TextField(controller: plateCtrl, textCapitalization: TextCapitalization.characters, decoration: const InputDecoration(labelText: 'PLAT NOMOR', hintText: 'cth. H 1234 AB')),
                const SizedBox(height: 14),
                Row(
                  children: [
                    Expanded(child: TextField(controller: brandCtrl, decoration: const InputDecoration(labelText: 'MERK', hintText: 'Honda / Toyota'))),
                    const SizedBox(width: 10),
                    Expanded(child: TextField(controller: yearCtrl, keyboardType: TextInputType.number, decoration: const InputDecoration(labelText: 'TAHUN', hintText: '2022'))),
                  ],
                ),
                const SizedBox(height: 14),
                TextField(
                  controller: odoCtrl,
                  keyboardType: TextInputType.number,
                  decoration: const InputDecoration(labelText: 'ODOMETER SAAT INI (KM)', suffixText: 'KM'),
                ),
                const SizedBox(height: 14),
                ListTile(
                  contentPadding: EdgeInsets.zero,
                  title: const Text('PAJAK TAHUNAN (PKB)', style: TextStyle(fontSize: 12, color: AppColors.textMuted, fontWeight: FontWeight.w600)),
                  subtitle: Text(
                    taxAnnual != null ? Fmt.dateDay(taxAnnual!.toIso8601String().substring(0, 10)) : 'Belum diatur',
                    style: const TextStyle(fontSize: 14, fontWeight: FontWeight.w700),
                  ),
                  trailing: const Icon(Icons.calendar_today_outlined, size: 18, color: AppColors.primary),
                  onTap: () async {
                    final p = await showDatePicker(
                      context: ctx,
                      initialDate: taxAnnual ?? DateTime.now().add(const Duration(days: 90)),
                      firstDate: DateTime(2020),
                      lastDate: DateTime(2035),
                    );
                    if (p != null) setSS(() => taxAnnual = p);
                  },
                ),
                ListTile(
                  contentPadding: EdgeInsets.zero,
                  title: const Text('PAJAK 5 TAHUNAN (GANTI PLAT)', style: TextStyle(fontSize: 12, color: AppColors.textMuted, fontWeight: FontWeight.w600)),
                  subtitle: Text(
                    tax5Year != null ? Fmt.dateDay(tax5Year!.toIso8601String().substring(0, 10)) : 'Belum diatur',
                    style: const TextStyle(fontSize: 14, fontWeight: FontWeight.w700),
                  ),
                  trailing: const Icon(Icons.calendar_today_outlined, size: 18, color: AppColors.primary),
                  onTap: () async {
                    final p = await showDatePicker(
                      context: ctx,
                      initialDate: tax5Year ?? DateTime.now().add(const Duration(days: 365)),
                      firstDate: DateTime(2020),
                      lastDate: DateTime(2035),
                    );
                    if (p != null) setSS(() => tax5Year = p);
                  },
                ),
                const SizedBox(height: 20),
                FilledButton(
                  onPressed: () async {
                    final name = nameCtrl.text.trim();
                    if (name.isEmpty) return;
                    final odo = int.tryParse(Fmt.parseAmount(odoCtrl.text)) ?? 0;
                    try {
                      await ApiService.instance.storeVehicle(
                        id: editV?.id,
                        name: name,
                        type: type,
                        licensePlate: plateCtrl.text.trim().toUpperCase(),
                        brand: brandCtrl.text.trim(),
                        modelYear: yearCtrl.text.trim(),
                        odometer: odo,
                        taxAnnualDate: taxAnnual?.toIso8601String().substring(0, 10),
                        tax5yearDate: tax5Year?.toIso8601String().substring(0, 10),
                      );
                      if (ctx.mounted) Navigator.pop(ctx, true);
                    } on ApiException catch (e) {
                      if (ctx.mounted) ScaffoldMessenger.of(ctx).showSnackBar(SnackBar(content: Text(e.message)));
                    }
                  },
                  child: const Text('Simpan Kendaraan'),
                ),
              ],
            ),
          ),
        ),
      ),
    );

    if (saved == true) _load();
  }

  Widget _vTypeBtn(String label, String val, String emoji, String current, VoidCallback onTap) {
    final sel = val == current;
    return GestureDetector(
      onTap: onTap,
      child: Container(
        padding: const EdgeInsets.symmetric(vertical: 8),
        decoration: BoxDecoration(
          color: sel ? AppColors.primary : AppColors.bg,
          borderRadius: BorderRadius.circular(10),
          border: Border.all(color: sel ? AppColors.primary : AppColors.border),
        ),
        child: Column(
          children: [
            Text(emoji, style: const TextStyle(fontSize: 18)),
            const SizedBox(height: 2),
            Text(label, style: TextStyle(fontSize: 11, fontWeight: FontWeight.w700, color: sel ? Colors.white : AppColors.textPrimary)),
          ],
        ),
      ),
    );
  }

  Future<void> _showAddLogSheet([Vehicle? preselected]) async {
    if (_vehicles.isEmpty) return;
    int vehicleId = preselected?.id ?? _vehicles.first.id;
    String type = 'ganti_oli';
    final titleCtrl = TextEditingController();
    final costCtrl = TextEditingController();
    final kmCtrl = TextEditingController(
      text: preselected != null && preselected.odometer > 0 ? Fmt.money0(preselected.odometer.toDouble()) : '',
    );
    final nextKmCtrl = TextEditingController();
    final workshopCtrl = TextEditingController();
    final notesCtrl = TextEditingController();
    final date = DateTime.now();

    final saved = await showModalBottomSheet<bool>(
      context: context,
      isScrollControlled: true,
      backgroundColor: AppColors.card,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
      ),
      builder: (ctx) => StatefulBuilder(
        builder: (ctx, setSS) => Padding(
          padding: EdgeInsets.only(bottom: MediaQuery.of(ctx).viewInsets.bottom),
          child: SingleChildScrollView(
            padding: const EdgeInsets.fromLTRB(20, 16, 20, 28),
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
                const SizedBox(height: 12),
                const Text('🔧 Catat Perawatan / Servis',
                    textAlign: TextAlign.center,
                    style: TextStyle(fontSize: 17, fontWeight: FontWeight.w800, color: AppColors.textPrimary)),
                const SizedBox(height: 16),
                DropdownButtonFormField<int>(
                  initialValue: vehicleId,
                  decoration: const InputDecoration(labelText: 'PILIH KENDARAAN'),
                  items: _vehicles
                      .map((v) => DropdownMenuItem(
                            value: v.id,
                            child: Text('${v.name} (${v.licensePlate ?? '-'})'),
                          ))
                      .toList(),
                  onChanged: (v) {
                    if (v != null) {
                      setSS(() => vehicleId = v);
                      final selected = _vehicles.firstWhere((x) => x.id == v);
                      if (selected.odometer > 0) {
                        kmCtrl.text = Fmt.money0(selected.odometer.toDouble());
                      }
                    }
                  },
                ),
                const SizedBox(height: 14),
                DropdownButtonFormField<String>(
                  initialValue: type,
                  decoration: const InputDecoration(labelText: 'JENIS KEGIATAN'),
                  items: const [
                    DropdownMenuItem(value: 'ganti_oli', child: Text('🛢️ Ganti Oli Mesin / Gardan')),
                    DropdownMenuItem(value: 'service_rutin', child: Text('🔧 Servis Berkala / Tune Up')),
                    DropdownMenuItem(value: 'pajak_tahunan', child: Text('📑 Bayar Pajak Tahunan (PKB)')),
                    DropdownMenuItem(value: 'pajak_5tahunan', child: Text('📑 Bayar Pajak 5 Tahun (Plat)')),
                    DropdownMenuItem(value: 'ganti_ban', child: Text('🚗 Ganti Ban / Aki / Sparepart')),
                    DropdownMenuItem(value: 'bbm', child: Text('⛽ Pengisian BBM')),
                    DropdownMenuItem(value: 'perbaikan', child: Text('🛠️ Perbaikan / Reparasi')),
                    DropdownMenuItem(value: 'lainnya', child: Text('📦 Lain-lain')),
                  ],
                  onChanged: (v) => setSS(() => type = v ?? 'ganti_oli'),
                ),
                const SizedBox(height: 14),
                TextField(controller: titleCtrl, decoration: const InputDecoration(labelText: 'DESKRIPSI / KEGIATAN', hintText: 'cth. Oli Shell 10W-40 + Filter')),
                const SizedBox(height: 14),
                TextField(controller: costCtrl, keyboardType: TextInputType.number, decoration: InputDecoration(labelText: 'BIAYA', prefixText: '$_symbol ')),
                const SizedBox(height: 14),
                Row(
                  children: [
                    Expanded(child: TextField(controller: kmCtrl, keyboardType: TextInputType.number, decoration: const InputDecoration(labelText: 'KM SAAT INI', suffixText: 'KM'))),
                    const SizedBox(width: 10),
                    Expanded(child: TextField(controller: nextKmCtrl, keyboardType: TextInputType.number, decoration: const InputDecoration(labelText: 'TARGET KM BERIKUTNYA', suffixText: 'KM'))),
                  ],
                ),
                const SizedBox(height: 14),
                TextField(controller: workshopCtrl, decoration: const InputDecoration(labelText: 'BENGKEL / LOKASI (OPSIONAL)', hintText: 'Ahass / Auto2000')),
                const SizedBox(height: 14),
                TextField(controller: notesCtrl, decoration: const InputDecoration(labelText: 'CATATAN (OPSIONAL)')),
                const SizedBox(height: 20),
                FilledButton(
                  onPressed: () async {
                    final title = titleCtrl.text.trim();
                    final cost = double.tryParse(Fmt.parseAmount(costCtrl.text)) ?? 0;
                    final km = int.tryParse(Fmt.parseAmount(kmCtrl.text));
                    final nextKm = int.tryParse(Fmt.parseAmount(nextKmCtrl.text));
                    if (title.isEmpty) return;

                    try {
                      await ApiService.instance.storeVehicleLog(
                        vehicleId: vehicleId,
                        type: type,
                        title: title,
                        cost: cost,
                        km: km,
                        nextKm: nextKm,
                        date: date.toIso8601String().substring(0, 10),
                        workshop: workshopCtrl.text.trim(),
                        notes: notesCtrl.text.trim(),
                      );
                      if (ctx.mounted) Navigator.pop(ctx, true);
                    } on ApiException catch (e) {
                      if (ctx.mounted) ScaffoldMessenger.of(ctx).showSnackBar(SnackBar(content: Text(e.message)));
                    }
                  },
                  child: const Text('Simpan Catatan Servis'),
                ),
              ],
            ),
          ),
        ),
      ),
    );

    if (saved == true) _load();
  }

  Future<void> _viewVehicleDetail(Vehicle v) async {
    await showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: AppColors.card,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
      ),
      builder: (ctx) => DraggableScrollableSheet(
        initialChildSize: 0.75,
        minChildSize: 0.5,
        maxChildSize: 0.95,
        expand: false,
        builder: (ctx, scrollCtrl) => FutureBuilder<Map<String, dynamic>>(
          future: ApiService.instance.vehicleDetail(v.id),
          builder: (ctx, snap) {
            if (snap.connectionState == ConnectionState.waiting) {
              return const Center(child: CircularProgressIndicator());
            }
            final logs = (snap.data?['logs'] as List<dynamic>? ?? [])
                .map((e) => VehicleLog.fromJson(e as Map<String, dynamic>))
                .toList();

            return ListView(
              controller: scrollCtrl,
              padding: const EdgeInsets.fromLTRB(20, 16, 20, 32),
              children: [
                Center(
                  child: Container(
                    width: 40,
                    height: 4,
                    decoration: BoxDecoration(color: AppColors.border, borderRadius: BorderRadius.circular(2)),
                  ),
                ),
                const SizedBox(height: 12),
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(v.name, style: const TextStyle(fontSize: 18, fontWeight: FontWeight.w800, color: AppColors.textPrimary)),
                          Text('${v.brand ?? ''} · ${v.licensePlate ?? '-'}', style: const TextStyle(fontSize: 12, color: AppColors.textMuted)),
                        ],
                      ),
                    ),
                    IconButton(
                      icon: const Icon(Icons.delete_outline, color: AppColors.expense),
                      onPressed: () async {
                        final ok = await showDialog<bool>(
                          context: context,
                          builder: (c) => AlertDialog(
                            title: const Text('Hapus kendaraan?'),
                            content: Text('Kendaraan ${v.name} beserta seluruh lognya akan dihapus.'),
                            actions: [
                              TextButton(onPressed: () => Navigator.pop(c, false), child: const Text('Batal')),
                              TextButton(
                                onPressed: () => Navigator.pop(c, true),
                                style: TextButton.styleFrom(foregroundColor: AppColors.expense),
                                child: const Text('Hapus'),
                              ),
                            ],
                          ),
                        );
                        if (ok == true) {
                          await ApiService.instance.deleteVehicle(v.id);
                          if (ctx.mounted) Navigator.pop(ctx);
                          _load();
                        }
                      },
                    ),
                  ],
                ),
                const SizedBox(height: 16),
                Container(
                  padding: const EdgeInsets.all(14),
                  decoration: BoxDecoration(
                    color: AppColors.bg,
                    borderRadius: BorderRadius.circular(14),
                  ),
                  child: Row(
                    mainAxisAlignment: MainAxisAlignment.spaceAround,
                    children: [
                      _statCol('ODOMETER', '${Fmt.money0(v.odometer.toDouble())} KM'),
                      _statCol('TOTAL BIAYA', Fmt.money(v.totalExpense, symbol: _symbol), color: AppColors.expense),
                      _statCol('TOTAL SERVIS', '${logs.length} kali'),
                    ],
                  ),
                ),
                const SizedBox(height: 16),
                ElevatedButton.icon(
                  onPressed: () {
                    Navigator.pop(ctx);
                    _showAddLogSheet(v);
                  },
                  icon: const Icon(Icons.add, size: 18),
                  label: const Text('Catat Servis / Oli'),
                ),
                const SizedBox(height: 16),
                const Text('RIWAYAT PERAWATAN', style: TextStyle(fontSize: 11, fontWeight: FontWeight.w700, color: AppColors.textMuted)),
                const SizedBox(height: 8),
                if (logs.isEmpty)
                  const Padding(
                    padding: EdgeInsets.all(24),
                    child: Center(child: Text('Belum ada riwayat servis untuk kendaraan ini.', style: TextStyle(color: AppColors.textMuted))),
                  )
                else
                  ...logs.map((l) => Container(
                        margin: const EdgeInsets.only(bottom: 8),
                        padding: const EdgeInsets.all(12),
                        decoration: BoxDecoration(
                          color: AppColors.card,
                          borderRadius: BorderRadius.circular(12),
                          border: Border.all(color: AppColors.border),
                        ),
                        child: Row(
                          children: [
                            Expanded(
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Text(l.title, style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w700)),
                                  const SizedBox(height: 2),
                                  Text(
                                    '${_logTypeLabel(l.type)} · ${Fmt.dateDay(l.date)}${l.km != null ? ' · ${Fmt.money0(l.km!.toDouble())} KM' : ''}',
                                    style: const TextStyle(fontSize: 11, color: AppColors.textMuted),
                                  ),
                                  if (l.nextKm != null)
                                    Text('Target Servis: ${Fmt.money0(l.nextKm!.toDouble())} KM',
                                        style: const TextStyle(fontSize: 11, fontWeight: FontWeight.w700, color: AppColors.primary)),
                                ],
                              ),
                            ),
                            Text(
                              '- ${Fmt.money(l.cost, symbol: _symbol)}',
                              style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w800, color: AppColors.expense),
                            ),
                          ],
                        ),
                      )),
              ],
            );
          },
        ),
      ),
    );
  }

  Widget _statCol(String label, String val, {Color? color}) {
    return Column(
      children: [
        Text(label, style: const TextStyle(fontSize: 10, fontWeight: FontWeight.w700, color: AppColors.textMuted)),
        const SizedBox(height: 3),
        Text(val, style: TextStyle(fontSize: 14, fontWeight: FontWeight.w800, color: color ?? AppColors.textPrimary)),
      ],
    );
  }

  @override
  Widget build(BuildContext context) {
    final taxAlerts = <(String name, String plate, String type, int days)>[];
    for (final v in _vehicles) {
      if (v.taxAnnualDate != null && v.taxAnnualDate!.isNotEmpty) {
        final d = DateTime.tryParse(v.taxAnnualDate!);
        if (d != null) {
          final diff = d.difference(DateTime.now()).inDays + 1;
          if (diff <= 30) {
            taxAlerts.add((v.name, v.licensePlate ?? '', 'Pajak Tahunan (PKB)', diff));
          }
        }
      }
      if (v.tax5yearDate != null && v.tax5yearDate!.isNotEmpty) {
        final d = DateTime.tryParse(v.tax5yearDate!);
        if (d != null) {
          final diff = d.difference(DateTime.now()).inDays + 1;
          if (diff <= 30) {
            taxAlerts.add((v.name, v.licensePlate ?? '', 'Pajak 5 Tahun (Ganti Plat)', diff));
          }
        }
      }
    }

    return Scaffold(
      backgroundColor: AppColors.bg,
      appBar: AppBar(
        title: const Text('Kendaraan & Servis'),
      ),
      floatingActionButton: FloatingActionButton.extended(
        onPressed: () => _showAddVehicleSheet(),
        backgroundColor: AppColors.primary,
        foregroundColor: Colors.white,
        icon: const Icon(Icons.add),
        label: const Text('Kendaraan', style: TextStyle(fontWeight: FontWeight.w700)),
      ),
      body: _loading
          ? const Center(child: CircularProgressIndicator())
          : RefreshIndicator(
              onRefresh: _load,
              child: ListView(
                padding: const EdgeInsets.fromLTRB(16, 12, 16, 100),
                children: [
                  if (taxAlerts.isNotEmpty) ...[
                    Container(
                      padding: const EdgeInsets.all(14),
                      decoration: BoxDecoration(
                        color: const Color(0xFFFEF2F2),
                        border: Border.all(color: const Color(0xFFFCA5A5)),
                        borderRadius: BorderRadius.circular(14),
                      ),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          const Row(
                            children: [
                              Text('⚠️', style: TextStyle(fontSize: 16)),
                              SizedBox(width: 6),
                              Text('PERINGATAN JATUH TEMPO PAJAK',
                                  style: TextStyle(fontSize: 12, fontWeight: FontWeight.w800, color: Color(0xFFDC2626))),
                            ],
                          ),
                          const SizedBox(height: 8),
                          ...taxAlerts.map((a) => Padding(
                                padding: const EdgeInsets.only(bottom: 4),
                                child: Row(
                                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                  children: [
                                    Text('${a.$1} (${a.$2}) — ${a.$3}',
                                        style: const TextStyle(fontSize: 11.5, fontWeight: FontWeight.w600, color: Color(0xFF991B1B))),
                                    Text(a.$4 <= 0 ? 'Hari Ini / Lewat' : '${a.$4} hari lagi',
                                        style: const TextStyle(fontSize: 11, fontWeight: FontWeight.w800, color: Color(0xFFDC2626))),
                                  ],
                                ),
                              )),
                        ],
                      ),
                    ),
                    const SizedBox(height: 16),
                  ],
                  if (_vehicles.isNotEmpty)
                    Padding(
                      padding: const EdgeInsets.only(bottom: 16),
                      child: Row(
                        children: [
                          Expanded(
                            child: OutlinedButton.icon(
                              onPressed: () => _showAddLogSheet(),
                              icon: const Icon(Icons.build_outlined, size: 16),
                              label: const Text('Catat Servis / Oli'),
                            ),
                          ),
                        ],
                      ),
                    ),
                  if (_vehicles.isEmpty)
                    const Padding(
                      padding: EdgeInsets.symmetric(vertical: 60),
                      child: Center(
                        child: Column(
                          children: [
                            Text('🏍️', style: TextStyle(fontSize: 56)),
                            SizedBox(height: 12),
                            Text('Belum ada kendaraan',
                                style: TextStyle(fontSize: 16, fontWeight: FontWeight.w700, color: AppColors.textPrimary)),
                            SizedBox(height: 6),
                            Padding(
                              padding: EdgeInsets.symmetric(horizontal: 40),
                              child: Text('Tambahkan motor atau mobil Anda untuk memantau oli, servis berkala, dan pajaknya.',
                                  textAlign: TextAlign.center, style: TextStyle(fontSize: 13, color: AppColors.textMuted)),
                            ),
                          ],
                        ),
                      ),
                    )
                  else
                    ..._vehicles.map((v) => Container(
                          margin: const EdgeInsets.only(bottom: 12),
                          padding: const EdgeInsets.all(16),
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
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Container(
                                    width: 46,
                                    height: 46,
                                    decoration: BoxDecoration(
                                      color: AppColors.bg,
                                      borderRadius: BorderRadius.circular(14),
                                    ),
                                    child: Center(
                                      child: Text(_typeIcon(v.type), style: const TextStyle(fontSize: 24)),
                                    ),
                                  ),
                                  const SizedBox(width: 12),
                                  Expanded(
                                    child: Column(
                                      crossAxisAlignment: CrossAxisAlignment.start,
                                      children: [
                                        Row(
                                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                          children: [
                                            Text(v.name,
                                                style: const TextStyle(fontSize: 15, fontWeight: FontWeight.w800, color: AppColors.textPrimary)),
                                            if (v.licensePlate != null && v.licensePlate!.isNotEmpty)
                                              Container(
                                                padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                                                decoration: BoxDecoration(
                                                  color: AppColors.bg,
                                                  borderRadius: BorderRadius.circular(6),
                                                  border: Border.all(color: AppColors.border),
                                                ),
                                                child: Text(v.licensePlate!,
                                                    style: const TextStyle(fontSize: 10.5, fontWeight: FontWeight.w800)),
                                              ),
                                          ],
                                        ),
                                        const SizedBox(height: 2),
                                        Text(
                                          '${v.brand ?? ''}${v.modelYear != null ? ' · ${v.modelYear}' : ''} · ${Fmt.money0(v.odometer.toDouble())} KM',
                                          style: const TextStyle(fontSize: 12, color: AppColors.textMuted),
                                        ),
                                      ],
                                    ),
                                  ),
                                ],
                              ),
                              const SizedBox(height: 12),
                              Container(
                                padding: const EdgeInsets.all(10),
                                decoration: BoxDecoration(
                                  color: AppColors.bg,
                                  borderRadius: BorderRadius.circular(12),
                                ),
                                child: Row(
                                  children: [
                                    Expanded(
                                      child: Column(
                                        crossAxisAlignment: CrossAxisAlignment.start,
                                        children: [
                                          const Text('🛢️ Oli Terakhir', style: TextStyle(fontSize: 10.5, color: AppColors.textMuted)),
                                          Text(
                                            v.lastOilDate != null ? Fmt.dateDay(v.lastOilDate!) : '-',
                                            style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w700),
                                          ),
                                        ],
                                      ),
                                    ),
                                    Expanded(
                                      child: Column(
                                        crossAxisAlignment: CrossAxisAlignment.start,
                                        children: [
                                          const Text('📑 Pajak PKB', style: TextStyle(fontSize: 10.5, color: AppColors.textMuted)),
                                          Text(
                                            v.taxAnnualDate != null ? Fmt.dateDay(v.taxAnnualDate!) : '-',
                                            style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w700),
                                          ),
                                        ],
                                      ),
                                    ),
                                  ],
                                ),
                              ),
                              const SizedBox(height: 12),
                              Row(
                                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                children: [
                                  Text('Biaya: ${Fmt.money(v.totalExpense, symbol: _symbol)}',
                                      style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w700)),
                                  Row(
                                    children: [
                                      TextButton(
                                        onPressed: () => _showAddLogSheet(v),
                                        child: const Text('+ Catat Log'),
                                      ),
                                      ElevatedButton(
                                        onPressed: () => _viewVehicleDetail(v),
                                        style: ElevatedButton.styleFrom(
                                          padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 6),
                                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                                        ),
                                        child: const Text('Detail', style: TextStyle(fontSize: 12)),
                                      ),
                                    ],
                                  ),
                                ],
                              ),
                            ],
                          ),
                        )),
                  if (_recentLogs.isNotEmpty) ...[
                    const SizedBox(height: 12),
                    const Text('AKTIVITAS PERAWATAN TERAKHIR',
                        style: TextStyle(fontSize: 11, fontWeight: FontWeight.w700, letterSpacing: .5, color: AppColors.textMuted)),
                    const SizedBox(height: 8),
                    ..._recentLogs.take(5).map((l) => Container(
                          margin: const EdgeInsets.only(bottom: 8),
                          padding: const EdgeInsets.all(12),
                          decoration: BoxDecoration(
                            color: AppColors.card,
                            borderRadius: BorderRadius.circular(14),
                            border: Border.all(color: AppColors.border),
                          ),
                          child: Row(
                            mainAxisAlignment: MainAxisAlignment.spaceBetween,
                            children: [
                              Expanded(
                                child: Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    Text(l.title, style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w700)),
                                    const SizedBox(height: 2),
                                    Text(
                                      '${l.vehicleName ?? ''} · ${_logTypeLabel(l.type)} · ${Fmt.dateDay(l.date)}',
                                      style: const TextStyle(fontSize: 11, color: AppColors.textMuted),
                                    ),
                                  ],
                                ),
                              ),
                              Text(
                                '- ${Fmt.money(l.cost, symbol: _symbol)}',
                                style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w800, color: AppColors.expense),
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
