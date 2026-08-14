import 'dart:io';
import 'dart:isolate';

import 'package:flutter/material.dart';
import 'package:image_picker/image_picker.dart';
import 'package:intl/intl.dart';
import 'package:path_provider/path_provider.dart';

import '../../models/barang.dart';
import '../../services/barang_store.dart';
import '../../theme.dart';

/// Menyalin file foto di background isolate agar UI tidak freeze
/// saat file berukuran besar.
Future<String> _copyPhotoToAppDir(_CopyPhotoArgs args) async {
  final photosDir = Directory(args.photosDirPath);
  if (!await photosDir.exists()) {
    await photosDir.create(recursive: true);
  }
  final file = await File(args.sourcePath).copy(args.targetPath);
  return file.path;
}

class _CopyPhotoArgs {
  final String sourcePath;
  final String photosDirPath;
  final String targetPath;

  const _CopyPhotoArgs({
    required this.sourcePath,
    required this.photosDirPath,
    required this.targetPath,
  });
}

class BarangScreen extends StatefulWidget {
  const BarangScreen({super.key});

  @override
  State<BarangScreen> createState() => _BarangScreenState();
}

class _BarangScreenState extends State<BarangScreen> {
  List<Barang> _items = [];
  bool _loading = true;
  String _search = '';

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    final items = await BarangStore.loadAll();
    if (!mounted) return;
    setState(() {
      _items = items;
      _loading = false;
    });
  }

  List<Barang> get _filtered {
    if (_search.trim().isEmpty) return _items;
    final q = _search.toLowerCase();
    return _items
        .where((b) =>
            b.name.toLowerCase().contains(q) ||
            b.location.toLowerCase().contains(q))
        .toList();
  }

  Future<void> _openForm({Barang? barang}) async {
    final saved = await Navigator.push<bool>(
      context,
      MaterialPageRoute(
        builder: (_) => BarangFormScreen(barang: barang),
      ),
    );
    if (saved == true) {
      _load();
    }
  }

  Future<void> _delete(Barang barang) async {
    final ok = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        title: const Text('Hapus barang?'),
        content: Text('"${barang.name}" akan dihapus permanen.'),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(ctx, false),
            child: const Text('Batal'),
          ),
          TextButton(
            onPressed: () => Navigator.pop(ctx, true),
            style: TextButton.styleFrom(foregroundColor: AppColors.expense),
            child: const Text('Hapus'),
          ),
        ],
      ),
    );
    if (ok != true) return;
    await _deletePhotos(barang);
    await BarangStore.delete(barang.id);
    _load();
  }

  Future<void> _deletePhotos(Barang barang) async {
    for (final path in [barang.itemPhoto, barang.locationPhoto]) {
      if (path != null) {
        try {
          final file = File(path);
          if (await file.exists()) await file.delete();
        } catch (_) {}
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.bg,
      appBar: AppBar(
        title: const Text('Manajemen Barang'),
        actions: [
          IconButton(
            onPressed: () => _openForm(),
            icon: const Icon(Icons.add),
          ),
        ],
      ),
      body: _buildBody(),
      floatingActionButton: FloatingActionButton(
        onPressed: () => _openForm(),
        backgroundColor: AppColors.primary,
        shape: const CircleBorder(),
        child: const Icon(Icons.add, color: Colors.white),
      ),
    );
  }

  Widget _buildBody() {
    if (_loading) {
      return const Center(child: CircularProgressIndicator());
    }

    return RefreshIndicator(
      onRefresh: _load,
      child: CustomScrollView(
        slivers: [
          SliverToBoxAdapter(
            child: Padding(
              padding: const EdgeInsets.fromLTRB(16, 12, 16, 8),
              child: TextField(
                onChanged: (v) => setState(() => _search = v),
                decoration: InputDecoration(
                  hintText: 'Cari nama atau lokasi barang...',
                  prefixIcon: const Icon(Icons.search),
                  filled: true,
                  fillColor: AppColors.card,
                  contentPadding: const EdgeInsets.symmetric(vertical: 12),
                  border: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(12),
                    borderSide: BorderSide.none,
                  ),
                ),
              ),
            ),
          ),
          if (_filtered.isEmpty)
            SliverFillRemaining(
              hasScrollBody: false,
              child: _EmptySearch(hasItems: _items.isNotEmpty),
            )
          else
            SliverPadding(
              padding: const EdgeInsets.fromLTRB(16, 8, 16, 100),
              sliver: SliverList.separated(
                itemCount: _filtered.length,
                separatorBuilder: (_, _) => const SizedBox(height: 10),
                itemBuilder: (context, index) {
                  final item = _filtered[index];
                  return _BarangCard(
                    barang: item,
                    onTap: () => _openForm(barang: item),
                    onDelete: () => _delete(item),
                  );
                },
              ),
            ),
        ],
      ),
    );
  }
}

class _BarangCard extends StatelessWidget {
  final Barang barang;
  final VoidCallback onTap;
  final VoidCallback onDelete;

  const _BarangCard({
    required this.barang,
    required this.onTap,
    required this.onDelete,
  });

  @override
  Widget build(BuildContext context) {
    final date = DateFormat('d MMM yyyy · HH:mm', 'id_ID').format(barang.updatedAt);
    return Container(
      decoration: BoxDecoration(
        color: AppColors.card,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: AppColors.border),
        boxShadow: AppColors.cardShadow,
      ),
      child: InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(16),
        child: Padding(
          padding: const EdgeInsets.all(12),
          child: Row(
            children: [
              _PhotoThumb(path: barang.itemPhoto, fallback: Icons.inventory_2_outlined),
              const SizedBox(width: 12),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      barang.name,
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                      style: const TextStyle(
                        fontSize: 15,
                        fontWeight: FontWeight.w800,
                        color: AppColors.textPrimary,
                      ),
                    ),
                    const SizedBox(height: 4),
                    Row(
                      children: [
                        const Icon(Icons.place_outlined, size: 14, color: AppColors.primary),
                        const SizedBox(width: 4),
                        Expanded(
                          child: Text(
                            barang.location,
                            maxLines: 1,
                            overflow: TextOverflow.ellipsis,
                            style: const TextStyle(
                              fontSize: 13,
                              color: AppColors.textSecondary,
                            ),
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 6),
                    Text(
                      date,
                      style: const TextStyle(
                        fontSize: 11,
                        color: AppColors.textMuted,
                      ),
                    ),
                  ],
                ),
              ),
              IconButton(
                onPressed: onDelete,
                icon: const Icon(Icons.delete_outline, color: AppColors.expense),
              ),
            ],
          ),
        ),
      ),
    );
  }
}

class _PhotoThumb extends StatelessWidget {
  final String? path;
  final IconData fallback;

  const _PhotoThumb({this.path, required this.fallback});

  @override
  Widget build(BuildContext context) {
    return Container(
      width: 64,
      height: 64,
      decoration: BoxDecoration(
        color: AppColors.bg,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: AppColors.border),
      ),
      clipBehavior: Clip.antiAlias,
      child: path != null
          ? Image.file(
              File(path!),
              fit: BoxFit.cover,
              errorBuilder: (_, _, _) => _fallback(),
              frameBuilder: (context, child, frame, wasSynchronouslyLoaded) {
                if (wasSynchronouslyLoaded || frame != null) return child;
                return const Center(
                  child: SizedBox(
                    width: 20,
                    height: 20,
                    child: CircularProgressIndicator(strokeWidth: 2),
                  ),
                );
              },
            )
          : _fallback(),
    );
  }

  Widget _fallback() => Center(
        child: Icon(fallback, color: AppColors.textMuted, size: 28),
      );
}

class _EmptySearch extends StatelessWidget {
  final bool hasItems;
  const _EmptySearch({required this.hasItems});

  @override
  Widget build(BuildContext context) {
    return Column(
      mainAxisAlignment: MainAxisAlignment.center,
      children: [
        Icon(
          hasItems ? Icons.search_off_outlined : Icons.inventory_2_outlined,
          size: 56,
          color: AppColors.textMuted,
        ),
        const SizedBox(height: 12),
        Text(
          hasItems ? 'Tidak ada hasil pencarian' : 'Belum ada barang tersimpan',
          style: const TextStyle(
            fontSize: 15,
            fontWeight: FontWeight.w700,
            color: AppColors.textSecondary,
          ),
        ),
        const SizedBox(height: 6),
        Text(
          hasItems
              ? 'Coba kata kunci lain'
              : 'Tekan tombol + untuk mencatat barang\ndan lokasi penyimpanannya.',
          textAlign: TextAlign.center,
          style: const TextStyle(fontSize: 13, color: AppColors.textMuted),
        ),
      ],
    );
  }
}

class BarangFormScreen extends StatefulWidget {
  final Barang? barang;

  const BarangFormScreen({super.key, this.barang});

  @override
  State<BarangFormScreen> createState() => _BarangFormScreenState();
}

class _BarangFormScreenState extends State<BarangFormScreen> {
  final _nameCtrl = TextEditingController();
  final _locationCtrl = TextEditingController();
  String? _itemPhoto;
  String? _locationPhoto;
  bool _saving = false;
  bool _pickingPhoto = false;

  @override
  void initState() {
    super.initState();
    if (widget.barang != null) {
      _nameCtrl.text = widget.barang!.name;
      _locationCtrl.text = widget.barang!.location;
      _itemPhoto = widget.barang!.itemPhoto;
      _locationPhoto = widget.barang!.locationPhoto;
    }
  }

  @override
  void dispose() {
    _nameCtrl.dispose();
    _locationCtrl.dispose();
    super.dispose();
  }

  Future<void> _takePhoto({required bool isLocation}) async {
    await _pickImage(source: ImageSource.camera, isLocation: isLocation);
  }

  Future<void> _pickGallery({required bool isLocation}) async {
    await _pickImage(source: ImageSource.gallery, isLocation: isLocation);
  }

  Future<void> _pickImage({
    required ImageSource source,
    required bool isLocation,
  }) async {
    setState(() => _pickingPhoto = true);
    try {
      final picker = ImagePicker();
      final img = await picker.pickImage(
        source: source,
        maxWidth: 1024,
        imageQuality: 70,
      );
      if (img == null) return;
      final saved = await _copyToAppDir(img.path);
      if (saved == null) return;
      setState(() {
        if (isLocation) {
          _locationPhoto = saved;
        } else {
          _itemPhoto = saved;
        }
      });
    } finally {
      if (mounted) setState(() => _pickingPhoto = false);
    }
  }

  Future<String?> _copyToAppDir(String sourcePath) async {
    try {
      final dir = await getApplicationDocumentsDirectory();
      final photosDirPath = '${dir.path}/barang_photos';
      final ext = sourcePath.split('.').lastOrNull ?? 'jpg';
      final name = '${DateTime.now().millisecondsSinceEpoch}.$ext';
      final targetPath = '$photosDirPath/$name';

      return await Isolate.run(
        () => _copyPhotoToAppDir(_CopyPhotoArgs(
          sourcePath: sourcePath,
          photosDirPath: photosDirPath,
          targetPath: targetPath,
        )),
      );
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Gagal menyimpan foto: $e')),
        );
      }
      return null;
    }
  }

  Future<void> _removePhoto({required bool isLocation}) async {
    final path = isLocation ? _locationPhoto : _itemPhoto;
    if (path != null) {
      try {
        final file = File(path);
        if (await file.exists()) await file.delete();
      } catch (_) {}
    }
    setState(() {
      if (isLocation) {
        _locationPhoto = null;
      } else {
        _itemPhoto = null;
      }
    });
  }

  Future<void> _save() async {
    final name = _nameCtrl.text.trim();
    final location = _locationCtrl.text.trim();

    if (name.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Nama barang wajib diisi')),
      );
      return;
    }
    if (location.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Lokasi penyimpanan wajib diisi')),
      );
      return;
    }

    setState(() => _saving = true);
    try {
      final now = DateTime.now();
      if (widget.barang == null) {
        await BarangStore.add(Barang(
          id: now.millisecondsSinceEpoch.toString(),
          name: name,
          location: location,
          itemPhoto: _itemPhoto,
          locationPhoto: _locationPhoto,
          createdAt: now,
          updatedAt: now,
        ));
      } else {
        await BarangStore.update(widget.barang!.copyWith(
          name: name,
          location: location,
          itemPhoto: _itemPhoto,
          locationPhoto: _locationPhoto,
          updatedAt: now,
        ));
      }
      if (!mounted) return;
      Navigator.pop(context, true);
    } catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Gagal menyimpan: $e')),
      );
    } finally {
      if (mounted) setState(() => _saving = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final isEdit = widget.barang != null;
    return Scaffold(
      backgroundColor: AppColors.bg,
      appBar: AppBar(
        title: Text(isEdit ? 'Edit Barang' : 'Tambah Barang'),
      ),
      body: Stack(
        children: [
          SingleChildScrollView(
            padding: const EdgeInsets.fromLTRB(16, 16, 16, 100),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [
                TextField(
                  controller: _nameCtrl,
                  textCapitalization: TextCapitalization.words,
                  decoration: const InputDecoration(
                    labelText: 'NAMA BARANG',
                    hintText: 'contoh: Koper hitam',
                  ),
                ),
                const SizedBox(height: 16),
                TextField(
                  controller: _locationCtrl,
                  textCapitalization: TextCapitalization.sentences,
                  decoration: const InputDecoration(
                    labelText: 'LOKASI PENYIMPANAN',
                    hintText: 'contoh: Lemari atas, rak sepatu, gudang',
                  ),
                ),
                const SizedBox(height: 24),
                _PhotoField(
                  label: 'Foto Barang',
                  photo: _itemPhoto,
                  onCamera: () => _takePhoto(isLocation: false),
                  onGallery: () => _pickGallery(isLocation: false),
                  onRemove: () => _removePhoto(isLocation: false),
                ),
                const SizedBox(height: 16),
                _PhotoField(
                  label: 'Foto Lokasi',
                  photo: _locationPhoto,
                  onCamera: () => _takePhoto(isLocation: true),
                  onGallery: () => _pickGallery(isLocation: true),
                  onRemove: () => _removePhoto(isLocation: true),
                ),
                const SizedBox(height: 28),
                FilledButton(
                  onPressed: _saving || _pickingPhoto ? null : _save,
                  child: _saving
                      ? const SizedBox(
                          height: 20,
                          width: 20,
                          child: CircularProgressIndicator(
                            strokeWidth: 2.5,
                            color: Colors.white,
                          ),
                        )
                      : const Text('Simpan'),
                ),
              ],
            ),
          ),
          if (_saving || _pickingPhoto)
            Container(
              color: Colors.black.withValues(alpha: .2),
              child: const Center(
                child: CircularProgressIndicator(color: AppColors.primary),
              ),
            ),
        ],
      ),
    );
  }
}

class _PhotoField extends StatelessWidget {
  final String label;
  final String? photo;
  final VoidCallback onCamera;
  final VoidCallback onGallery;
  final VoidCallback onRemove;

  const _PhotoField({
    required this.label,
    this.photo,
    required this.onCamera,
    required this.onGallery,
    required this.onRemove,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: AppColors.card,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: AppColors.border),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(label,
              style: const TextStyle(
                fontSize: 12,
                fontWeight: FontWeight.w700,
                color: AppColors.textSecondary,
              )),
          const SizedBox(height: 12),
          if (photo != null)
            ClipRRect(
              borderRadius: BorderRadius.circular(12),
              child: Image.file(
                File(photo!),
                height: 180,
                width: double.infinity,
                fit: BoxFit.cover,
                errorBuilder: (_, _, _) => const _PhotoPlaceholder(),
              ),
            )
          else
            const _PhotoPlaceholder(),
          const SizedBox(height: 12),
          Row(
            children: [
              Expanded(
                child: OutlinedButton.icon(
                  onPressed: onCamera,
                  icon: const Icon(Icons.camera_alt_outlined, size: 18),
                  label: const Text('Kamera'),
                  style: OutlinedButton.styleFrom(
                    foregroundColor: AppColors.primary,
                    side: const BorderSide(color: AppColors.border),
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(10),
                    ),
                  ),
                ),
              ),
              const SizedBox(width: 10),
              Expanded(
                child: OutlinedButton.icon(
                  onPressed: onGallery,
                  icon: const Icon(Icons.photo_library_outlined, size: 18),
                  label: const Text('Galeri'),
                  style: OutlinedButton.styleFrom(
                    foregroundColor: AppColors.primary,
                    side: const BorderSide(color: AppColors.border),
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(10),
                    ),
                  ),
                ),
              ),
              if (photo != null) ...[
                const SizedBox(width: 10),
                IconButton(
                  onPressed: onRemove,
                  icon: const Icon(Icons.delete_outline, color: AppColors.expense),
                ),
              ],
            ],
          ),
        ],
      ),
    );
  }
}

class _PhotoPlaceholder extends StatelessWidget {
  const _PhotoPlaceholder();

  @override
  Widget build(BuildContext context) {
    return Container(
      height: 180,
      width: double.infinity,
      decoration: BoxDecoration(
        color: AppColors.bg,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: AppColors.border, style: BorderStyle.solid),
      ),
      child: const Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Icon(Icons.add_photo_alternate_outlined,
              size: 40, color: AppColors.textMuted),
          SizedBox(height: 8),
          Text(
            'Belum ada foto',
            style: TextStyle(fontSize: 13, color: AppColors.textMuted),
          ),
        ],
      ),
    );
  }
}
