import 'dart:io';

import 'package:flutter/material.dart';
import 'package:image_picker/image_picker.dart';

import '../../models/digital_document.dart';
import '../../services/api_service.dart';
import '../../theme.dart';
import '../../utils/format.dart';
import 'document_detail_screen.dart';

class DocumentScreen extends StatefulWidget {
  const DocumentScreen({super.key});

  @override
  State<DocumentScreen> createState() => _DocumentScreenState();
}

class _DocumentScreenState extends State<DocumentScreen> {
  bool _loading = true;
  DocumentData? _data;
  String? _error;
  String _filter = 'Semua';

  List<String> get _categories => _data?.summary.categories.keys.toList() ?? const [];

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
      final data = await ApiService.instance.documents();
      if (!mounted) return;
      setState(() {
        _data = data;
        _loading = false;
      });
    } on ApiException catch (e) {
      if (!mounted) return;
      setState(() {
        _error = e.message;
        _loading = false;
      });
    } catch (_) {
      if (!mounted) return;
      setState(() {
        _error = 'Terjadi kesalahan saat memuat data.';
        _loading = false;
      });
    }
  }

  Future<void> _snack(String msg) async {
    if (!mounted) return;
    ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(msg)));
  }

  List<DigitalDocument> get _visible => (_data?.documents ?? []).where((d) {
        if (_filter == 'Semua') return true;
        if (_filter == 'Favorit') return d.isFavorite;
        return d.category == _filter;
      }).toList();

  Future<void> _openCreateSheet() async {
    final saved = await showModalBottomSheet<Map<String, dynamic>>(
      context: context,
      useRootNavigator: true,
      isScrollControlled: true,
      backgroundColor: AppColors.card,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
      ),
      builder: (ctx) => const _DocumentSheet(),
    );
    if (saved == null) return;
    try {
      await ApiService.instance.storeDocument(
        name: saved['name'] as String,
        documentType: saved['document_type'] as String,
        category: saved['category'] as String,
        documentNumber: saved['document_number'] as String?,
        issuedDate: saved['issued_date'] as String?,
        expiryDate: saved['expiry_date'] as String?,
        issuingAuthority: saved['issuing_authority'] as String?,
        ownerName: saved['owner_name'] as String?,
        notes: saved['notes'] as String?,
        photoBase64: saved['photo_base64'] as String?,
        photoBackBase64: saved['photo_back_base64'] as String?,
        storageLocation: saved['storage_location'] as String?,
        isFavorite: saved['is_favorite'] == true,
      );
      await _snack('Dokumen berhasil disimpan.');
      _load();
    } on ApiException catch (e) {
      await _snack(e.message);
    }
  }

  Future<void> _toggleFavorite(DigitalDocument d) async {
    try {
      await ApiService.instance.toggleDocumentFavorite(d.id);
      _load();
    } on ApiException catch (e) {
      await _snack(e.message);
    }
  }

  Future<void> _openDetail(DigitalDocument d) async {
    await Navigator.of(context).push(
      MaterialPageRoute(builder: (_) => DocumentDetailScreen(documentId: d.id)),
    );
    _load();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.bg,
      appBar: AppBar(
        title: const Text('Dokumen Digital'),
        actions: [
          IconButton(
            onPressed: _openCreateSheet,
            tooltip: 'Tambah dokumen',
            icon: const Icon(Icons.add_circle_outline),
          ),
        ],
      ),
      body: _loading
          ? const Center(child: CircularProgressIndicator())
          : _error != null && _data == null
              ? Center(
                  child: Column(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Icon(Icons.cloud_off_rounded, size: 44, color: AppColors.textMuted.withValues(alpha: 0.7)),
                      const SizedBox(height: 12),
                      Padding(
                        padding: const EdgeInsets.symmetric(horizontal: 32),
                        child: Text(_error!,
                            textAlign: TextAlign.center,
                            style: const TextStyle(fontSize: 13, color: AppColors.textSecondary)),
                      ),
                      const SizedBox(height: 14),
                      OutlinedButton.icon(
                        onPressed: _load,
                        icon: const Icon(Icons.refresh, size: 18),
                        label: const Text('Coba Lagi'),
                      ),
                    ],
                  ),
                )
              : RefreshIndicator(
                  onRefresh: _load,
                  child: ListView(
                    padding: const EdgeInsets.fromLTRB(14, 6, 14, 110),
                    children: [
                      _buildHero(),
                      if (_data!.expiring.isNotEmpty) _buildExpiring(),
                      _buildFilterChips(),
                      _buildList(),
                    ],
                  ),
                ),
    );
  }

  Widget _buildHero() {
    final s = _data!.summary;
    return Container(
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        gradient: const LinearGradient(
          colors: [Color(0xFF475569), Color(0xFF64748B), Color(0xFF94A3B8)],
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
        ),
        borderRadius: BorderRadius.circular(24),
        boxShadow: const [
          BoxShadow(color: Color(0x4764748B), blurRadius: 24, offset: Offset(0, 10)),
        ],
      ),
      child: Stack(
        children: [
          Positioned(
            right: -5,
            bottom: -20,
            child: Text('🗃️',
                style: TextStyle(fontSize: 90, color: Colors.white.withValues(alpha: 0.12))),
          ),
          Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
                decoration: BoxDecoration(
                  color: Colors.white.withValues(alpha: 0.2),
                  borderRadius: BorderRadius.circular(20),
                ),
                child: const Text('🗂️ Arsip Digital',
                    style: TextStyle(fontSize: 10.5, fontWeight: FontWeight.w800, color: Colors.white)),
              ),
              const SizedBox(height: 10),
              Text('${s.totalDocs} dokumen',
                  style: const TextStyle(fontSize: 26, fontWeight: FontWeight.w900, letterSpacing: -0.5, color: Colors.white)),
              const SizedBox(height: 4),
              Text(
                '${s.favorites} favorit • ${s.expired} kadaluarsa',
                style: TextStyle(fontSize: 12.5, color: Colors.white.withValues(alpha: 0.9)),
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildExpiring() {
    return Container(
      margin: const EdgeInsets.only(top: 16),
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: const Color(0xFFFFFBEB),
        borderRadius: BorderRadius.circular(18),
        border: Border.all(color: const Color(0xFFFDE68A)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Row(
            children: [
              Text('⚠️', style: TextStyle(fontSize: 16)),
              SizedBox(width: 8),
              Text('Perlu Perhatian',
                  style: TextStyle(fontSize: 13.5, fontWeight: FontWeight.w800, color: AppColors.textPrimary)),
            ],
          ),
          const SizedBox(height: 4),
          ..._data!.expiring.map((d) => InkWell(
                onTap: () => _openDetail(d),
                borderRadius: BorderRadius.circular(10),
                child: Padding(
                  padding: const EdgeInsets.symmetric(vertical: 8),
                  child: Row(
                    children: [
                      const Icon(Icons.warning_amber_rounded, size: 18, color: Color(0xFFD97706)),
                      const SizedBox(width: 10),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(d.name,
                                style: const TextStyle(fontSize: 12.5, fontWeight: FontWeight.w800, color: AppColors.textPrimary)),
                            Text(
                              d.isExpired
                                  ? 'Sudah kadaluarsa • ${Fmt.dateDay(d.expiryDate ?? '')}'
                                  : 'Akan kadaluarsa ${Fmt.dateDay(d.expiryDate ?? '')}',
                              style: const TextStyle(fontSize: 10.5, color: AppColors.textSecondary),
                            ),
                          ],
                        ),
                      ),
                      const Icon(Icons.chevron_right_rounded, size: 18, color: AppColors.textMuted),
                    ],
                  ),
                ),
              )),
        ],
      ),
    );
  }

  Widget _buildFilterChips() {
    final chips = <String>['Semua', 'Favorit', ..._categories];
    return SizedBox(
      height: 40,
      child: ListView.separated(
        scrollDirection: Axis.horizontal,
        padding: const EdgeInsets.only(top: 14),
        itemCount: chips.length,
        separatorBuilder: (_, __) => const SizedBox(width: 8),
        itemBuilder: (_, i) {
          final c = chips[i];
          final selected = c == _filter;
          return ChoiceChip(
            label: Text(c),
            selected: selected,
            showCheckmark: false,
            labelStyle: TextStyle(
              fontSize: 12,
              fontWeight: FontWeight.w800,
              color: selected ? Colors.white : AppColors.textSecondary,
            ),
            backgroundColor: AppColors.card,
            selectedColor: AppColors.primary,
            side: BorderSide(color: selected ? Colors.transparent : AppColors.border),
            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
            onSelected: (_) => setState(() => _filter = c),
          );
        },
      ),
    );
  }

  Widget _buildList() {
    final docs = _visible;
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Padding(
          padding: const EdgeInsets.fromLTRB(4, 16, 4, 8),
          child: Text('Semua Dokumen (${docs.length})',
              style: const TextStyle(fontSize: 14, fontWeight: FontWeight.w800, color: AppColors.textPrimary)),
        ),
        if (docs.isEmpty)
          Container(
            padding: const EdgeInsets.symmetric(vertical: 30, horizontal: 16),
            decoration: BoxDecoration(
              color: AppColors.card,
              borderRadius: BorderRadius.circular(18),
              border: Border.all(color: AppColors.border),
            ),
            child: const Column(
              children: [
                Icon(Icons.folder_open_outlined, size: 40, color: AppColors.textMuted),
                SizedBox(height: 10),
                Text('Belum ada dokumen.',
                    style: TextStyle(fontSize: 13.5, fontWeight: FontWeight.w800, color: AppColors.textPrimary)),
                SizedBox(height: 4),
                Text('Klik ikon + di kanan atas untuk menyimpan dokumen.',
                    textAlign: TextAlign.center,
                    style: TextStyle(fontSize: 12, color: AppColors.textSecondary)),
              ],
            ),
          )
        else
          ...docs.map((d) => _DocumentTile(
                document: d,
                onTap: () => _openDetail(d),
                onToggleFavorite: () => _toggleFavorite(d),
              )),
      ],
    );
  }
}

IconData _categoryIcon(String category) => switch (category) {
      'Identitas' => Icons.credit_card_rounded,
      'Kendaraan' => Icons.directions_car_rounded,
      'Properti' => Icons.home_work_rounded,
      'Keuangan' => Icons.account_balance_wallet_rounded,
      'Kesehatan' => Icons.medical_services_rounded,
      'Pendidikan' => Icons.school_rounded,
      _ => Icons.folder_rounded,
    };

Color _categoryColor(String category) => switch (category) {
      'Identitas' => const Color(0xFF7C3AED),
      'Kendaraan' => const Color(0xFF2563EB),
      'Properti' => const Color(0xFFD97706),
      'Keuangan' => const Color(0xFF059669),
      'Kesehatan' => const Color(0xFFE11D48),
      'Pendidikan' => const Color(0xFF0284C7),
      _ => const Color(0xFF64748B),
    };

class _DocumentTile extends StatelessWidget {
  final DigitalDocument document;
  final VoidCallback onTap;
  final VoidCallback onToggleFavorite;
  const _DocumentTile({required this.document, required this.onTap, required this.onToggleFavorite});

  @override
  Widget build(BuildContext context) {
    final d = document;
    final expired = d.isExpired;
    final expiring = d.isExpiringSoon;
    final (Color bg, Color fg, String label) = expired
        ? (const Color(0xFFFEE2E2), const Color(0xFFB91C1C), 'Kadaluarsa')
        : expiring
            ? (const Color(0xFFFEF3C7), const Color(0xFFB45309), 'Segera')
            : (const Color(0xFFD1FAE5), const Color(0xFF047857), 'Aktif');
    final color = _categoryColor(d.category);
    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(18),
      child: Container(
        margin: const EdgeInsets.only(bottom: 10),
        padding: const EdgeInsets.all(14),
        decoration: BoxDecoration(
          color: AppColors.card,
          borderRadius: BorderRadius.circular(18),
          border: Border.all(color: AppColors.border),
        ),
        child: Row(
          children: [
            Container(
              width: 44,
              height: 44,
              decoration: BoxDecoration(color: color.withValues(alpha: 0.12), borderRadius: BorderRadius.circular(13)),
              alignment: Alignment.center,
              child: Icon(_categoryIcon(d.category), size: 22, color: color),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    children: [
                      Flexible(
                        child: Text(d.name,
                            overflow: TextOverflow.ellipsis,
                            style: const TextStyle(fontSize: 13.5, fontWeight: FontWeight.w800, color: AppColors.textPrimary)),
                      ),
                      const SizedBox(width: 6),
                      IconButton(
                        onPressed: onToggleFavorite,
                        visualDensity: VisualDensity.compact,
                        padding: EdgeInsets.zero,
                        constraints: const BoxConstraints(),
                        iconSize: 18,
                        icon: Icon(
                          d.isFavorite ? Icons.star_rounded : Icons.star_border_rounded,
                          color: d.isFavorite ? const Color(0xFFF59E0B) : AppColors.textMuted,
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 2),
                  Text('${d.documentType} • ${d.category}',
                      style: const TextStyle(fontSize: 10.5, color: AppColors.textSecondary)),
                  const SizedBox(height: 4),
                  Row(
                    children: [
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                        decoration: BoxDecoration(color: bg, borderRadius: BorderRadius.circular(20)),
                        child: Text(label,
                            style: TextStyle(fontSize: 9.5, fontWeight: FontWeight.w800, color: fg)),
                      ),
                      if (d.expiryDate != null && d.expiryDate!.isNotEmpty) ...[
                        const SizedBox(width: 6),
                        Text('Berlaku s.d. ${Fmt.dateDay(d.expiryDate!)}',
                            style: const TextStyle(fontSize: 10.5, color: AppColors.textSecondary)),
                      ],
                    ],
                  ),
                ],
              ),
            ),
            const Icon(Icons.chevron_right_rounded, size: 20, color: AppColors.textMuted),
          ],
        ),
      ),
    );
  }
}

class _DocumentSheet extends StatefulWidget {
  const _DocumentSheet();

  @override
  State<_DocumentSheet> createState() => _DocumentSheetState();
}

class _DocumentSheetState extends State<_DocumentSheet> {
  final _nameCtrl = TextEditingController();
  final _numberCtrl = TextEditingController();
  final _authorityCtrl = TextEditingController();
  final _ownerCtrl = TextEditingController();
  final _notesCtrl = TextEditingController();
  final _locationCtrl = TextEditingController();
  String _category = 'Identitas';
  String _documentType = 'KTP';
  DateTime? _issuedDate;
  DateTime? _expiryDate;
  XFile? _photo;
  XFile? _photoBack;
  bool _isFavorite = false;

  static const _categories = ['Identitas', 'Kendaraan', 'Properti', 'Keuangan', 'Kesehatan', 'Pendidikan', 'Lainnya'];
  static const _types = ['KTP', 'SIM', 'STNK', 'BPKB', 'KK', 'Sertifikat Rumah', 'Polis Asuransi', 'Rekening', 'Akta', 'Lainnya'];

  @override
  void dispose() {
    _nameCtrl.dispose();
    _numberCtrl.dispose();
    _authorityCtrl.dispose();
    _ownerCtrl.dispose();
    _notesCtrl.dispose();
    _locationCtrl.dispose();
    super.dispose();
  }

  String? _fmtDate(DateTime d) =>
      '${d.year.toString().padLeft(4, '0')}-${d.month.toString().padLeft(2, '0')}-${d.day.toString().padLeft(2, '0')}';

  Future<void> _pickDate({required bool issued}) async {
    final now = DateTime.now();
    final initial = issued ? (_issuedDate ?? now) : (_expiryDate ?? now);
    final picked = await showDatePicker(
      context: context,
      initialDate: initial,
      firstDate: DateTime(now.year - 50),
      lastDate: DateTime(now.year + 20),
      helpText: issued ? 'Tanggal terbit' : 'Tanggal kadaluarsa',
    );
    if (picked == null) return;
    setState(() => issued ? _issuedDate = picked : _expiryDate = picked);
  }

  Future<void> _pickPhoto({required bool back}) async {
    final picked = await ImagePicker().pickImage(source: ImageSource.gallery, maxWidth: 1600, imageQuality: 80);
    if (picked == null) return;
    setState(() => back ? _photoBack = picked : _photo = picked);
  }

  Widget _photoPicker({required bool back}) {
    final file = back ? _photoBack : _photo;
    final label = back ? 'Foto Belakang' : 'Foto Depan';
    return Container(
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: AppColors.bg,
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: AppColors.border),
      ),
      child: file == null
          ? InkWell(
              onTap: () => _pickPhoto(back: back),
              borderRadius: BorderRadius.circular(10),
              child: Row(
                children: [
                  Icon(Icons.add_a_photo_outlined, size: 20, color: AppColors.textMuted),
                  const SizedBox(width: 10),
                  Text(label,
                      style: const TextStyle(fontSize: 12.5, fontWeight: FontWeight.w700, color: AppColors.textSecondary)),
                ],
              ),
            )
          : Row(
              children: [
                Image.file(
                  File(file.path),
                  width: 56,
                  height: 56,
                  fit: BoxFit.cover,
                ),
                const SizedBox(width: 10),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(label,
                          style: const TextStyle(fontSize: 12.5, fontWeight: FontWeight.w800, color: AppColors.textPrimary)),
                      const SizedBox(height: 2),
                      Text(file.name,
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                          style: const TextStyle(fontSize: 10.5, color: AppColors.textSecondary)),
                    ],
                  ),
                ),
                IconButton(
                  onPressed: () => setState(() => back ? _photoBack = null : _photo = null),
                  icon: const Icon(Icons.close, size: 18, color: AppColors.expense),
                ),
              ],
            ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: EdgeInsets.only(bottom: MediaQuery.of(context).viewInsets.bottom),
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
            const Text('Simpan Dokumen',
                textAlign: TextAlign.center,
                style: TextStyle(fontSize: 17, fontWeight: FontWeight.w800, color: AppColors.textPrimary)),
            const SizedBox(height: 16),
            DropdownButtonFormField<String>(
              initialValue: _category,
              decoration: const InputDecoration(labelText: 'KATEGORI'),
              items: _categories.map((c) => DropdownMenuItem(value: c, child: Text(c))).toList(),
              onChanged: (v) => setState(() => _category = v!),
            ),
            const SizedBox(height: 14),
            TextField(
              controller: _nameCtrl,
              decoration: const InputDecoration(labelText: 'NAMA DOKUMEN', hintText: 'KTP Budi Santoso'),
            ),
            const SizedBox(height: 14),
            DropdownButtonFormField<String>(
              initialValue: _documentType,
              decoration: const InputDecoration(labelText: 'JENIS DOKUMEN'),
              items: _types.map((t) => DropdownMenuItem(value: t, child: Text(t))).toList(),
              onChanged: (v) => setState(() => _documentType = v!),
            ),
            const SizedBox(height: 14),
            TextField(
              controller: _numberCtrl,
              decoration: const InputDecoration(labelText: 'NOMOR DOKUMEN (OPSIONAL)'),
            ),
            const SizedBox(height: 14),
            TextField(
              controller: _authorityCtrl,
              decoration: const InputDecoration(labelText: 'PENERBIT (OPSIONAL)'),
            ),
            const SizedBox(height: 14),
            TextField(
              controller: _ownerCtrl,
              decoration: const InputDecoration(labelText: 'ATAS NAMA (OPSIONAL)'),
            ),
            const SizedBox(height: 14),
            Row(
              children: [
                Expanded(
                  child: InkWell(
                    onTap: () => _pickDate(issued: true),
                    borderRadius: BorderRadius.circular(12),
                    child: InputDecorator(
                      decoration: const InputDecoration(
                          labelText: 'TERBIT',
                          contentPadding: EdgeInsets.symmetric(horizontal: 12, vertical: 6)),
                      child: Text(
                        _issuedDate == null
                            ? 'Tanggal'
                            : '${_issuedDate!.day} ${Fmt.monthLabel(_fmtDate(_issuedDate!)!)}',
                        style: const TextStyle(fontSize: 12.5, color: AppColors.textPrimary),
                      ),
                    ),
                  ),
                ),
                const SizedBox(width: 10),
                Expanded(
                  child: InkWell(
                    onTap: () => _pickDate(issued: false),
                    borderRadius: BorderRadius.circular(12),
                    child: InputDecorator(
                      decoration: const InputDecoration(
                          labelText: 'KADALUARSA',
                          contentPadding: EdgeInsets.symmetric(horizontal: 12, vertical: 6)),
                      child: Text(
                        _expiryDate == null
                            ? 'Tanggal'
                            : '${_expiryDate!.day} ${Fmt.monthLabel(_fmtDate(_expiryDate!)!)}',
                        style: const TextStyle(fontSize: 12.5, color: AppColors.textPrimary),
                      ),
                    ),
                  ),
                ),
              ],
            ),
            const SizedBox(height: 14),
            TextField(
              controller: _locationCtrl,
              decoration: const InputDecoration(labelText: 'LOKASI PENYIMPANAN FISIK (OPSIONAL)', hintText: 'Laci meja kerja'),
            ),
            const SizedBox(height: 14),
            TextField(
              controller: _notesCtrl,
              maxLines: 2,
              decoration: const InputDecoration(labelText: 'CATATAN (OPSIONAL)'),
            ),
            const SizedBox(height: 14),
            _photoPicker(back: false),
            const SizedBox(height: 10),
            _photoPicker(back: true),
            const SizedBox(height: 6),
            SwitchListTile(
              value: _isFavorite,
              onChanged: (v) => setState(() => _isFavorite = v),
              contentPadding: EdgeInsets.zero,
              activeTrackColor: const Color(0xFFF59E0B),
              title: const Text('⭐ Tandai sebagai favorit',
                  style: TextStyle(fontSize: 12.5, fontWeight: FontWeight.w700)),
            ),
            const SizedBox(height: 16),
            FilledButton(
              onPressed: () async {
                final name = _nameCtrl.text.trim();
                if (name.isEmpty) {
                  ScaffoldMessenger.of(context).showSnackBar(
                    const SnackBar(content: Text('Nama dokumen wajib diisi.')),
                  );
                  return;
                }
                String? front;
                String? back;
                try {
                  if (_photo != null) front = await ApiService.instance.base64FromFile(_photo!.path);
                  if (_photoBack != null) back = await ApiService.instance.base64FromFile(_photoBack!.path);
                } catch (_) {
                  ScaffoldMessenger.of(context).showSnackBar(
                    const SnackBar(content: Text('Gagal membaca foto, coba lagi.')),
                  );
                  return;
                }
                if (!mounted) return;
                Navigator.pop(context, {
                  'name': name,
                  'document_type': _documentType,
                  'category': _category,
                  'document_number': _numberCtrl.text.trim(),
                  'issued_date': _issuedDate != null ? _fmtDate(_issuedDate!) : null,
                  'expiry_date': _expiryDate != null ? _fmtDate(_expiryDate!) : null,
                  'issuing_authority': _authorityCtrl.text.trim(),
                  'owner_name': _ownerCtrl.text.trim(),
                  'notes': _notesCtrl.text.trim(),
                  'photo_base64': front,
                  'photo_back_base64': back,
                  'storage_location': _locationCtrl.text.trim(),
                  'is_favorite': _isFavorite,
                });
              },
              child: const Text('Simpan'),
            ),
          ],
        ),
      ),
    );
  }
}