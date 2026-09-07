import 'package:flutter/material.dart';

import '../../config/api_config.dart';
import '../../models/digital_document.dart';
import '../../services/api_service.dart';
import '../../theme.dart';
import '../../utils/format.dart';

class DocumentDetailScreen extends StatefulWidget {
  final int documentId;
  const DocumentDetailScreen({super.key, required this.documentId});

  @override
  State<DocumentDetailScreen> createState() => _DocumentDetailScreenState();
}

class _DocumentDetailScreenState extends State<DocumentDetailScreen> {
  bool _loading = true;
  DigitalDocument? _doc;
  String? _error;

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
      final doc = await ApiService.instance.documentDetail(widget.documentId);
      if (!mounted) return;
      setState(() {
        _doc = doc;
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

  Future<void> _toggleFavorite() async {
    try {
      await ApiService.instance.toggleDocumentFavorite(widget.documentId);
      _load();
    } on ApiException catch (e) {
      await _snack(e.message);
    }
  }

  Future<void> _confirmDelete() async {
    final ok = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        backgroundColor: AppColors.card,
        title: const Text('Hapus Dokumen?',
            style: TextStyle(fontSize: 16, fontWeight: FontWeight.w800)),
        content: Text('Dokumen "${_doc?.name ?? ''}" beserta fotonya akan dihapus permanen.',
            style: const TextStyle(fontSize: 13, color: AppColors.textSecondary)),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(ctx, false),
            child: const Text('Batal'),
          ),
          TextButton(
            onPressed: () => Navigator.pop(ctx, true),
            child: const Text('Hapus', style: TextStyle(color: AppColors.expense)),
          ),
        ],
      ),
    );
    if (ok != true) return;
    try {
      await ApiService.instance.deleteDocument(widget.documentId);
      if (!mounted) return;
      Navigator.of(context).pop(true);
    } on ApiException catch (e) {
      await _snack(e.message);
    }
  }

  String? _resolveUrl(String? path) {
    if (path == null || path.isEmpty) return null;
    return ApiConfig.baseUrl + (path.startsWith('/') ? path : '/$path');
  }

  Widget _photoView({String? url, required String label}) {
    final resolved = _resolveUrl(url);
    return InkWell(
      onTap: () {
        if (resolved == null) return;
        showDialog<void>(
          context: context,
          builder: (ctx) => Dialog(
            backgroundColor: Colors.black,
            child: InteractiveViewer(
              child: Image.network(resolved, fit: BoxFit.contain),
            ),
          ),
        );
      },
      borderRadius: BorderRadius.circular(18),
      child: AspectRatio(
        aspectRatio: 1.4,
        child: ClipRRect(
          borderRadius: BorderRadius.circular(18),
          child: resolved == null
              ? Container(
                  color: const Color(0xFFF1F5F9),
                  alignment: Alignment.center,
                  child: Column(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      const Icon(Icons.image_not_supported_outlined, size: 38, color: AppColors.textMuted),
                      const SizedBox(height: 8),
                      Text(label,
                          style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w700, color: AppColors.textSecondary)),
                    ],
                  ),
                )
              : Stack(
                  fit: StackFit.expand,
                  children: [
                    Image.network(resolved, fit: BoxFit.cover, loadingBuilder: (_, child, progress) {
                      if (progress == null) return child;
                      return Container(
                        color: const Color(0xFFF1F5F9),
                        alignment: Alignment.center,
                        child: const CircularProgressIndicator(strokeWidth: 2),
                      );
                    }),
                    Positioned(
                      left: 10,
                      top: 10,
                      child: Container(
                        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                        decoration: BoxDecoration(
                          color: Colors.black.withValues(alpha: 0.55),
                          borderRadius: BorderRadius.circular(20),
                        ),
                        child: Text(label,
                            style: const TextStyle(fontSize: 10.5, fontWeight: FontWeight.w800, color: Colors.white)),
                      ),
                    ),
                  ],
                ),
        ),
      ),
    );
  }

  Widget _infoRow(IconData icon, String label, String value) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 8),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Icon(icon, size: 18, color: const Color(0xFF64748B)),
          const SizedBox(width: 10),
          SizedBox(
            width: 96,
            child: Text(label,
                style: const TextStyle(fontSize: 11.5, fontWeight: FontWeight.w800, color: AppColors.textSecondary)),
          ),
          Expanded(
            child: Text(
              value.isEmpty ? '—' : value,
              style: const TextStyle(fontSize: 12.5, fontWeight: FontWeight.w700, color: AppColors.textPrimary),
            ),
          ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.bg,
      appBar: AppBar(
        title: const Text('Dokumen'),
        actions: [
          IconButton(
            onPressed: _toggleFavorite,
            icon: Icon(
              _doc?.isFavorite == true ? Icons.star_rounded : Icons.star_border_rounded,
              color: _doc?.isFavorite == true ? const Color(0xFFF59E0B) : AppColors.textMuted,
            ),
          ),
          IconButton(
            onPressed: _confirmDelete,
            icon: const Icon(Icons.delete_outline, color: AppColors.expense),
          ),
        ],
      ),
      body: _loading
          ? const Center(child: CircularProgressIndicator())
          : _error != null && _doc == null
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
              : ListView(
                  padding: const EdgeInsets.fromLTRB(14, 6, 14, 40),
                  children: [
                    _photoView(url: _doc!.photoPath, label: 'Foto Depan'),
                    if (_doc!.photoBackPath != null) ...[
                      const SizedBox(height: 10),
                      _photoView(url: _doc!.photoBackPath, label: 'Foto Belakang'),
                    ],
                    const SizedBox(height: 16),
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
                          Row(
                            children: [
                              Expanded(
                                child: Text(_doc!.name,
                                    style: const TextStyle(fontSize: 16, fontWeight: FontWeight.w900, color: AppColors.textPrimary)),
                              ),
                              _DocStatusBadge(document: _doc!),
                            ],
                          ),
                          const SizedBox(height: 4),
                          Text('${_doc!.documentType} • ${_doc!.category}',
                              style: const TextStyle(fontSize: 12, color: AppColors.textSecondary)),
                          const Divider(height: 24, color: AppColors.border),
                          _infoRow(Icons.badge_outlined, 'Nomor', _doc!.documentNumber ?? ''),
                          _infoRow(Icons.apartment_rounded, 'Penerbit', _doc!.issuingAuthority ?? ''),
                          _infoRow(Icons.person_outline, 'Atas Nama', _doc!.ownerName ?? ''),
                          _infoRow(Icons.event_outlined, 'Terbit', _doc!.issuedDate != null ? Fmt.dateFull(_doc!.issuedDate!) : ''),
                          _infoRow(Icons.event_busy_outlined, 'Kadaluarsa',
                              _doc!.expiryDate != null ? Fmt.dateFull(_doc!.expiryDate!) : ''),
                          _infoRow(Icons.inventory_2_outlined, 'Tersimpan', _doc!.storageLocation ?? ''),
                          if (_doc!.notes != null && _doc!.notes!.isNotEmpty)
                            _infoRow(Icons.notes_rounded, 'Catatan', _doc!.notes!),
                        ],
                      ),
                    ),
                  ],
                ),
    );
  }
}

class _DocStatusBadge extends StatelessWidget {
  final DigitalDocument document;
  const _DocStatusBadge({required this.document});

  @override
  Widget build(BuildContext context) {
    final (Color bg, Color fg, String label) = document.isExpired
        ? (const Color(0xFFFEE2E2), const Color(0xFFB91C1C), 'Kadaluarsa')
        : document.isExpiringSoon
            ? (const Color(0xFFFEF3C7), const Color(0xFFB45309), 'Akan Kadaluarsa')
            : (const Color(0xFFD1FAE5), const Color(0xFF047857), 'Aktif');
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
      decoration: BoxDecoration(color: bg, borderRadius: BorderRadius.circular(20)),
      child: Text(label, style: TextStyle(fontSize: 10.5, fontWeight: FontWeight.w800, color: fg)),
    );
  }
}