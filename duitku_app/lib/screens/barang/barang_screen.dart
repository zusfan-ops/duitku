import 'dart:io';

import 'package:flutter/material.dart';
import 'package:image_picker/image_picker.dart';
import 'package:intl/intl.dart';

import '../../config/api_config.dart';
import '../../models/barang.dart';
import '../../services/api_service.dart';
import '../../services/barang_store.dart';
import '../../theme.dart';

class BarangScreen extends StatefulWidget {
  final int initialTabIndex;

  const BarangScreen({
    super.key,
    this.initialTabIndex = 0,
  });

  @override
  State<BarangScreen> createState() => _BarangScreenState();
}

class _BarangScreenState extends State<BarangScreen> {
  int _currentTab = 0;
  List<Barang> _items = [];
  bool _loading = true;
  String _search = '';

  @override
  void initState() {
    super.initState();
    _currentTab = widget.initialTabIndex;
    _load();
  }

  Future<void> _load() async {
    final localItems = await BarangStore.loadAll();
    if (mounted) {
      setState(() {
        _items = localItems;
        _loading = false;
      });
    }

    // Attempt background sync from API
    try {
      final res = await ApiService.instance.get('barang');
      if (res['success'] == true && res['items'] is List) {
        final serverList = (res['items'] as List)
            .map((e) => Barang.fromJson(e as Map<String, dynamic>))
            .toList();
        if (serverList.isNotEmpty) {
          await BarangStore.saveAll(serverList);
          if (mounted) {
            setState(() {
              _items = serverList;
            });
          }
        }
      }
    } catch (_) {}
  }

  // Summary calculations
  int get _healthScore {
    if (_items.isEmpty) return 0;
    int dueMaint = 0;
    final nowStr = DateFormat('yyyy-MM-dd').format(DateTime.now());
    for (final it in _items) {
      for (final m in it.maintenance) {
        if (!m.isDone && m.dueDate.isNotEmpty && m.dueDate.compareTo(nowStr) <= 0) {
          dueMaint++;
        }
      }
    }
    int score = 100 - (dueMaint * 12);
    return score.clamp(45, 100);
  }

  String get _healthStatus {
    if (_items.isEmpty) return 'Belum ada aset terdaftar. Mulai catat aset rumah Anda.';
    final score = _healthScore;
    if (score >= 90) return 'Kondisi rumah & seluruh aset sangat prima.';
    if (score >= 75) return 'Kondisi rumah baik, ada beberapa jadwal perawatan mendatang.';
    return 'Beberapa perawatan aset butuh perhatian Anda.';
  }

  List<MaintenanceTask> get _allMaintenance {
    final list = <MaintenanceTask>[];
    for (final it in _items) {
      list.addAll(it.maintenance);
    }
    return list;
  }

  List<WarrantyItem> get _allWarranties {
    final list = <WarrantyItem>[];
    for (final it in _items) {
      list.addAll(it.warranties);
    }
    return list;
  }

  Map<String, List<Barang>> get _groupedByRoom {
    final map = <String, List<Barang>>{};
    for (final it in _filteredItems) {
      final r = it.room.isNotEmpty ? it.room : (it.location.isNotEmpty ? it.location : 'Lainnya');
      map.putIfAbsent(r, () => []).add(it);
    }
    return map;
  }

  List<Barang> get _filteredItems {
    if (_search.trim().isEmpty) return _items;
    final q = _search.toLowerCase();
    return _items.where((b) {
      return b.name.toLowerCase().contains(q) ||
          b.room.toLowerCase().contains(q) ||
          b.category.toLowerCase().contains(q) ||
          b.brand.toLowerCase().contains(q);
    }).toList();
  }

  String _getRoomIcon(String room) {
    final r = room.toLowerCase();
    if (r.contains('garasi') || r.contains('garage')) return '🚗';
    if (r.contains('tamu') || r.contains('living')) return '🛋️';
    if (r.contains('dapur') || r.contains('kitchen')) return '🍽️';
    if (r.contains('tidur') || r.contains('bedroom')) return '🛏️';
    if (r.contains('luar') || r.contains('exterior') || r.contains('taman')) return '🏡';
    if (r.contains('kerja') || r.contains('office')) return '💼';
    if (r.contains('mandi') || r.contains('bath')) return '🚿';
    return '🏠';
  }

  void _openAddForm({Barang? barang}) {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (ctx) => _AssetFormSheet(
        barang: barang,
        onSaved: (savedBarang) async {
          if (barang == null) {
            await BarangStore.add(savedBarang);
          } else {
            await BarangStore.update(savedBarang);
          }
          _load();
          // Sync in background
          try {
            await ApiService.instance.post('barang/store', savedBarang.toJson());
          } catch (_) {}
        },
      ),
    );
  }

  void _openDetailSheet(Barang barang) {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (ctx) => _AssetDetailSheet(
        barang: barang,
        onEdit: () {
          Navigator.pop(ctx);
          _openAddForm(barang: barang);
        },
        onDelete: () async {
          Navigator.pop(ctx);
          final confirm = await showDialog<bool>(
            context: context,
            builder: (c) => AlertDialog(
              title: const Text('Hapus Aset?'),
              content: Text('Yakin ingin menghapus "${barang.name}"?'),
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
          if (confirm == true) {
            await BarangStore.delete(barang.id);
            _load();
            try {
              await ApiService.instance.post('barang/delete/${barang.id}', {});
            } catch (_) {}
          }
        },
        onUpdated: (updated) async {
          await BarangStore.update(updated);
          _load();
          try {
            await ApiService.instance.post('barang/store', updated.toJson());
          } catch (_) {}
        },
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.bg,
      appBar: AppBar(
        backgroundColor: AppColors.card,
        elevation: 0,
        title: const Row(
          children: [
            Text('🏡 My Home', style: TextStyle(fontWeight: FontWeight.w900, fontSize: 18)),
          ],
        ),
        actions: [
          IconButton(
            icon: Container(
              padding: const EdgeInsets.all(6),
              decoration: const BoxDecoration(
                color: Color(0xFF6366F1),
                shape: BoxShape.circle,
              ),
              child: const Icon(Icons.add, color: Colors.white, size: 18),
            ),
            tooltip: 'Tambah Aset',
            onPressed: () => _openAddForm(),
          ),
          const SizedBox(width: 8),
        ],
      ),
      body: _loading
          ? const Center(child: CircularProgressIndicator())
          : IndexedStack(
              index: _currentTab,
              children: [
                _buildHomeTab(),
                _buildRoomsTab(),
                _buildMaintenanceTab(),
                _buildWarrantiesTab(),
                _buildHouseholdTab(),
              ],
            ),
      bottomNavigationBar: Container(
        decoration: BoxDecoration(
          color: AppColors.card,
          border: Border(top: BorderSide(color: AppColors.border, width: 1)),
        ),
        child: BottomNavigationBar(
          currentIndex: _currentTab,
          onTap: (i) => setState(() => _currentTab = i),
          type: BottomNavigationBarType.fixed,
          backgroundColor: AppColors.card,
          selectedItemColor: const Color(0xFF6366F1),
          unselectedItemColor: AppColors.textMuted,
          selectedFontSize: 11,
          unselectedFontSize: 10,
          elevation: 0,
          items: [
            const BottomNavigationBarItem(icon: Icon(Icons.home_rounded), label: 'Home'),
            BottomNavigationBarItem(
              icon: const Icon(Icons.meeting_room_rounded),
              label: 'Ruangan (${_groupedByRoom.keys.length})',
            ),
            BottomNavigationBarItem(
              icon: const Icon(Icons.build_circle_rounded),
              label: 'Perawatan (${_allMaintenance.length})',
            ),
            BottomNavigationBarItem(
              icon: const Icon(Icons.verified_user_rounded),
              label: 'Garansi (${_allWarranties.length})',
            ),
            const BottomNavigationBarItem(icon: Icon(Icons.people_alt_rounded), label: 'Keluarga'),
          ],
        ),
      ),
    );
  }

  // ── TAB 0: HOME AT A GLANCE ──
  Widget _buildHomeTab() {
    return ListView(
      padding: const EdgeInsets.fromLTRB(16, 12, 16, 30),
      children: [
        // Hero Health Card
        Container(
          padding: const EdgeInsets.symmetric(vertical: 22, horizontal: 16),
          decoration: BoxDecoration(
            gradient: const LinearGradient(
              colors: [Color(0xFFF97316), Color(0xFFD946EF), Color(0xFF6366F1)],
              begin: Alignment.topLeft,
              end: Alignment.bottomRight,
            ),
            borderRadius: BorderRadius.circular(22),
            boxShadow: [
              BoxShadow(
                color: const Color(0xFFD946EF).withValues(alpha: 0.35),
                blurRadius: 20,
                offset: const Offset(0, 8),
              ),
            ],
          ),
          child: Column(
            children: [
              Text(
                _items.isEmpty ? '-' : '$_healthScore',
                style: const TextStyle(
                  fontSize: 50,
                  fontWeight: FontWeight.w900,
                  color: Colors.white,
                  letterSpacing: -1,
                  height: 1.05,
                ),
              ),
              const SizedBox(height: 4),
              const Row(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  Icon(Icons.home_rounded, color: Colors.white, size: 18),
                  SizedBox(width: 5),
                  Text(
                    'Home Health',
                    style: TextStyle(
                      fontSize: 14,
                      fontWeight: FontWeight.w800,
                      color: Colors.white,
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 4),
              Text(
                _healthStatus,
                textAlign: TextAlign.center,
                style: TextStyle(
                  fontSize: 12,
                  color: Colors.white.withValues(alpha: 0.94),
                ),
              ),
              const SizedBox(height: 12),
              GestureDetector(
                onTap: _items.isEmpty ? () => _openAddForm() : null,
                child: Container(
                  padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 5),
                  decoration: BoxDecoration(
                    color: Colors.white.withValues(alpha: 0.22),
                    borderRadius: BorderRadius.circular(20),
                  ),
                  child: Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Text(_items.isEmpty ? '➕' : '✨', style: const TextStyle(fontSize: 12)),
                      const SizedBox(width: 5),
                      Text(
                        _items.isEmpty ? 'Mulai catat aset pertama' : 'Semua aset dan perawatan terpelihara',
                        style: const TextStyle(
                          fontSize: 11,
                          fontWeight: FontWeight.w700,
                          color: Colors.white,
                        ),
                      ),
                    ],
                  ),
                ),
              ),
            ],
          ),
        ),
        const SizedBox(height: 16),

        // Section Title
        const Text(
          'Home Summary',
          style: TextStyle(fontSize: 15, fontWeight: FontWeight.w800, color: AppColors.textPrimary),
        ),
        const SizedBox(height: 10),

        // 4-Tile Grid
        Row(
          children: [
            Expanded(
              child: _buildSummaryTile(
                icon: '🏢',
                count: '${_groupedByRoom.keys.length}',
                label: 'Rooms',
                bg: const Color(0xFFEFF6FF),
                onTap: () => setState(() => _currentTab = 1),
              ),
            ),
            const SizedBox(width: 10),
            Expanded(
              child: _buildSummaryTile(
                icon: '📦',
                count: '${_items.length}',
                label: 'Assets',
                bg: const Color(0xFFFEF3C7),
                onTap: () => setState(() => _currentTab = 1),
              ),
            ),
          ],
        ),
        const SizedBox(height: 10),
        Row(
          children: [
            Expanded(
              child: _buildSummaryTile(
                icon: '🛠️',
                count: '${_allMaintenance.length}',
                label: 'Maintenance',
                bg: const Color(0xFFECFDF5),
                onTap: () => setState(() => _currentTab = 2),
              ),
            ),
            const SizedBox(width: 10),
            Expanded(
              child: _buildSummaryTile(
                icon: '🛡️',
                count: '${_allWarranties.length}',
                label: 'Warranties',
                bg: const Color(0xFFF5F3FF),
                onTap: () => setState(() => _currentTab = 3),
              ),
            ),
          ],
        ),
        const SizedBox(height: 20),

        // Recent Assets Row
        Row(
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          children: [
            const Text(
              'Aset Terbaru',
              style: TextStyle(fontSize: 15, fontWeight: FontWeight.w800, color: AppColors.textPrimary),
            ),
            TextButton(
              onPressed: () => setState(() => _currentTab = 1),
              child: const Text('Lihat Semua', style: TextStyle(fontSize: 12, fontWeight: FontWeight.w700)),
            ),
          ],
        ),
        if (_items.isEmpty)
          Container(
            padding: const EdgeInsets.all(24),
            decoration: BoxDecoration(
              color: AppColors.card,
              borderRadius: BorderRadius.circular(16),
              border: Border.all(color: AppColors.border),
            ),
            child: const Center(
              child: Text(
                'Belum ada aset terdaftar.\nTekan + untuk menambahkan barang/aset pertama Anda.',
                textAlign: TextAlign.center,
                style: TextStyle(fontSize: 12.5, color: AppColors.textMuted),
              ),
            ),
          )
        else
          ..._items.take(4).map((b) => _buildAssetRow(b)),
      ],
    );
  }

  // ── TAB 1: EVERY ROOM, ORGANIZED ──
  Widget _buildRoomsTab() {
    final grouped = _groupedByRoom;

    return ListView(
      padding: const EdgeInsets.fromLTRB(16, 12, 16, 30),
      children: [
        // Search bar
        Container(
          padding: const EdgeInsets.symmetric(horizontal: 12),
          decoration: BoxDecoration(
            color: AppColors.card,
            borderRadius: BorderRadius.circular(14),
            border: Border.all(color: AppColors.border),
          ),
          child: Row(
            children: [
              const Icon(Icons.search_rounded, size: 20, color: AppColors.textMuted),
              const SizedBox(width: 8),
              Expanded(
                child: TextField(
                  decoration: const InputDecoration(
                    hintText: 'Search assets (Fridge, TV, Sofa)...',
                    hintStyle: TextStyle(fontSize: 13, color: AppColors.textMuted),
                    border: InputBorder.none,
                  ),
                  onChanged: (v) => setState(() => _search = v),
                ),
              ),
              if (_search.isNotEmpty)
                GestureDetector(
                  onTap: () => setState(() => _search = ''),
                  child: const Icon(Icons.close_rounded, size: 18, color: AppColors.textMuted),
                ),
            ],
          ),
        ),
        const SizedBox(height: 16),

        if (grouped.isEmpty)
          const Center(
            child: Padding(
              padding: EdgeInsets.all(40),
              child: Text('Tidak ada aset ditemukan', style: TextStyle(color: AppColors.textMuted)),
            ),
          )
        else
          ...grouped.entries.map((entry) {
            final roomName = entry.key;
            final assets = entry.value;

            return Container(
              margin: const EdgeInsets.only(bottom: 12),
              decoration: BoxDecoration(
                color: AppColors.card,
                borderRadius: BorderRadius.circular(18),
                border: Border.all(color: AppColors.border),
              ),
              child: Theme(
                data: Theme.of(context).copyWith(dividerColor: Colors.transparent),
                child: ExpansionTile(
                  initiallyExpanded: true,
                  leading: Text(_getRoomIcon(roomName), style: const TextStyle(fontSize: 22)),
                  title: Text(
                    roomName,
                    style: const TextStyle(fontSize: 15, fontWeight: FontWeight.w800, color: AppColors.textPrimary),
                  ),
                  subtitle: Text(
                    '${assets.length} item${assets.length > 1 ? 's' : ''}',
                    style: const TextStyle(fontSize: 11.5, color: AppColors.textMuted),
                  ),
                  children: assets.map((b) => _buildAssetRow(b)).toList(),
                ),
              ),
            );
          }),
      ],
    );
  }

  // ── TAB 2: NEVER MISS MAINTENANCE ──
  Widget _buildMaintenanceTab() {
    final maintList = _allMaintenance;

    return ListView(
      padding: const EdgeInsets.fromLTRB(16, 12, 16, 30),
      children: [
        Row(
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          children: [
            const Text(
              'Jadwal Perawatan Berkala',
              style: TextStyle(fontSize: 16, fontWeight: FontWeight.w800, color: AppColors.textPrimary),
            ),
            IconButton(
              icon: const Icon(Icons.add_task_rounded, color: Color(0xFF6366F1)),
              tooltip: 'Tambah Aset Baru',
              onPressed: () => _openAddForm(),
            ),
          ],
        ),
        const SizedBox(height: 8),

        if (maintList.isEmpty)
          Container(
            padding: const EdgeInsets.all(32),
            decoration: BoxDecoration(
              color: AppColors.card,
              borderRadius: BorderRadius.circular(18),
              border: Border.all(color: AppColors.border),
            ),
            child: const Center(
              child: Column(
                children: [
                  Icon(Icons.build_circle_outlined, size: 40, color: Color(0xFF6366F1)),
                  SizedBox(height: 8),
                  Text(
                    'Belum ada jadwal perawatan',
                    style: TextStyle(fontSize: 14, fontWeight: FontWeight.w800),
                  ),
                  SizedBox(height: 4),
                  Text(
                    'Tambahkan perawatan berkala pada detail aset (misal: ganti filter, cuci AC).',
                    textAlign: TextAlign.center,
                    style: TextStyle(fontSize: 11.5, color: AppColors.textMuted),
                  ),
                ],
              ),
            ),
          )
        else
          ...maintList.map((m) {
            return Container(
              margin: const EdgeInsets.only(bottom: 8),
              padding: const EdgeInsets.all(14),
              decoration: BoxDecoration(
                color: AppColors.card,
                borderRadius: BorderRadius.circular(14),
                border: Border.all(color: AppColors.border),
              ),
              child: Row(
                children: [
                  Container(
                    width: 38,
                    height: 38,
                    decoration: BoxDecoration(
                      color: const Color(0xFFECFDF5),
                      borderRadius: BorderRadius.circular(10),
                    ),
                    child: const Center(child: Text('🛠️', style: TextStyle(fontSize: 18))),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          m.title,
                          style: TextStyle(
                            fontSize: 13.5,
                            fontWeight: FontWeight.w800,
                            color: AppColors.textPrimary,
                            decoration: m.isDone ? TextDecoration.lineThrough : null,
                          ),
                        ),
                        Text(
                          '${m.frequency} • Tempo: ${m.dueDate.isNotEmpty ? m.dueDate : '-'}',
                          style: const TextStyle(fontSize: 11, color: AppColors.textMuted),
                        ),
                      ],
                    ),
                  ),
                  Checkbox(
                    value: m.isDone,
                    activeColor: const Color(0xFF059669),
                    onChanged: (val) async {
                      setState(() => m.isDone = val ?? false);
                      await BarangStore.saveAll(_items);
                    },
                  ),
                ],
              ),
            );
          }),
      ],
    );
  }

  // ── TAB 3: TRACK EVERY WARRANTY ──
  Widget _buildWarrantiesTab() {
    final warrList = _allWarranties;

    return ListView(
      padding: const EdgeInsets.fromLTRB(16, 12, 16, 30),
      children: [
        const Text(
          'Garansi & Perlindungan Aset',
          style: TextStyle(fontSize: 16, fontWeight: FontWeight.w800, color: AppColors.textPrimary),
        ),
        const SizedBox(height: 10),

        if (warrList.isEmpty)
          Container(
            padding: const EdgeInsets.all(32),
            decoration: BoxDecoration(
              color: AppColors.card,
              borderRadius: BorderRadius.circular(18),
              border: Border.all(color: AppColors.border),
            ),
            child: const Center(
              child: Column(
                children: [
                  Icon(Icons.shield_outlined, size: 40, color: Color(0xFF6366F1)),
                  SizedBox(height: 8),
                  Text(
                    'Belum ada data garansi',
                    style: TextStyle(fontSize: 14, fontWeight: FontWeight.w800),
                  ),
                  SizedBox(height: 4),
                  Text(
                    'Catat garansi resmi aset dan barang elektronik Anda untuk kemudahan klaim.',
                    textAlign: TextAlign.center,
                    style: TextStyle(fontSize: 11.5, color: AppColors.textMuted),
                  ),
                ],
              ),
            ),
          )
        else
          ...warrList.map((w) {
            return Container(
              margin: const EdgeInsets.only(bottom: 8),
              padding: const EdgeInsets.all(14),
              decoration: BoxDecoration(
                color: AppColors.card,
                borderRadius: BorderRadius.circular(14),
                border: Border.all(color: AppColors.border),
              ),
              child: Row(
                children: [
                  Container(
                    width: 38,
                    height: 38,
                    decoration: BoxDecoration(
                      color: const Color(0xFFEFF6FF),
                      borderRadius: BorderRadius.circular(10),
                    ),
                    child: const Center(child: Text('🛡️', style: TextStyle(fontSize: 18))),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          w.provider,
                          style: const TextStyle(fontSize: 13.5, fontWeight: FontWeight.w800, color: AppColors.textPrimary),
                        ),
                        Text(
                          'Berlaku hingga: ${w.expiryDate.isNotEmpty ? w.expiryDate : '-'}',
                          style: const TextStyle(fontSize: 11, color: AppColors.textMuted),
                        ),
                      ],
                    ),
                  ),
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                    decoration: BoxDecoration(
                      color: const Color(0xFFECFDF5),
                      borderRadius: BorderRadius.circular(8),
                    ),
                    child: Text(
                      w.status,
                      style: const TextStyle(fontSize: 10.5, fontWeight: FontWeight.w800, color: Color(0xFF059669)),
                    ),
                  ),
                ],
              ),
            );
          }),
      ],
    );
  }

  // ── TAB 4: HOUSEHOLD (SHARE WITH YOUR HOUSEHOLD) ──
  Widget _buildHouseholdTab() {
    return ListView(
      padding: const EdgeInsets.fromLTRB(16, 12, 16, 30),
      children: [
        const Text(
          'Share With Your Household',
          style: TextStyle(fontSize: 16, fontWeight: FontWeight.w800, color: AppColors.textPrimary),
        ),
        const SizedBox(height: 12),

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
              const Text('Signed in as', style: TextStyle(fontSize: 11, color: AppColors.textMuted)),
              const SizedBox(height: 8),
              const Row(
                children: [
                  CircleAvatar(
                    radius: 20,
                    backgroundColor: Color(0xFF6366F1),
                    child: Icon(Icons.person, color: Colors.white),
                  ),
                  SizedBox(width: 12),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text('Owner / Pengguna Utama', style: TextStyle(fontSize: 14, fontWeight: FontWeight.w800)),
                        Text('DuitKu Household Account', style: TextStyle(fontSize: 11, color: AppColors.textMuted)),
                      ],
                    ),
                  ),
                  Chip(
                    label: Text('Owner', style: TextStyle(fontSize: 10.5, fontWeight: FontWeight.w800, color: Colors.white)),
                    backgroundColor: Color(0xFF6366F1),
                    padding: EdgeInsets.zero,
                  ),
                ],
              ),
              const SizedBox(height: 16),
              const Divider(),
              const SizedBox(height: 8),
              const Text('Members', style: TextStyle(fontSize: 13, fontWeight: FontWeight.w800)),
              const SizedBox(height: 8),
              const ListTile(
                contentPadding: EdgeInsets.zero,
                leading: CircleAvatar(child: Text('👨‍👩‍👦')),
                title: Text('Keluarga Anda', style: TextStyle(fontSize: 13, fontWeight: FontWeight.w700)),
                subtitle: Text('Semua perangkat yang terhubung memiliki akses inventaris ini.', style: TextStyle(fontSize: 11)),
              ),
            ],
          ),
        ),
      ],
    );
  }

  Widget _buildSummaryTile({
    required String icon,
    required String count,
    required String label,
    required Color bg,
    required VoidCallback onTap,
  }) {
    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(16),
      child: Container(
        padding: const EdgeInsets.all(14),
        decoration: BoxDecoration(
          color: AppColors.card,
          borderRadius: BorderRadius.circular(16),
          border: Border.all(color: AppColors.border),
        ),
        child: Column(
          children: [
            Container(
              width: 38,
              height: 38,
              decoration: BoxDecoration(color: bg, borderRadius: BorderRadius.circular(10)),
              child: Center(child: Text(icon, style: const TextStyle(fontSize: 18))),
            ),
            const SizedBox(height: 6),
            Text(count, style: const TextStyle(fontSize: 18, fontWeight: FontWeight.w900, color: AppColors.textPrimary)),
            Text(label, style: const TextStyle(fontSize: 11, color: AppColors.textMuted, fontWeight: FontWeight.w600)),
          ],
        ),
      ),
    );
  }

  Widget _buildAssetRow(Barang b) {
    return Container(
      margin: const EdgeInsets.only(bottom: 8),
      padding: const EdgeInsets.all(10),
      decoration: BoxDecoration(
        color: AppColors.bg,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: AppColors.border),
      ),
      child: InkWell(
        onTap: () => _openDetailSheet(b),
        child: Row(
          children: [
            Container(
              width: 44,
              height: 44,
              decoration: BoxDecoration(
                color: const Color(0xFFEEF2FF),
                borderRadius: BorderRadius.circular(10),
              ),
              child: ClipRRect(
                borderRadius: BorderRadius.circular(10),
                child: b.itemPhoto != null && b.itemPhoto!.isNotEmpty
                    ? (b.itemPhoto!.startsWith('http') || b.itemPhoto!.startsWith('/'))
                        ? Image.network(
                            b.itemPhoto!.startsWith('http') ? b.itemPhoto! : '${ApiConfig.baseUrl}${b.itemPhoto!}',
                            fit: BoxFit.cover,
                            errorBuilder: (context, error, stackTrace) => const Center(child: Text('📦', style: TextStyle(fontSize: 20))),
                          )
                        : Image.file(File(b.itemPhoto!), fit: BoxFit.cover, errorBuilder: (context, error, stackTrace) => const Center(child: Text('📦', style: TextStyle(fontSize: 20))))
                    : const Center(child: Text('📦', style: TextStyle(fontSize: 20))),
              ),
            ),
            const SizedBox(width: 10),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(b.name, style: const TextStyle(fontSize: 13.5, fontWeight: FontWeight.w800, color: AppColors.textPrimary)),
                  Text(
                    '${b.category} • ${b.brand.isNotEmpty ? b.brand : b.room}',
                    style: const TextStyle(fontSize: 11, color: AppColors.textMuted),
                  ),
                ],
              ),
            ),
            const Icon(Icons.chevron_right_rounded, color: AppColors.textMuted, size: 20),
          ],
        ),
      ),
    );
  }
}

// ── SHEET: DETAIL ASET (NEVER MISS MAINTENANCE & WARRANTIES) ──
class _AssetDetailSheet extends StatefulWidget {
  final Barang barang;
  final VoidCallback onEdit;
  final VoidCallback onDelete;
  final ValueChanged<Barang> onUpdated;

  const _AssetDetailSheet({
    required this.barang,
    required this.onEdit,
    required this.onDelete,
    required this.onUpdated,
  });

  @override
  State<_AssetDetailSheet> createState() => _AssetDetailSheetState();
}

class _AssetDetailSheetState extends State<_AssetDetailSheet> with SingleTickerProviderStateMixin {
  late TabController _tabCtrl;
  late Barang _b;

  @override
  void initState() {
    super.initState();
    _tabCtrl = TabController(length: 3, vsync: this);
    _b = widget.barang;
  }

  void _addMaintenance() async {
    final titleCtrl = TextEditingController();
    final freqCtrl = TextEditingController(text: 'Every 6 Months');
    final dateCtrl = TextEditingController(text: DateFormat('yyyy-MM-dd').format(DateTime.now().add(const Duration(days: 180))));

    final ok = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        title: const Text('Tambah Perawatan'),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            TextField(controller: titleCtrl, decoration: const InputDecoration(labelText: 'Tugas Perawatan (mis: Ganti Filter)')),
            TextField(controller: freqCtrl, decoration: const InputDecoration(labelText: 'Frekuensi (mis: Every 6 Months)')),
            TextField(controller: dateCtrl, decoration: const InputDecoration(labelText: 'Tanggal Jatuh Tempo (YYYY-MM-DD)')),
          ],
        ),
        actions: [
          TextButton(onPressed: () => Navigator.pop(ctx, false), child: const Text('Batal')),
          ElevatedButton(onPressed: () => Navigator.pop(ctx, true), child: const Text('Simpan')),
        ],
      ),
    );

    if (ok == true && titleCtrl.text.isNotEmpty) {
      final updatedList = List<MaintenanceTask>.from(_b.maintenance)
        ..add(MaintenanceTask(
          id: 'm_${DateTime.now().millisecondsSinceEpoch}',
          title: titleCtrl.text,
          frequency: freqCtrl.text,
          dueDate: dateCtrl.text,
        ));
      setState(() {
        _b = _b.copyWith(maintenance: updatedList);
      });
      widget.onUpdated(_b);
    }
  }

  void _addWarranty() async {
    final provCtrl = TextEditingController(text: 'Official Store');
    final dateCtrl = TextEditingController(text: DateFormat('yyyy-MM-dd').format(DateTime.now().add(const Duration(days: 365))));

    final ok = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        title: const Text('Tambah Garansi'),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            TextField(controller: provCtrl, decoration: const InputDecoration(labelText: 'Penyedia Garansi (mis: Samsung Official)')),
            TextField(controller: dateCtrl, decoration: const InputDecoration(labelText: 'Berlaku Hingga (YYYY-MM-DD)')),
          ],
        ),
        actions: [
          TextButton(onPressed: () => Navigator.pop(ctx, false), child: const Text('Batal')),
          ElevatedButton(onPressed: () => Navigator.pop(ctx, true), child: const Text('Simpan')),
        ],
      ),
    );

    if (ok == true && provCtrl.text.isNotEmpty) {
      final updatedList = List<WarrantyItem>.from(_b.warranties)
        ..add(WarrantyItem(
          id: 'w_${DateTime.now().millisecondsSinceEpoch}',
          provider: provCtrl.text,
          expiryDate: dateCtrl.text,
        ));
      setState(() {
        _b = _b.copyWith(warranties: updatedList);
      });
      widget.onUpdated(_b);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Container(
      decoration: const BoxDecoration(
        color: AppColors.card,
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      padding: const EdgeInsets.all(20),
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Expanded(
                child: Text(
                  _b.name,
                  style: const TextStyle(fontSize: 18, fontWeight: FontWeight.w900),
                ),
              ),
              IconButton(icon: const Icon(Icons.close), onPressed: () => Navigator.pop(context)),
            ],
          ),
          TabBar(
            controller: _tabCtrl,
            labelColor: const Color(0xFF6366F1),
            unselectedLabelColor: AppColors.textMuted,
            indicatorColor: const Color(0xFF6366F1),
            tabs: const [
              Tab(text: 'Details'),
              Tab(text: 'Maintenance'),
              Tab(text: 'Warranty'),
            ],
          ),
          const SizedBox(height: 12),
          SizedBox(
            height: 320,
            child: TabBarView(
              controller: _tabCtrl,
              children: [
                // Details
                ListView(
                  children: [
                    _buildDetailRow('Category', _b.category),
                    _buildDetailRow('Brand', _b.brand.isNotEmpty ? _b.brand : '-'),
                    _buildDetailRow('Room', _b.room),
                    _buildDetailRow('Purchase Date', _b.purchaseDate.isNotEmpty ? _b.purchaseDate : '-'),
                    if (_b.purchasePrice > 0)
                      _buildDetailRow('Purchase Price', 'Rp ${NumberFormat('#,###').format(_b.purchasePrice)}'),
                    const SizedBox(height: 16),
                    Row(
                      children: [
                        Expanded(
                          child: OutlinedButton(
                            onPressed: widget.onDelete,
                            style: OutlinedButton.styleFrom(foregroundColor: AppColors.expense),
                            child: const Text('Hapus'),
                          ),
                        ),
                        const SizedBox(width: 8),
                        Expanded(
                          child: ElevatedButton(
                            onPressed: widget.onEdit,
                            style: ElevatedButton.styleFrom(backgroundColor: const Color(0xFF6366F1)),
                            child: const Text('Edit'),
                          ),
                        ),
                      ],
                    ),
                  ],
                ),

                // Maintenance
                ListView(
                  children: [
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        const Text('Maintenance Tasks', style: TextStyle(fontWeight: FontWeight.w800)),
                        TextButton.icon(
                          onPressed: _addMaintenance,
                          icon: const Icon(Icons.add, size: 16),
                          label: const Text('Add'),
                        ),
                      ],
                    ),
                    if (_b.maintenance.isEmpty)
                      const Padding(
                        padding: EdgeInsets.all(20),
                        child: Center(child: Text('No maintenance scheduled', style: TextStyle(color: AppColors.textMuted))),
                      )
                    else
                      ..._b.maintenance.map((m) => ListTile(
                            contentPadding: EdgeInsets.zero,
                            title: Text(m.title, style: const TextStyle(fontWeight: FontWeight.w700, fontSize: 13)),
                            subtitle: Text('${m.frequency} • Due: ${m.dueDate}', style: const TextStyle(fontSize: 11)),
                            trailing: Checkbox(
                              value: m.isDone,
                              onChanged: (val) {
                                setState(() => m.isDone = val ?? false);
                                widget.onUpdated(_b);
                              },
                            ),
                          )),
                  ],
                ),

                // Warranty
                ListView(
                  children: [
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        const Text('Warranty Tracking', style: TextStyle(fontWeight: FontWeight.w800)),
                        TextButton.icon(
                          onPressed: _addWarranty,
                          icon: const Icon(Icons.add, size: 16),
                          label: const Text('Add'),
                        ),
                      ],
                    ),
                    if (_b.warranties.isEmpty)
                      const Padding(
                        padding: EdgeInsets.all(20),
                        child: Center(child: Text('No warranty info', style: TextStyle(color: AppColors.textMuted))),
                      )
                    else
                      ..._b.warranties.map((w) => ListTile(
                            contentPadding: EdgeInsets.zero,
                            title: Text(w.provider, style: const TextStyle(fontWeight: FontWeight.w700, fontSize: 13)),
                            subtitle: Text('Expires: ${w.expiryDate}', style: const TextStyle(fontSize: 11)),
                            trailing: Chip(
                              label: Text(w.status, style: const TextStyle(fontSize: 10, color: Colors.white)),
                              backgroundColor: const Color(0xFF059669),
                            ),
                          )),
                  ],
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildDetailRow(String label, String value) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 6),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Text(label, style: const TextStyle(fontSize: 12.5, color: AppColors.textMuted)),
          Text(value, style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w700, color: AppColors.textPrimary)),
        ],
      ),
    );
  }
}

// ── SHEET: ADD / EDIT ASSET FORM ──
class _AssetFormSheet extends StatefulWidget {
  final Barang? barang;
  final ValueChanged<Barang> onSaved;

  const _AssetFormSheet({this.barang, required this.onSaved});

  @override
  State<_AssetFormSheet> createState() => _AssetFormSheetState();
}

class _AssetFormSheetState extends State<_AssetFormSheet> {
  final _nameCtrl = TextEditingController();
  final _catCtrl = TextEditingController(text: 'Appliance');
  final _brandCtrl = TextEditingController();
  final _priceCtrl = TextEditingController();
  final _dateCtrl = TextEditingController();
  String _selectedRoom = 'Dapur';
  String? _photoPath;

  final List<String> _rooms = [
    'Dapur',
    'Ruang Tamu',
    'Kamar Tidur',
    'Garasi',
    'Exterior',
    'Ruang Kerja',
    'Kamar Mandi',
    'Gudang',
    'Lainnya',
  ];

  @override
  void initState() {
    super.initState();
    if (widget.barang != null) {
      final b = widget.barang!;
      _nameCtrl.text = b.name;
      _catCtrl.text = b.category;
      _brandCtrl.text = b.brand;
      _priceCtrl.text = b.purchasePrice > 0 ? b.purchasePrice.toStringAsFixed(0) : '';
      _dateCtrl.text = b.purchaseDate;
      _selectedRoom = _rooms.contains(b.room) ? b.room : _rooms.first;
      _photoPath = b.itemPhoto;
    } else {
      _dateCtrl.text = DateFormat('yyyy-MM-dd').format(DateTime.now());
    }
  }

  Future<void> _pickPhoto() async {
    final picker = ImagePicker();
    final file = await picker.pickImage(source: ImageSource.gallery);
    if (file != null) {
      setState(() => _photoPath = file.path);
    }
  }

  void _submit() {
    if (_nameCtrl.text.trim().isEmpty) return;

    final barang = Barang(
      id: widget.barang?.id ?? 'brg_${DateTime.now().millisecondsSinceEpoch}',
      name: _nameCtrl.text.trim(),
      location: _selectedRoom,
      room: _selectedRoom,
      category: _catCtrl.text.trim(),
      brand: _brandCtrl.text.trim(),
      purchaseDate: _dateCtrl.text.trim(),
      purchasePrice: double.tryParse(_priceCtrl.text.trim()) ?? 0,
      itemPhoto: _photoPath,
      maintenance: widget.barang?.maintenance ?? [],
      warranties: widget.barang?.warranties ?? [],
      createdAt: widget.barang?.createdAt ?? DateTime.now(),
      updatedAt: DateTime.now(),
    );

    widget.onSaved(barang);
    Navigator.pop(context);
  }

  @override
  Widget build(BuildContext context) {
    return Container(
      decoration: const BoxDecoration(
        color: AppColors.card,
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      padding: EdgeInsets.fromLTRB(20, 20, 20, MediaQuery.of(context).viewInsets.bottom + 20),
      child: SingleChildScrollView(
        child: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Text(
                  widget.barang == null ? 'Tambah Aset Rumah' : 'Edit Aset',
                  style: const TextStyle(fontSize: 18, fontWeight: FontWeight.w900),
                ),
                IconButton(icon: const Icon(Icons.close), onPressed: () => Navigator.pop(context)),
              ],
            ),
            const SizedBox(height: 12),
            TextField(
              controller: _nameCtrl,
              decoration: const InputDecoration(labelText: 'Nama Aset (contoh: Kulkas Inverter) *'),
            ),
            const SizedBox(height: 8),
            DropdownButtonFormField<String>(
              initialValue: _selectedRoom,
              decoration: const InputDecoration(labelText: 'Ruangan *'),
              items: _rooms.map((r) => DropdownMenuItem(value: r, child: Text(r))).toList(),
              onChanged: (val) => setState(() => _selectedRoom = val ?? _selectedRoom),
            ),
            const SizedBox(height: 8),
            Row(
              children: [
                Expanded(
                  child: TextField(controller: _catCtrl, decoration: const InputDecoration(labelText: 'Kategori')),
                ),
                const SizedBox(width: 8),
                Expanded(
                  child: TextField(controller: _brandCtrl, decoration: const InputDecoration(labelText: 'Merek (Brand)')),
                ),
              ],
            ),
            const SizedBox(height: 8),
            Row(
              children: [
                Expanded(
                  child: TextField(controller: _dateCtrl, decoration: const InputDecoration(labelText: 'Tanggal Beli')),
                ),
                const SizedBox(width: 8),
                Expanded(
                  child: TextField(controller: _priceCtrl, decoration: const InputDecoration(labelText: 'Harga (Rp)'), keyboardType: TextInputType.number),
                ),
              ],
            ),
            const SizedBox(height: 14),
            Row(
              children: [
                ElevatedButton.icon(
                  onPressed: _pickPhoto,
                  icon: const Icon(Icons.camera_alt_rounded, size: 16),
                  label: const Text('Foto Aset'),
                  style: ElevatedButton.styleFrom(backgroundColor: AppColors.bg, foregroundColor: AppColors.textPrimary),
                ),
                const SizedBox(width: 10),
                if (_photoPath != null)
                  const Text('✓ Foto terpilih', style: TextStyle(color: Color(0xFF059669), fontSize: 11, fontWeight: FontWeight.w700)),
              ],
            ),
            const SizedBox(height: 16),
            SizedBox(
              width: double.infinity,
              child: ElevatedButton(
                onPressed: _submit,
                style: ElevatedButton.styleFrom(
                  backgroundColor: const Color(0xFF6366F1),
                  padding: const EdgeInsets.symmetric(vertical: 12),
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                ),
                child: const Text('Simpan Aset', style: TextStyle(fontSize: 14, fontWeight: FontWeight.w800, color: Colors.white)),
              ),
            ),
          ],
        ),
      ),
    );
  }
}
