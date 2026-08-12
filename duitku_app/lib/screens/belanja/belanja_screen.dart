import 'package:flutter/material.dart';

import '../../services/belanja_store.dart';
import '../../theme.dart';
import '../../utils/format.dart';

class BelanjaScreen extends StatefulWidget {
  const BelanjaScreen({super.key});

  @override
  State<BelanjaScreen> createState() => _BelanjaScreenState();
}

class BelanjaItem {
  String id;
  String name;
  String qty;
  String price;
  String category;
  String notes;
  String? image;
  String listId;
  bool bought;

  BelanjaItem({
    required this.id,
    required this.name,
    this.qty = '',
    this.price = '',
    this.category = 'Lainnya',
    this.notes = '',
    this.image,
    this.listId = '',
    this.bought = false,
  });

  factory BelanjaItem.fromJson(Map<String, dynamic> j) => BelanjaItem(
        id: j['id']?.toString() ?? '',
        name: j['name']?.toString() ?? '',
        qty: j['qty']?.toString() ?? '',
        price: j['price']?.toString() ?? '',
        category: j['category']?.toString() ?? 'Lainnya',
        notes: j['notes']?.toString() ?? '',
        image: j['image']?.toString(),
        listId: j['listId']?.toString() ?? '',
        bought: (j['bought']?.toString() ?? 'false') == 'true',
      );

  Map<String, dynamic> toJson() => {
        'id': id,
        'name': name,
        'qty': qty,
        'price': price,
        'category': category,
        'notes': notes,
        if (image != null) 'image': image,
        'listId': listId,
        'bought': bought,
      };

  double get priceValue => double.tryParse(price.replaceAll(RegExp(r'[^0-9]'), '')) ?? 0;
}

class BelanjaNote {
  String id;
  String title;
  String content;
  String? image;

  BelanjaNote({required this.id, required this.title, this.content = '', this.image});

  factory BelanjaNote.fromJson(Map<String, dynamic> j) => BelanjaNote(
        id: j['id']?.toString() ?? '',
        title: j['title']?.toString() ?? '',
        content: j['content']?.toString() ?? '',
        image: j['image']?.toString(),
      );

  Map<String, dynamic> toJson() => {
        'id': id,
        'title': title,
        'content': content,
        if (image != null) 'image': image,
      };
}

class _BelanjaScreenState extends State<BelanjaScreen> {
  List<BelanjaItem> _items = [];
  List<BelanjaNote> _notes = [];
  List<Map<String, dynamic>> _lists = [];
  List<Map<String, dynamic>> _favorites = [];
  String _currentListId = '';
  bool _loading = true;
  bool _dirty = false;
  bool _syncing = false;
  String _search = '';
  String _filter = 'Semua';
  int _tab = 0; // 0 daftar, 1 catatan

  static const _categories = ['Semua', 'Sayur', 'Buah', 'Daging', 'Ikan', 'Sembako', 'Minuman', 'Lainnya'];

  @override
  void initState() {
    super.initState();
    _init();
  }

  Future<void> _init() async {
    await _loadLocal();
    if (!mounted) return;
    setState(() => _loading = false);
    _syncAuto();
  }

  Future<void> _loadLocal() async {
    final data = await BelanjaStore.localList('belanja_data');
    final lists = await BelanjaStore.localList('belanja_lists');
    final notes = await BelanjaStore.localList('belanja_notes');
    final favorites = await BelanjaStore.localList('belanja_favorites');
    final current = await BelanjaStore.localGet('belanja_current_list');

    final effectiveLists = lists.isNotEmpty
        ? lists
        : [
            {'id': 'pasar', 'name': 'Pasar'},
            {'id': 'supermarket', 'name': 'Supermarket'},
            {'id': 'warung', 'name': 'Warung'},
          ];

    String curId = current ?? '';
    if (curId.isEmpty || !effectiveLists.any((l) => l['id'] == curId)) {
      curId = effectiveLists.first['id'].toString();
    }
    if (curId != (current ?? '')) {
      await BelanjaStore.localSet('belanja_current_list', curId);
    }

    for (final i in data) {
      final item = BelanjaItem.fromJson(i);
      if (item.listId.isEmpty) item.listId = curId;
    }

    if (!mounted) return;
    setState(() {
      _items = data.map((e) => BelanjaItem.fromJson(e)).toList();
      for (final i in _items) {
        if (i.listId.isEmpty) i.listId = curId;
      }
      _lists = effectiveLists;
      _notes = notes.map((e) => BelanjaNote.fromJson(e)).toList();
      _favorites = favorites;
      _currentListId = curId;
      _dirty = false;
    });
  }

  Future<void> _syncAuto() async {
    final dirty = await BelanjaStore.isDirty();
    if (dirty) {
      _push();
    } else {
      _pull();
    }
  }

  Future<void> _pull() async {
    if (_syncing) return;
    setState(() => _syncing = true);
    await BelanjaStore.pull();
    await _loadLocal();
    if (!mounted) return;
    setState(() => _syncing = false);
  }

  Future<void> _push() async {
    if (_syncing) return;
    setState(() => _syncing = true);
    final ok = await BelanjaStore.push();
    if (!mounted) return;
    setState(() {
      _syncing = false;
      _dirty = !ok;
    });
    if (ok) {
      await _loadLocal();
      if (!mounted) return;
      ScaffoldMessenger.of(context)
          .showSnackBar(const SnackBar(content: Text('Data Belanja disinkronkan')));
    } else {
      ScaffoldMessenger.of(context)
          .showSnackBar(const SnackBar(content: Text('Offline — data tersimpan lokal')));
    }
  }

  Future<void> _save() async {
    await BelanjaStore.localSet('belanja_data', _items.map((e) => e.toJson()).toList());
    await BelanjaStore.localSet('belanja_lists', _lists);
    await BelanjaStore.localSet('belanja_notes', _notes.map((e) => e.toJson()).toList());
    await BelanjaStore.localSet('belanja_favorites', _favorites);
    await BelanjaStore.localSet('belanja_current_list', _currentListId);
    if (!mounted) return;
    setState(() => _dirty = true);
    _push();
  }

  List<BelanjaItem> get _currentItems {    var list = _items.where((i) => i.listId == _currentListId).toList();
    if (_filter != 'Semua') {
      list = list.where((i) => i.category == _filter).toList();
    }
    if (_search.isNotEmpty) {
      list = list.where((i) => (i.name).toLowerCase().contains(_search.toLowerCase())).toList();
    }
    list.sort((a, b) {
      if (a.bought != b.bought) return a.bought ? 1 : -1;
      return a.name.compareTo(b.name);
    });
    return list;
  }

  String get _currentListName {
    for (final l in _lists) {
      if (l['id'] == _currentListId) return l['name'].toString();
    }
    return 'Daftar';
  }

  // ── Actions ──────────────────────────────────────────────────
  Future<void> _openItemSheet({BelanjaItem? item}) async {
    final nameCtrl = TextEditingController(text: item?.name ?? '');
    final qtyCtrl = TextEditingController(text: item?.qty ?? '');
    final priceCtrl = TextEditingController(text: item?.price ?? '');
    final notesCtrl = TextEditingController(text: item?.notes ?? '');
    String category = item?.category ?? 'Sayur';
    String? image = item?.image;

    final saved = await showModalBottomSheet<Map<String, dynamic>>(
      context: context,
      isScrollControlled: true,
      backgroundColor: AppColors.card,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
      ),
      builder: (ctx) => StatefulBuilder(
        builder: (ctx, setSheetState) => Padding(
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
                    decoration: BoxDecoration(
                      color: AppColors.border,
                      borderRadius: BorderRadius.circular(2),
                    ),
                  ),
                ),
                const SizedBox(height: 12),
                Text(item == null ? 'Tambah Barang' : 'Edit Barang',
                    textAlign: TextAlign.center,
                    style: const TextStyle(fontSize: 17, fontWeight: FontWeight.w800, color: AppColors.textPrimary)),
                const SizedBox(height: 16),
                TextField(controller: nameCtrl, decoration: const InputDecoration(labelText: 'NAMA BARANG', hintText: 'contoh: Wortel')),
                const SizedBox(height: 14),
                Row(
                  children: [
                    Expanded(
                      child: TextField(controller: qtyCtrl, decoration: const InputDecoration(labelText: 'JUMLAH', hintText: '1 kg')),
                    ),
                    const SizedBox(width: 10),
                    Expanded(
                      child: TextField(
                        controller: priceCtrl,
                        keyboardType: TextInputType.number,
                        decoration: const InputDecoration(labelText: 'HARGA (OPSIONAL)', prefixText: 'Rp '),
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 14),
                DropdownButtonFormField<String>(
                  initialValue: category,
                  decoration: const InputDecoration(labelText: 'KATEGORI'),
                  items: _categories.where((c) => c != 'Semua').map((c) => DropdownMenuItem(value: c, child: Text(c))).toList(),
                  onChanged: (v) => setSheetState(() => category = v ?? category),
                ),
                const SizedBox(height: 14),
                TextField(controller: notesCtrl, decoration: const InputDecoration(labelText: 'CATATAN (OPSIONAL)')),
                const SizedBox(height: 20),
                FilledButton(
                  onPressed: () {
                    final name = nameCtrl.text.trim();
                    if (name.isEmpty) return;
                    Navigator.pop(ctx, {
                      'name': name,
                      'qty': qtyCtrl.text.trim(),
                      'price': priceCtrl.text.trim(),
                      'category': category,
                      'notes': notesCtrl.text.trim(),
                      'image': image,
                    });
                  },
                  child: const Text('Simpan'),
                ),
              ],
            ),
          ),
        ),
      ),
    );

    if (saved != null) {
      setState(() {
        if (item == null) {
          _items.add(BelanjaItem(
            id: DateTime.now().millisecondsSinceEpoch.toString(),
            name: saved['name'] as String,
            qty: saved['qty'] as String,
            price: saved['price'] as String,
            category: saved['category'] as String,
            notes: saved['notes'] as String,
            image: saved['image'] as String?,
            listId: _currentListId,
          ));
        } else {
          item
            ..name = saved['name'] as String
            ..qty = saved['qty'] as String
            ..price = saved['price'] as String
            ..category = saved['category'] as String
            ..notes = saved['notes'] as String
            ..image = saved['image'] as String?;
        }
      });
      _save();
    }
  }

  void _toggleBought(BelanjaItem item) {
    setState(() => item.bought = !item.bought);
    _save();
  }

  Future<void> _deleteItem(BelanjaItem item) async {
    setState(() => _items.remove(item));
    _save();
  }

  void _toggleFavorite(BelanjaItem item) {
    final idx = _favorites.indexWhere((f) => f['name']?.toString().toLowerCase() == item.name.toLowerCase());
    setState(() {
      if (idx >= 0) {
        _favorites.removeAt(idx);
      } else {
        _favorites.add({'name': item.name, 'category': item.category});
      }
    });
    _save();
  }

  bool _isFavorite(BelanjaItem item) {
    return _favorites.any((f) => f['name']?.toString().toLowerCase() == item.name.toLowerCase());
  }

  Future<void> _addList() async {
    final ctrl = TextEditingController();
    await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        title: const Text('Daftar Baru'),
        content: TextField(controller: ctrl, decoration: const InputDecoration(hintText: 'contoh: Miniloka')),
        actions: [
          TextButton(onPressed: () => Navigator.pop(ctx, false), child: const Text('Batal')),
          FilledButton(
            onPressed: () {
              final name = ctrl.text.trim();
              if (name.isEmpty) return;
              Navigator.pop(ctx, true);
              setState(() {
                final id = 'list_${DateTime.now().millisecondsSinceEpoch}';
                _lists.add({'id': id, 'name': name});
                _currentListId = id;
              });
              _save();
            },
            child: const Text('Buat'),
          ),
        ],
      ),
    );
  }

  Future<void> _openListMenu() async {
    await showModalBottomSheet<String>(
      context: context,
      backgroundColor: AppColors.card,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
      ),
      builder: (ctx) => SafeArea(
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            const Padding(
              padding: EdgeInsets.all(16),
              child: Text('Kelola Daftar',
                  style: TextStyle(fontSize: 16, fontWeight: FontWeight.w800, color: AppColors.textPrimary)),
            ),
            ..._lists.map((l) {
              final active = l['id'] == _currentListId;
              return ListTile(
                title: Text(l['name'].toString(),
                    style: TextStyle(
                        fontSize: 14,
                        fontWeight: FontWeight.w600,
                        color: active ? AppColors.primary : AppColors.textPrimary)),
                trailing: active
                    ? const Icon(Icons.check_circle, color: AppColors.primary, size: 20)
                    : null,
                onTap: () {
                  Navigator.pop(ctx, 'switch');
                  setState(() => _currentListId = l['id'].toString());
                  _save();
                },
              );
            }),
            const Divider(),
            ListTile(
              leading: const Icon(Icons.add),
              title: const Text('Tambah Daftar', style: TextStyle(fontWeight: FontWeight.w700, color: AppColors.primary)),
              onTap: () {
                Navigator.pop(ctx, 'add');
                _addList();
              },
            ),
          ],
        ),
      ),
    );
  }

  Future<void> _openNoteSheet({BelanjaNote? note}) async {
    final titleCtrl = TextEditingController(text: note?.title ?? '');
    final contentCtrl = TextEditingController(text: note?.content ?? '');

    final saved = await showModalBottomSheet<Map<String, dynamic>>(
      context: context,
      isScrollControlled: true,
      backgroundColor: AppColors.card,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
      ),
      builder: (ctx) => Padding(
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
                  decoration: BoxDecoration(
                    color: AppColors.border,
                    borderRadius: BorderRadius.circular(2),
                  ),
                ),
              ),
              const SizedBox(height: 12),
              Text(note == null ? 'Catatan Baru' : 'Edit Catatan',
                  textAlign: TextAlign.center,
                  style: const TextStyle(fontSize: 17, fontWeight: FontWeight.w800, color: AppColors.textPrimary)),
              const SizedBox(height: 16),
              TextField(controller: titleCtrl, decoration: const InputDecoration(labelText: 'JUDUL')),
              const SizedBox(height: 14),
              TextField(
                controller: contentCtrl,
                maxLines: 6,
                decoration: const InputDecoration(labelText: 'ISI CATATAN', alignLabelWithHint: true),
              ),
              const SizedBox(height: 20),
              FilledButton(
                onPressed: () {
                  final title = titleCtrl.text.trim();
                  if (title.isEmpty) return;
                  Navigator.pop(ctx, {'title': title, 'content': contentCtrl.text.trim()});
                },
                child: const Text('Simpan'),
              ),
            ],
          ),
        ),
      ),
    );

    if (saved != null) {
      setState(() {
        if (note == null) {
          _notes.add(BelanjaNote(
            id: DateTime.now().millisecondsSinceEpoch.toString(),
            title: saved['title'] as String,
            content: saved['content'] as String,
          ));
        } else {
          note
            ..title = saved['title'] as String
            ..content = saved['content'] as String;
        }
      });
      _save();
    }
  }

  void _deleteNote(BelanjaNote note) {
    setState(() => _notes.remove(note));
    _save();
  }

  Future<void> _clearBought() async {
    final ok = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        title: const Text('Bersihkan yang sudah dibeli?'),
        content: const Text('Barang yang sudah dicentang akan dihapus dari daftar.'),
        actions: [
          TextButton(onPressed: () => Navigator.pop(ctx, false), child: const Text('Batal')),
          FilledButton(
            onPressed: () => Navigator.pop(ctx, true),
            child: const Text('Bersihkan'),
          ),
        ],
      ),
    );
    if (ok != true) return;
    setState(() => _items.removeWhere((i) => i.bought && i.listId == _currentListId));
    _save();
  }

  @override
  Widget build(BuildContext context) {
    final current = _currentItems;

    return Scaffold(
      backgroundColor: AppColors.bg,
      appBar: AppBar(
        title: const Text('Belanja'),
        actions: [
          if (_syncing)
            const Padding(
              padding: EdgeInsets.only(right: 16),
              child: Center(child: SizedBox(width: 18, height: 18, child: CircularProgressIndicator(strokeWidth: 2))),
            )
          else if (_dirty)
            IconButton(
              tooltip: 'Perlu disinkronkan',
              onPressed: _push,
              icon: const Icon(Icons.cloud_upload_outlined, color: AppColors.textMuted),
            )
          else
            IconButton(
              tooltip: 'Sinkronkan',
              onPressed: _pull,
              icon: const Icon(Icons.cloud_sync_outlined),
            ),
        ],
      ),
      floatingActionButton: _tab == 0
          ? FloatingActionButton.extended(
              onPressed: _openItemSheet,
              backgroundColor: AppColors.primary,
              icon: const Icon(Icons.add, color: Colors.white),
              label: const Text('Tambah', style: TextStyle(color: Colors.white, fontWeight: FontWeight.w700)),
            )
          : FloatingActionButton.extended(
              onPressed: _openNoteSheet,
              backgroundColor: AppColors.primary,
              icon: const Icon(Icons.add, color: Colors.white),
              label: const Text('Catatan', style: TextStyle(color: Colors.white, fontWeight: FontWeight.w700)),
            ),
      body: _loading
          ? const Center(child: CircularProgressIndicator())
          : Column(
              children: [
                Padding(
                  padding: const EdgeInsets.fromLTRB(16, 8, 16, 0),
                  child: Row(
                    children: [
                      Expanded(
                        child: Container(
                          padding: const EdgeInsets.all(4),
                          decoration: BoxDecoration(
                            color: AppColors.bg,
                            borderRadius: BorderRadius.circular(12),
                          ),
                          child: Row(
                            children: [
                              _tabBtn('🛒 Daftar', 0),
                              _tabBtn('📝 Catatan', 1),
                            ],
                          ),
                        ),
                      ),
                      const SizedBox(width: 10),
                      GestureDetector(
                        onTap: _openListMenu,
                        child: Container(
                          padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
                          decoration: BoxDecoration(
                            color: AppColors.card,
                            borderRadius: BorderRadius.circular(12),
                            border: Border.all(color: AppColors.border),
        boxShadow: AppColors.cardShadow,
                          ),
                          child: Row(
                            children: [
                              Text(_currentListName,
                                  style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w700, color: AppColors.primary)),
                              const SizedBox(width: 2),
                              const Icon(Icons.arrow_drop_down, color: AppColors.primary, size: 18),
                            ],
                          ),
                        ),
                      ),
                    ],
                  ),
                ),
                Expanded(
                  child: _tab == 0 ? _buildList(current) : _buildNotes(),
                ),
              ],
            ),
    );
  }

  Widget _tabBtn(String label, int index) {
    final active = _tab == index;
    return Expanded(
      child: GestureDetector(
        onTap: () => setState(() => _tab = index),
        child: Container(
          padding: const EdgeInsets.symmetric(vertical: 8),
          decoration: BoxDecoration(
            color: active ? AppColors.card : Colors.transparent,
            borderRadius: BorderRadius.circular(9),
            boxShadow: active ? [BoxShadow(color: Colors.black.withValues(alpha: .04), blurRadius: 6)] : null,
          ),
          child: Text(label,
              textAlign: TextAlign.center,
              style: TextStyle(
                  fontSize: 12,
                  fontWeight: FontWeight.w700,
                  color: active ? AppColors.primary : AppColors.textSecondary)),
        ),
      ),
    );
  }

  Widget _buildList(List<BelanjaItem> current) {
    final total = current.where((i) => !i.bought).fold<double>(0, (s, i) => s + i.priceValue);

    return Column(
      children: [
        Padding(
          padding: const EdgeInsets.fromLTRB(16, 10, 16, 0),
          child: TextField(
            onChanged: (v) => setState(() => _search = v),
            decoration: InputDecoration(
              hintText: 'Cari barang...',
              prefixIcon: const Icon(Icons.search, size: 20),
              isDense: true,
              contentPadding: const EdgeInsets.symmetric(vertical: 4),
              suffixIcon: _search.isEmpty
                  ? null
                  : IconButton(
                      icon: const Icon(Icons.close, size: 18),
                      onPressed: () => setState(() => _search = ''),
                    ),
            ),
          ),
        ),
        SizedBox(
          height: 42,
          child: ListView.separated(
            scrollDirection: Axis.horizontal,
            padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 6),
            itemCount: _categories.length,
            separatorBuilder: (_, _) => const SizedBox(width: 6),
            itemBuilder: (context, i) {
              final c = _categories[i];
              final active = _filter == c;
              return ChoiceChip(
                label: Text(c),
                selected: active,
                onSelected: (_) => setState(() => _filter = c),
                selectedColor: AppColors.primary,
                labelStyle: TextStyle(
                    fontSize: 11,
                    fontWeight: FontWeight.w700,
                    color: active ? Colors.white : AppColors.textSecondary),
                backgroundColor: AppColors.card,
                side: BorderSide(color: active ? AppColors.primary : AppColors.border),
                showCheckmark: false,
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(18)),
              );
            },
          ),
        ),
        Padding(
          padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 4),
          child: Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Text('${current.length} barang · ${current.where((i) => i.bought).length} dibeli',
                  style: const TextStyle(fontSize: 11, color: AppColors.textMuted)),
              if (current.any((i) => i.bought))
                TextButton(
                  onPressed: _clearBought,
                  style: TextButton.styleFrom(
                    padding: const EdgeInsets.symmetric(horizontal: 6),
                    minimumSize: Size.zero,
                    tapTargetSize: MaterialTapTargetSize.shrinkWrap,
                  ),
                  child: const Text('✓ Bersihkan',
                      style: TextStyle(fontSize: 11, fontWeight: FontWeight.w700, color: AppColors.primary)),
                )
              else if (total > 0)
                Text('Estimasi ${Fmt.money(total)}',
                    style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w800, color: AppColors.primary)),
            ],
          ),
        ),
        Expanded(
          child: current.isEmpty
              ? const Center(
                  child: Column(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      Icon(Icons.shopping_cart_outlined, size: 44, color: AppColors.textMuted),
                      SizedBox(height: 10),
                      Text('Daftar belanja kosong',
                          style: TextStyle(fontSize: 14, fontWeight: FontWeight.w700, color: AppColors.textSecondary)),
                      SizedBox(height: 4),
                      Text('Tekan tombol + untuk menambah barang.',
                          style: TextStyle(fontSize: 12, color: AppColors.textMuted)),
                    ],
                  ),
                )
              : RefreshIndicator(
                  onRefresh: _pull,
                  child: ListView.builder(
                    padding: const EdgeInsets.fromLTRB(16, 4, 16, 100),
                    itemCount: current.length,
                    itemBuilder: (context, i) {
                      final item = current[i];
                      return _ItemTile(
                        item: item,
                        isFavorite: _isFavorite(item),
                        onToggle: () => _toggleBought(item),
                        onEdit: () => _openItemSheet(item: item),
                        onDelete: () => _deleteItem(item),
                        onFavorite: () => _toggleFavorite(item),
                      );
                    },
                  ),
                ),
        ),
      ],
    );
  }

  Widget _buildNotes() {
    return ListView(
      padding: const EdgeInsets.fromLTRB(16, 12, 16, 100),
      children: [
        if (_notes.isEmpty)
          const Padding(
            padding: EdgeInsets.symmetric(vertical: 60),
            child: Column(
              children: [
                Icon(Icons.edit_note, size: 44, color: AppColors.textMuted),
                SizedBox(height: 10),
                Text('Belum ada catatan',
                    style: TextStyle(fontSize: 14, fontWeight: FontWeight.w700, color: AppColors.textSecondary)),
              ],
            ),
          )
        else
          ..._notes.map((n) => Container(
                margin: const EdgeInsets.only(bottom: 10),
                padding: const EdgeInsets.all(14),
                decoration: BoxDecoration(
                  color: AppColors.card,
                  borderRadius: BorderRadius.circular(16),
                  border: Border.all(color: AppColors.border),
        boxShadow: AppColors.cardShadow,
                ),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      children: [
                        Expanded(
                          child: Text(n.title,
                              style: const TextStyle(fontSize: 14, fontWeight: FontWeight.w700, color: AppColors.textPrimary)),
                        ),
                        IconButton(
                          onPressed: () => _openNoteSheet(note: n),
                          icon: const Icon(Icons.edit_outlined, size: 18, color: AppColors.textMuted),
                        ),
                        IconButton(
                          onPressed: () => _deleteNote(n),
                          icon: const Icon(Icons.delete_outline, size: 18, color: AppColors.expense),
                        ),
                      ],
                    ),
                    if (n.content.isNotEmpty)
                      Text(n.content, style: const TextStyle(fontSize: 13, height: 1.5, color: AppColors.textSecondary)),
                  ],
                ),
              )),
      ],
    );
  }
}

class _ItemTile extends StatelessWidget {
  final BelanjaItem item;
  final bool isFavorite;
  final VoidCallback onToggle;
  final VoidCallback onEdit;
  final VoidCallback onDelete;
  final VoidCallback onFavorite;

  const _ItemTile({
    required this.item,
    required this.isFavorite,
    required this.onToggle,
    required this.onEdit,
    required this.onDelete,
    required this.onFavorite,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      margin: const EdgeInsets.only(bottom: 8),
      padding: const EdgeInsets.all(10),
      decoration: BoxDecoration(
        color: AppColors.card,
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: item.bought ? AppColors.border : AppColors.primaryLight, width: item.bought ? 1 : 1.2),
        boxShadow: item.bought ? null : AppColors.cardShadow,
      ),
      child: Row(
        children: [
          Checkbox(
            value: item.bought,
            activeColor: AppColors.primary,
            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(5)),
            onChanged: (_) => onToggle(),
          ),
          Expanded(
            child: GestureDetector(
              onTap: onEdit,
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    item.name,
                    style: TextStyle(
                      fontSize: 14,
                      fontWeight: FontWeight.w700,
                      color: item.bought ? AppColors.textMuted : AppColors.textPrimary,
                      decoration: item.bought ? TextDecoration.lineThrough : null,
                    ),
                  ),
                  if (item.qty.isNotEmpty || item.category.isNotEmpty)
                    Text('${item.qty}${item.qty.isNotEmpty && item.category.isNotEmpty ? ' · ' : ''}${item.category}',
                        style: const TextStyle(fontSize: 11, color: AppColors.textMuted)),
                  if (item.notes.isNotEmpty)
                    Text(item.notes,
                        maxLines: 1, overflow: TextOverflow.ellipsis,
                        style: const TextStyle(fontSize: 11, color: AppColors.textMuted)),
                ],
              ),
            ),
          ),
          if (item.priceValue > 0)
            Padding(
              padding: const EdgeInsets.only(right: 8),
              child: Text(Fmt.money0(item.priceValue),
                  style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w800, color: AppColors.textPrimary)),
            ),
          IconButton(
            onPressed: onFavorite,
            icon: Icon(
              isFavorite ? Icons.star : Icons.star_border,
              size: 19,
              color: isFavorite ? const Color(0xFFF59E0B) : AppColors.textMuted,
            ),
          ),
          IconButton(
            onPressed: onDelete,
            icon: const Icon(Icons.delete_outline, size: 19, color: AppColors.textMuted),
          ),
        ],
      ),
    );
  }
}
