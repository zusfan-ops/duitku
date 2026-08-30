import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:url_launcher/url_launcher.dart';

import '../../models/emergency_contact.dart';
import '../../services/api_service.dart';
import '../../theme.dart';

class EmergencyScreen extends StatefulWidget {
  const EmergencyScreen({super.key});

  @override
  State<EmergencyScreen> createState() => _EmergencyScreenState();
}

class _EmergencyScreenState extends State<EmergencyScreen> {
  List<EmergencyContact> _contacts = EmergencyContact.defaults;
  List<String> _categories = ['Semua', 'Derek Tol', 'Medis', 'Keamanan', 'Penyelamatan', 'Utilitas'];
  String _selectedCategory = 'Semua';
  String _searchQuery = '';

  @override
  void initState() {
    super.initState();
    _loadDirectory();
  }

  Future<void> _loadDirectory() async {
    try {
      final res = await ApiService.instance.getEmergencyDirectory();
      final list = (res['directory'] as List<dynamic>?) ?? [];
      final cats = (res['categories'] as List<dynamic>?) ?? [];

      if (list.isNotEmpty && mounted) {
        setState(() {
          _contacts = list.map((e) => EmergencyContact.fromJson(e as Map<String, dynamic>)).toList();
          if (cats.isNotEmpty) {
            _categories = cats.map((e) => e.toString()).toList();
          }
        });
      }
    } catch (_) {}
  }

  Future<void> _callNumber(String number) async {
    final uri = Uri.parse('tel:$number');
    try {
      if (await canLaunchUrl(uri)) {
        await launchUrl(uri, mode: LaunchMode.externalApplication);
      } else {
        await launchUrl(uri);
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('Tidak dapat membuka panggilan untuk nomor: $number'),
            backgroundColor: Colors.red,
          ),
        );
      }
    }
  }

  void _copyNumber(String number, String name) {
    Clipboard.setData(ClipboardData(text: number));
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Row(
          children: [
            const Icon(Icons.check_circle_rounded, color: Colors.white, size: 18),
            const SizedBox(width: 8),
            Expanded(child: Text('Nomor $name ($number) berhasil disalin')),
          ],
        ),
        backgroundColor: const Color(0xFF0F172A),
        behavior: SnackBarBehavior.floating,
        duration: const Duration(seconds: 2),
      ),
    );
  }

  Future<void> _openMapSearch(String query) async {
    final uri = Uri.parse('https://www.google.com/maps/search/${Uri.encodeComponent(query)}');
    try {
      await launchUrl(uri, mode: LaunchMode.externalApplication);
    } catch (_) {
      await launchUrl(uri);
    }
  }

  void _shareSos() {
    showModalBottomSheet(
      context: context,
      backgroundColor: Colors.transparent,
      builder: (ctx) => Container(
        padding: const EdgeInsets.all(20),
        decoration: const BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
        ),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                const Row(
                  children: [
                    Text('🚨', style: TextStyle(fontSize: 20)),
                    SizedBox(width: 8),
                    Text(
                      'Tindakan Darurat SOS',
                      style: TextStyle(fontSize: 16, fontWeight: FontWeight.w900, color: Color(0xFFDC2626)),
                    ),
                  ],
                ),
                IconButton(
                  icon: const Icon(Icons.close_rounded, size: 20),
                  onPressed: () => Navigator.pop(ctx),
                ),
              ],
            ),
            const SizedBox(height: 6),
            const Text(
              'Pilih jalur darurat yang ingin Anda gunakan segera:',
              style: TextStyle(fontSize: 12, color: Color(0xFF64748B)),
            ),
            const SizedBox(height: 16),
            ListTile(
              leading: Container(
                padding: const EdgeInsets.all(10),
                decoration: BoxDecoration(
                  color: const Color(0xFFDCFCE7),
                  borderRadius: BorderRadius.circular(12),
                ),
                child: const Text('💬', style: TextStyle(fontSize: 18)),
              ),
              title: const Text('Kirim SOS via WhatsApp', style: TextStyle(fontWeight: FontWeight.w800, fontSize: 14)),
              subtitle: const Text('Kirim pesan gawat darurat ke kontak/grup', style: TextStyle(fontSize: 11)),
              shape: RoundedRectangleBorder(
                borderRadius: BorderRadius.circular(14),
                side: const BorderSide(color: Color(0xFFE2E8F0)),
              ),
              onTap: () {
                Navigator.pop(ctx);
                _sendWhatsAppSos();
              },
            ),
            const SizedBox(height: 8),
            ListTile(
              leading: Container(
                padding: const EdgeInsets.all(10),
                decoration: BoxDecoration(
                  color: const Color(0xFFFEE2E2),
                  borderRadius: BorderRadius.circular(12),
                ),
                child: const Text('📞', style: TextStyle(fontSize: 18)),
              ),
              title: const Text('Panggil 112 (Bebas Pulsa)', style: TextStyle(fontWeight: FontWeight.w800, fontSize: 14, color: Color(0xFFDC2626))),
              subtitle: const Text('Panggilan darurat terpadu nasional 24 jam', style: TextStyle(fontSize: 11)),
              shape: RoundedRectangleBorder(
                borderRadius: BorderRadius.circular(14),
                side: const BorderSide(color: Color(0xFFFCA5A5)),
              ),
              onTap: () {
                Navigator.pop(ctx);
                _callNumber('112');
              },
            ),
            const SizedBox(height: 8),
            ListTile(
              leading: Container(
                padding: const EdgeInsets.all(10),
                decoration: BoxDecoration(
                  color: const Color(0xFFE0E7FF),
                  borderRadius: BorderRadius.circular(12),
                ),
                child: const Text('🚗', style: TextStyle(fontSize: 18)),
              ),
              title: const Text('Derek Tol Jasa Marga (14080)', style: TextStyle(fontWeight: FontWeight.w800, fontSize: 14)),
              subtitle: const Text('Bantuan derek resmi jalan tol 24 jam', style: TextStyle(fontSize: 11)),
              shape: RoundedRectangleBorder(
                borderRadius: BorderRadius.circular(14),
                side: const BorderSide(color: Color(0xFFE2E8F0)),
              ),
              onTap: () {
                Navigator.pop(ctx);
                _callNumber('14080');
              },
            ),
            const SizedBox(height: 8),
            ListTile(
              leading: Container(
                padding: const EdgeInsets.all(10),
                decoration: BoxDecoration(
                  color: const Color(0xFFF1F5F9),
                  borderRadius: BorderRadius.circular(12),
                ),
                child: const Text('📋', style: TextStyle(fontSize: 18)),
              ),
              title: const Text('Salin Format Pesan SOS', style: TextStyle(fontWeight: FontWeight.w800, fontSize: 14)),
              subtitle: const Text('Salin teks darurat ke clipboard', style: TextStyle(fontSize: 11)),
              shape: RoundedRectangleBorder(
                borderRadius: BorderRadius.circular(14),
                side: const BorderSide(color: Color(0xFFE2E8F0)),
              ),
              onTap: () {
                Navigator.pop(ctx);
                Clipboard.setData(const ClipboardData(
                  text: '🚨 *SOS! PERMINTAAN BANTUAN DARURAT*\nSaya membutuhkan bantuan darurat segera!\n(Dikirim melalui Layanan Darurat DuitKu)',
                ));
                ScaffoldMessenger.of(context).showSnackBar(
                  const SnackBar(
                    content: Text('Format pesan SOS disalin ke clipboard!'),
                    backgroundColor: Color(0xFF0F172A),
                    behavior: SnackBarBehavior.floating,
                  ),
                );
              },
            ),
            const SizedBox(height: 12),
          ],
        ),
      ),
    );
  }

  Future<void> _sendWhatsAppSos() async {
    final text = Uri.encodeComponent(
      '🚨 *SOS! PERMINTAAN BANTUAN DARURAT*\nSaya membutuhkan bantuan darurat segera!\n(Dikirim melalui Layanan Darurat DuitKu)',
    );
    final waUri = Uri.parse('https://api.whatsapp.com/send?text=$text');
    try {
      final launched = await launchUrl(waUri, mode: LaunchMode.externalApplication);
      if (!launched) {
        await launchUrl(waUri, mode: LaunchMode.platformDefault);
      }
    } catch (_) {
      try {
        await launchUrl(Uri.parse('https://wa.me/?text=$text'), mode: LaunchMode.externalApplication);
      } catch (e) {
        if (!mounted) return;
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text('Gagal membuka WhatsApp. Salin teks pesan darurat.'),
            backgroundColor: Color(0xFFDC2626),
          ),
        );
      }
    }
  }

  List<EmergencyContact> get _filteredContacts {
    return _contacts.where((c) {
      final matchCat = _selectedCategory == 'Semua' || c.category == _selectedCategory;
      final q = _searchQuery.toLowerCase().trim();
      final matchQuery = q.isEmpty ||
          c.name.toLowerCase().contains(q) ||
          c.number.contains(q) ||
          c.description.toLowerCase().contains(q) ||
          c.category.toLowerCase().contains(q);
      return matchCat && matchQuery;
    }).toList();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.bg,
      appBar: AppBar(
        title: const Text(
          'Layanan Darurat',
          style: TextStyle(fontWeight: FontWeight.w900, fontSize: 17),
        ),
        backgroundColor: Colors.white,
        foregroundColor: AppColors.textPrimary,
        elevation: 0,
        centerTitle: false,
        actions: [
          IconButton(
            icon: const Icon(Icons.share_location_rounded, color: Color(0xFFEF4444)),
            tooltip: 'Kirim SOS',
            onPressed: _shareSos,
          ),
        ],
      ),
      body: CustomScrollView(
        slivers: [
          // Top SOS Hero Card
          SliverToBoxAdapter(
            child: Padding(
              padding: const EdgeInsets.fromLTRB(16, 16, 16, 8),
              child: Container(
                decoration: BoxDecoration(
                  gradient: const LinearGradient(
                    colors: [Color(0xFFDC2626), Color(0xFF991B1B)],
                    begin: Alignment.topLeft,
                    end: Alignment.bottomRight,
                  ),
                  borderRadius: BorderRadius.circular(20),
                  boxShadow: [
                    BoxShadow(
                      color: const Color(0xFFDC2626).withValues(alpha: 0.35),
                      blurRadius: 16,
                      offset: const Offset(0, 6),
                    ),
                  ],
                ),
                padding: const EdgeInsets.all(20),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      children: [
                        Container(
                          padding: const EdgeInsets.all(8),
                          decoration: BoxDecoration(
                            color: Colors.white.withValues(alpha: 0.2),
                            borderRadius: BorderRadius.circular(12),
                          ),
                          child: const Text('🚨', style: TextStyle(fontSize: 22)),
                        ),
                        const SizedBox(width: 12),
                        const Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                'Panggilan Darurat Cepat',
                                style: TextStyle(
                                  fontSize: 16,
                                  fontWeight: FontWeight.w900,
                                  color: Colors.white,
                                ),
                              ),
                              Text(
                                'Bebas pulsa 24 jam seluruh Indonesia',
                                style: TextStyle(
                                  fontSize: 11.5,
                                  color: Colors.white70,
                                ),
                              ),
                            ],
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 16),
                    Row(
                      children: [
                        Expanded(
                          child: ElevatedButton.icon(
                            onPressed: () => _callNumber('112'),
                            icon: const Icon(Icons.phone_in_talk_rounded, color: Color(0xFFDC2626), size: 18),
                            label: const Text(
                              'Panggil 112',
                              style: TextStyle(
                                fontWeight: FontWeight.w900,
                                color: Color(0xFFDC2626),
                                fontSize: 13.5,
                              ),
                            ),
                            style: ElevatedButton.styleFrom(
                              backgroundColor: Colors.white,
                              padding: const EdgeInsets.symmetric(vertical: 12),
                              shape: RoundedRectangleBorder(
                                borderRadius: BorderRadius.circular(12),
                              ),
                              elevation: 2,
                            ),
                          ),
                        ),
                        const SizedBox(width: 10),
                        Expanded(
                          child: OutlinedButton.icon(
                            onPressed: _shareSos,
                            icon: const Icon(Icons.emergency_share_rounded, color: Colors.white, size: 18),
                            label: const Text(
                              'Kirim SOS',
                              style: TextStyle(
                                fontWeight: FontWeight.w800,
                                color: Colors.white,
                                fontSize: 13,
                              ),
                            ),
                            style: OutlinedButton.styleFrom(
                              side: const BorderSide(color: Colors.white70, width: 1.2),
                              padding: const EdgeInsets.symmetric(vertical: 12),
                              shape: RoundedRectangleBorder(
                                borderRadius: BorderRadius.circular(12),
                              ),
                            ),
                          ),
                        ),
                      ],
                    ),
                  ],
                ),
              ),
            ),
          ),

          // Search Bar
          SliverToBoxAdapter(
            child: Padding(
              padding: const EdgeInsets.fromLTRB(16, 8, 16, 8),
              child: TextField(
                onChanged: (val) => setState(() => _searchQuery = val),
                decoration: InputDecoration(
                  hintText: 'Cari layanan (Derek tol, 14080, Damkar, Polisi)...',
                  hintStyle: TextStyle(fontSize: 13, color: Colors.grey.shade500),
                  prefixIcon: const Icon(Icons.search_rounded, color: AppColors.textSecondary, size: 20),
                  filled: true,
                  fillColor: Colors.white,
                  contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
                  border: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(14),
                    borderSide: const BorderSide(color: AppColors.border),
                  ),
                  enabledBorder: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(14),
                    borderSide: const BorderSide(color: AppColors.border),
                  ),
                  focusedBorder: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(14),
                    borderSide: const BorderSide(color: Color(0xFFEF4444), width: 1.5),
                  ),
                ),
              ),
            ),
          ),

          // Category Chips
          SliverToBoxAdapter(
            child: SizedBox(
              height: 44,
              child: ListView.builder(
                scrollDirection: Axis.horizontal,
                padding: const EdgeInsets.symmetric(horizontal: 16),
                itemCount: _categories.length,
                itemBuilder: (context, idx) {
                  final cat = _categories[idx];
                  final isSelected = cat == _selectedCategory;
                  return Padding(
                    padding: const EdgeInsets.only(right: 8),
                    child: ChoiceChip(
                      label: Text(
                        cat,
                        style: TextStyle(
                          fontSize: 12,
                          fontWeight: isSelected ? FontWeight.w800 : FontWeight.w600,
                          color: isSelected ? Colors.white : AppColors.textSecondary,
                        ),
                      ),
                      selected: isSelected,
                      selectedColor: const Color(0xFFEF4444),
                      backgroundColor: Colors.white,
                      side: BorderSide(
                        color: isSelected ? const Color(0xFFEF4444) : AppColors.border,
                      ),
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(20),
                      ),
                      onSelected: (selected) {
                        if (selected) {
                          setState(() => _selectedCategory = cat);
                        }
                      },
                    ),
                  );
                },
              ),
            ),
          ),

          // Section Title
          SliverToBoxAdapter(
            child: Padding(
              padding: const EdgeInsets.fromLTRB(16, 14, 16, 6),
              child: Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Text(
                    'DAFTAR KONTAK DARURAT (${_filteredContacts.length})',
                    style: const TextStyle(
                      fontSize: 11,
                      fontWeight: FontWeight.w800,
                      color: AppColors.textSecondary,
                      letterSpacing: 0.6,
                    ),
                  ),
                ],
              ),
            ),
          ),

          // Emergency Contact Cards
          _filteredContacts.isEmpty
              ? SliverToBoxAdapter(
                  child: Padding(
                    padding: const EdgeInsets.all(32),
                    child: Center(
                      child: Column(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          const Text('🔍', style: TextStyle(fontSize: 36)),
                          const SizedBox(height: 8),
                          const Text(
                            'Layanan tidak ditemukan',
                            style: TextStyle(fontWeight: FontWeight.w800, color: AppColors.textPrimary),
                          ),
                          const SizedBox(height: 4),
                          Text(
                            'Coba kata kunci lain atau pilih kategori Semua',
                            style: TextStyle(fontSize: 12, color: Colors.grey.shade500),
                          ),
                        ],
                      ),
                    ),
                  ),
                )
              : SliverPadding(
                  padding: const EdgeInsets.fromLTRB(16, 4, 16, 12),
                  sliver: SliverList(
                    delegate: SliverChildBuilderDelegate(
                      (context, index) {
                        final item = _filteredContacts[index];
                        return _buildEmergencyCard(item);
                      },
                      childCount: _filteredContacts.length,
                    ),
                  ),
                ),

          // Nearby Emergency Stations Box
          SliverToBoxAdapter(
            child: Padding(
              padding: const EdgeInsets.fromLTRB(16, 10, 16, 32),
              child: Container(
                decoration: BoxDecoration(
                  color: Colors.white,
                  borderRadius: BorderRadius.circular(18),
                  border: Border.all(color: AppColors.border),
                  boxShadow: const [
                    BoxShadow(
                      color: Color(0x06000000),
                      blurRadius: 8,
                      offset: Offset(0, 2),
                    ),
                  ],
                ),
                padding: const EdgeInsets.all(18),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Row(
                      children: [
                        Text('📍', style: TextStyle(fontSize: 20)),
                        SizedBox(width: 8),
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                'Pos & Fasilitas Darurat Terdekat',
                                style: TextStyle(
                                  fontSize: 14.5,
                                  fontWeight: FontWeight.w900,
                                  color: AppColors.textPrimary,
                                ),
                              ),
                              Text(
                                'Buka rute navigasi instan via Google Maps',
                                style: TextStyle(fontSize: 11.5, color: AppColors.textSecondary),
                              ),
                            ],
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 14),
                    Wrap(
                      spacing: 8,
                      runSpacing: 8,
                      children: [
                        _buildStationBtn('🏥 RS & UGD 24 Jam', 'Rumah Sakit UGD 24 Jam terdekat'),
                        _buildStationBtn('🚓 Kantor Polsek', 'Kantor Polisi Polsek terdekat'),
                        _buildStationBtn('🚒 Pos Damkar', 'Pos Pemadam Kebakaran terdekat'),
                        _buildStationBtn('🚗 Derek & Gerbang Tol', 'Gerbang Tol Posko Derek terdekat'),
                        _buildStationBtn('⛽ SPBU / Bensin', 'SPBU Pom Bensin terdekat'),
                        _buildStationBtn('🔧 Tambal Ban 24 Jam', 'Tambal Ban Bengkel 24 Jam terdekat'),
                      ],
                    ),
                  ],
                ),
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildEmergencyCard(EmergencyContact item) {
    return Container(
      margin: const EdgeInsets.only(bottom: 12),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(18),
        border: Border.all(
          color: item.color.withValues(alpha: 0.25),
          width: 1.2,
        ),
        boxShadow: const [
          BoxShadow(
            color: Color(0x06000000),
            blurRadius: 6,
            offset: Offset(0, 2),
          ),
        ],
      ),
      padding: const EdgeInsets.all(15),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Container(
                width: 44,
                height: 44,
                decoration: BoxDecoration(
                  color: item.color.withValues(alpha: 0.12),
                  borderRadius: BorderRadius.circular(12),
                  border: Border.all(color: item.color.withValues(alpha: 0.25)),
                ),
                child: Center(
                  child: Text(item.icon, style: const TextStyle(fontSize: 22)),
                ),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      item.name,
                      style: const TextStyle(
                        fontSize: 14,
                        fontWeight: FontWeight.w900,
                        color: AppColors.textPrimary,
                      ),
                    ),
                    const SizedBox(height: 4),
                    Row(
                      children: [
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 2),
                          decoration: BoxDecoration(
                            color: AppColors.bg,
                            borderRadius: BorderRadius.circular(6),
                            border: Border.all(color: AppColors.border),
                          ),
                          child: Text(
                            item.category,
                            style: const TextStyle(
                              fontSize: 10,
                              fontWeight: FontWeight.w700,
                              color: AppColors.textSecondary,
                            ),
                          ),
                        ),
                        const SizedBox(width: 6),
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 2),
                          decoration: BoxDecoration(
                            color: item.isTollFree
                                ? const Color(0xFFDCFCE7)
                                : const Color(0xFFFEF3C7),
                            borderRadius: BorderRadius.circular(6),
                          ),
                          child: Text(
                            item.isTollFree ? 'Bebas Pulsa' : '24 Jam',
                            style: TextStyle(
                              fontSize: 10,
                              fontWeight: FontWeight.w800,
                              color: item.isTollFree
                                  ? const Color(0xFF16A34A)
                                  : const Color(0xFFD97706),
                            ),
                          ),
                        ),
                      ],
                    ),
                  ],
                ),
              ),
            ],
          ),
          const SizedBox(height: 10),
          Text(
            item.description,
            style: const TextStyle(
              fontSize: 12,
              color: AppColors.textSecondary,
              height: 1.4,
            ),
          ),
          const SizedBox(height: 12),
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
            decoration: BoxDecoration(
              color: AppColors.bg,
              borderRadius: BorderRadius.circular(10),
              border: Border.all(color: AppColors.border, style: BorderStyle.solid),
            ),
            child: Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Text(
                  item.number,
                  style: TextStyle(
                    fontSize: 16,
                    fontWeight: FontWeight.w900,
                    color: item.color,
                    letterSpacing: 0.5,
                  ),
                ),
                InkWell(
                  onTap: () => _copyNumber(item.number, item.name),
                  borderRadius: BorderRadius.circular(6),
                  child: const Padding(
                    padding: EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                    child: Row(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        Icon(Icons.copy_rounded, size: 14, color: AppColors.textSecondary),
                        SizedBox(width: 4),
                        Text(
                          'Salin',
                          style: TextStyle(fontSize: 11.5, color: AppColors.textSecondary, fontWeight: FontWeight.w700),
                        ),
                      ],
                    ),
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(height: 10),
          SizedBox(
            width: double.infinity,
            child: ElevatedButton.icon(
              onPressed: () => _callNumber(item.number),
              icon: const Icon(Icons.phone_in_talk_rounded, color: Colors.white, size: 17),
              label: const Text(
                'Panggil Sekarang',
                style: TextStyle(fontWeight: FontWeight.w900, color: Colors.white, fontSize: 13),
              ),
              style: ElevatedButton.styleFrom(
                backgroundColor: item.color,
                padding: const EdgeInsets.symmetric(vertical: 11),
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(12),
                ),
                elevation: 1,
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildStationBtn(String label, String query) {
    return InkWell(
      onTap: () => _openMapSearch(query),
      borderRadius: BorderRadius.circular(12),
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
        decoration: BoxDecoration(
          color: AppColors.bg,
          borderRadius: BorderRadius.circular(12),
          border: Border.all(color: AppColors.border),
        ),
        child: Text(
          label,
          style: const TextStyle(
            fontSize: 12,
            fontWeight: FontWeight.w700,
            color: AppColors.textPrimary,
          ),
        ),
      ),
    );
  }
}
