import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import '../../models/neighborhood.dart';
import '../../services/api_service.dart';
import '../../theme.dart';
import 'tool_sharing_screen.dart';
import 'errand_screen.dart';

class NeighborhoodScreen extends StatefulWidget {
  const NeighborhoodScreen({super.key});

  @override
  State<NeighborhoodScreen> createState() => _NeighborhoodScreenState();
}

class _NeighborhoodScreenState extends State<NeighborhoodScreen> {
  bool _loading = true;
  bool _joined = false;
  Neighborhood? _neighborhood;
  List<Resident> _residents = [];
  List<Resident> _pendingResidents = [];
  bool _isRtAdmin = false;

  final TextEditingController _codeCtrl = TextEditingController();
  final TextEditingController _houseCtrl = TextEditingController();
  String _residenceStatus = 'permanent';

  @override
  void initState() {
    super.initState();
    _loadData();
  }

  Future<void> _loadData() async {
    setState(() => _loading = true);
    try {
      final res = await ApiService.instance.get('neighborhood');
      if (res['success'] == true) {
        _joined = res['joined'] == true;
        if (_joined && res['neighborhood'] != null) {
          _neighborhood = Neighborhood.fromJson(res['neighborhood'] as Map<String, dynamic>);
          _residents = (res['residents'] as List? ?? [])
              .map((r) => Resident.fromJson(r as Map<String, dynamic>))
              .toList();
          _pendingResidents = (res['pending_residents'] as List? ?? [])
              .map((r) => Resident.fromJson(r as Map<String, dynamic>))
              .toList();
          _isRtAdmin = res['is_rt_admin'] == true;
        }
      }
    } catch (_) {}
    if (mounted) setState(() => _loading = false);
  }

  Future<void> _joinRt() async {
    final code = _codeCtrl.text.trim();
    final house = _houseCtrl.text.trim();
    if (code.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Kode unik RT wajib diisi.')));
      return;
    }

    try {
      final res = await ApiService.instance.post('neighborhood/join', {
        'unique_code': code,
        'residence_status': _residenceStatus,
        'house_number': house,
      });

      if (!mounted) return;
      if (res['success'] == true) {
        ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(res['message']?.toString() ?? 'Berhasil bergabung.')));
        _loadData();
      } else {
        ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(res['message']?.toString() ?? 'Gagal bergabung.')));
      }
    } catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Error: $e')));
    }
  }

  Future<void> _verifyResident(int userId, String action) async {
    try {
      final res = await ApiService.instance.post('neighborhood/resident/verify', {
        'target_user_id': userId,
        'action': action,
      });
      if (res['success'] == true) {
        _loadData();
      }
    } catch (_) {}
  }

  void _showCreateRtDialog() {
    final nameCtrl = TextEditingController();
    final rtCtrl = TextEditingController();
    final rwCtrl = TextEditingController();
    final subdistrictCtrl = TextEditingController();
    final districtCtrl = TextEditingController();
    final cityCtrl = TextEditingController();
    final provinceCtrl = TextEditingController();

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
              const Text('Daftarkan RT Baru', textAlign: TextAlign.center, style: TextStyle(fontSize: 17, fontWeight: FontWeight.w900)),
              const SizedBox(height: 16),
              TextField(controller: nameCtrl, decoration: const InputDecoration(labelText: 'Nama RT / Perumahan', hintText: 'Misal: RT 04 Griya Asri')),
              const SizedBox(height: 10),
              Row(
                children: [
                  Expanded(child: TextField(controller: rtCtrl, decoration: const InputDecoration(labelText: 'RT', hintText: '04'))),
                  const SizedBox(width: 10),
                  Expanded(child: TextField(controller: rwCtrl, decoration: const InputDecoration(labelText: 'RW', hintText: '02'))),
                ],
              ),
              const SizedBox(height: 10),
              Row(
                children: [
                  Expanded(child: TextField(controller: subdistrictCtrl, decoration: const InputDecoration(labelText: 'Kelurahan'))),
                  const SizedBox(width: 10),
                  Expanded(child: TextField(controller: districtCtrl, decoration: const InputDecoration(labelText: 'Kecamatan'))),
                ],
              ),
              const SizedBox(height: 10),
              Row(
                children: [
                  Expanded(child: TextField(controller: cityCtrl, decoration: const InputDecoration(labelText: 'Kota / Kab'))),
                  const SizedBox(width: 10),
                  Expanded(child: TextField(controller: provinceCtrl, decoration: const InputDecoration(labelText: 'Provinsi'))),
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
                    final res = await ApiService.instance.post('neighborhood/store', {
                      'name': nameCtrl.text.trim(),
                      'rt': rtCtrl.text.trim(),
                      'rw': rwCtrl.text.trim(),
                      'subdistrict': subdistrictCtrl.text.trim(),
                      'district': districtCtrl.text.trim(),
                      'city': cityCtrl.text.trim(),
                      'province': provinceCtrl.text.trim(),
                    });
                    if (res['success'] == true) {
                      _loadData();
                    }
                  } catch (_) {}
                },
                child: const Text('Daftarkan RT', style: TextStyle(fontWeight: FontWeight.w800)),
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
        title: const Text('Komunitas & Sistem RT', style: TextStyle(fontWeight: FontWeight.w900, fontSize: 18)),
      ),
      body: _loading
          ? const Center(child: CircularProgressIndicator())
          : RefreshIndicator(
              onRefresh: _loadData,
              child: ListView(
                padding: const EdgeInsets.fromLTRB(16, 8, 16, 100),
                children: [
                  if (!_joined) ...[
                    _buildJoinView(),
                  ] else ...[
                    _buildNeighborhoodView(),
                  ],
                ],
              ),
            ),
    );
  }

  Widget _buildJoinView() {
    return Column(
      children: [
        // Hero Join Card
        Container(
          padding: const EdgeInsets.all(22),
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
          child: Column(
            children: [
              Container(
                width: 64,
                height: 64,
                decoration: BoxDecoration(
                  color: Colors.white.withValues(alpha: 0.2),
                  borderRadius: BorderRadius.circular(20),
                ),
                child: const Center(child: Text('🏘️', style: TextStyle(fontSize: 32))),
              ),
              const SizedBox(height: 12),
              const Text(
                'Gabung Komunitas RT',
                style: TextStyle(color: Colors.white, fontSize: 20, fontWeight: FontWeight.w900),
              ),
              const SizedBox(height: 4),
              Text(
                'Hubungkan akun Anda dengan lingkungan RT setempat untuk menikmati pinjam alat bersama dan layanan titip belanja tetangga.',
                style: TextStyle(color: Colors.white.withValues(alpha: 0.9), fontSize: 12.5, height: 1.45),
                textAlign: TextAlign.center,
              ),
            ],
          ),
        ),
        const SizedBox(height: 16),

        // Join Form Card
        Container(
          padding: const EdgeInsets.all(20),
          decoration: BoxDecoration(
            color: AppColors.card,
            borderRadius: BorderRadius.circular(22),
            border: Border.all(color: AppColors.border, width: 1.5),
            boxShadow: AppColors.cardShadow,
          ),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              const Text('🔑 KODE UNIK RT', style: TextStyle(fontSize: 11, fontWeight: FontWeight.w800, color: AppColors.textMuted, letterSpacing: 0.5)),
              const SizedBox(height: 6),
              TextField(
                controller: _codeCtrl,
                textCapitalization: TextCapitalization.characters,
                style: const TextStyle(fontWeight: FontWeight.w800, letterSpacing: 1.2, fontSize: 14),
                decoration: InputDecoration(
                  hintText: 'Misal: RT04-RW02-GRIYA-2026',
                  prefixIcon: const Icon(Icons.tag_rounded, size: 20, color: AppColors.textMuted),
                  filled: true,
                  fillColor: AppColors.bg,
                  border: OutlineInputBorder(borderRadius: BorderRadius.circular(14), borderSide: const BorderSide(color: AppColors.border)),
                  enabledBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(14), borderSide: const BorderSide(color: AppColors.border)),
                ),
              ),
              const SizedBox(height: 14),

              const Text('🏠 STATUS TEMPAT TINGGAL', style: TextStyle(fontSize: 11, fontWeight: FontWeight.w800, color: AppColors.textMuted, letterSpacing: 0.5)),
              const SizedBox(height: 6),
              Row(
                children: [
                  Expanded(
                    child: _buildRadioOption(
                      title: 'Warga Tetap',
                      subtitle: 'KTP di RT ini',
                      icon: '🏠',
                      value: 'permanent',
                    ),
                  ),
                  const SizedBox(width: 8),
                  Expanded(
                    child: _buildRadioOption(
                      title: 'Domisili',
                      subtitle: 'Kontrak / Kost',
                      icon: '🏢',
                      value: 'temporary',
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 14),

              const Text('📍 NOMOR RUMAH / BLOK', style: TextStyle(fontSize: 11, fontWeight: FontWeight.w800, color: AppColors.textMuted, letterSpacing: 0.5)),
              const SizedBox(height: 6),
              TextField(
                controller: _houseCtrl,
                style: const TextStyle(fontSize: 13.5, fontWeight: FontWeight.w600),
                decoration: InputDecoration(
                  hintText: 'Misal: Blok B No. 12 / Jl. Mawar No. 4',
                  prefixIcon: const Icon(Icons.home_outlined, size: 20, color: AppColors.textMuted),
                  filled: true,
                  fillColor: AppColors.bg,
                  border: OutlineInputBorder(borderRadius: BorderRadius.circular(14), borderSide: const BorderSide(color: AppColors.border)),
                  enabledBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(14), borderSide: const BorderSide(color: AppColors.border)),
                ),
              ),
              const SizedBox(height: 20),

              ElevatedButton(
                style: ElevatedButton.styleFrom(
                  backgroundColor: AppColors.primary,
                  foregroundColor: Colors.white,
                  minimumSize: const Size.fromHeight(50),
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(30)),
                  elevation: 0,
                ),
                onPressed: _joinRt,
                child: const Text('Bergabung Sekarang →', style: TextStyle(fontWeight: FontWeight.w800, fontSize: 14)),
              ),
            ],
          ),
        ),
        const SizedBox(height: 16),

        // Secondary Create RT Button Card
        Container(
          padding: const EdgeInsets.all(18),
          decoration: BoxDecoration(
            color: AppColors.card,
            borderRadius: BorderRadius.circular(20),
            border: Border.all(color: AppColors.border),
          ),
          child: Row(
            children: [
              Container(
                padding: const EdgeInsets.all(10),
                decoration: BoxDecoration(
                  color: AppColors.primary.withValues(alpha: 0.1),
                  borderRadius: BorderRadius.circular(12),
                ),
                child: const Text('👑', style: TextStyle(fontSize: 20)),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: const [
                    Text('Anda Pengurus / Ketua RT?', style: TextStyle(fontWeight: FontWeight.w800, fontSize: 13)),
                    Text('Inisiasi lingkungan RT baru untuk warga Anda.', style: TextStyle(fontSize: 11.5, color: AppColors.textMuted)),
                  ],
                ),
              ),
              OutlinedButton(
                style: OutlinedButton.styleFrom(
                  foregroundColor: AppColors.primary,
                  side: const BorderSide(color: AppColors.primary),
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
                  padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                ),
                onPressed: _showCreateRtDialog,
                child: const Text('Daftar RT', style: TextStyle(fontSize: 11.5, fontWeight: FontWeight.w800)),
              ),
            ],
          ),
        ),
      ],
    );
  }

  Widget _buildRadioOption({required String title, required String subtitle, required String icon, required String value}) {
    final isSelected = _residenceStatus == value;
    return Material(
      color: isSelected ? AppColors.primary.withValues(alpha: 0.08) : AppColors.bg,
      borderRadius: BorderRadius.circular(14),
      child: InkWell(
        borderRadius: BorderRadius.circular(14),
        onTap: () => setState(() => _residenceStatus = value),
        child: Container(
          padding: const EdgeInsets.all(12),
          decoration: BoxDecoration(
            borderRadius: BorderRadius.circular(14),
            border: Border.all(color: isSelected ? AppColors.primary : AppColors.border, width: isSelected ? 1.5 : 1.0),
          ),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Text(icon, style: const TextStyle(fontSize: 16)),
                  Container(
                    width: 16,
                    height: 16,
                    decoration: BoxDecoration(
                      shape: BoxShape.circle,
                      border: Border.all(color: isSelected ? AppColors.primary : AppColors.border, width: 1.5),
                      color: isSelected ? AppColors.primary : Colors.transparent,
                    ),
                    child: isSelected ? const Icon(Icons.check, size: 10, color: Colors.white) : null,
                  ),
                ],
              ),
              const SizedBox(height: 6),
              Text(title, style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 12.5)),
              Text(subtitle, style: const TextStyle(fontSize: 10.5, color: AppColors.textMuted)),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildNeighborhoodView() {
    final rt = _neighborhood!;
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        // Hero Card
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
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                    decoration: BoxDecoration(
                      color: Colors.white.withValues(alpha: 0.2),
                      borderRadius: BorderRadius.circular(20),
                    ),
                    child: Text(
                      '${rt.subdistrict}, ${rt.city}',
                      style: const TextStyle(color: Colors.white, fontSize: 11, fontWeight: FontWeight.bold),
                    ),
                  ),
                  if (_isRtAdmin)
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                      decoration: BoxDecoration(
                        color: Colors.amber,
                        borderRadius: BorderRadius.circular(20),
                      ),
                      child: const Text('👑 Ketua RT', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 11, color: Colors.black)),
                    ),
                ],
              ),
              const SizedBox(height: 12),
              Text(
                rt.name,
                style: const TextStyle(fontSize: 20, fontWeight: FontWeight.w900, color: Colors.white),
              ),
              const SizedBox(height: 4),
              Text(
                'RT ${rt.rt} / RW ${rt.rw} • Ketua: ${rt.adminName ?? "Pengurus RT"}',
                style: TextStyle(fontSize: 12, color: Colors.white.withValues(alpha: 0.85)),
              ),
              const SizedBox(height: 16),
              const Divider(color: Colors.white24),
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  GestureDetector(
                    onTap: () {
                      Clipboard.setData(ClipboardData(text: rt.uniqueCode));
                      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Kode unik RT disalin!')));
                    },
                    child: Container(
                      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                      decoration: BoxDecoration(
                        color: Colors.white.withValues(alpha: 0.2),
                        borderRadius: BorderRadius.circular(20),
                      ),
                      child: Row(
                        children: [
                          Text('🔑 ${rt.uniqueCode}', style: const TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 12)),
                          const SizedBox(width: 6),
                          const Icon(Icons.copy, size: 14, color: Colors.white70),
                        ],
                      ),
                    ),
                  ),
                  Text(
                    '${rt.totalVerifiedResidents} Warga',
                    style: TextStyle(color: Colors.white.withValues(alpha: 0.9), fontSize: 12, fontWeight: FontWeight.bold),
                  ),
                ],
              ),
            ],
          ),
        ),
        const SizedBox(height: 16),

        // Quick Modules Grid
        Row(
          children: [
            Expanded(
              child: GestureDetector(
                onTap: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const ToolSharingScreen())),
                child: Container(
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
                      Container(
                        width: 42,
                        height: 42,
                        decoration: BoxDecoration(
                          color: const Color(0xFFECFDF5),
                          borderRadius: BorderRadius.circular(14),
                        ),
                        child: const Center(child: Text('🔨', style: TextStyle(fontSize: 22))),
                      ),
                      const SizedBox(height: 12),
                      const Text('Pinjam Alat', style: TextStyle(fontWeight: FontWeight.w800, fontSize: 15)),
                      const SizedBox(height: 2),
                      const Text('Alat RT & sharing warga', style: TextStyle(fontSize: 11, color: AppColors.textMuted)),
                    ],
                  ),
                ),
              ),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: GestureDetector(
                onTap: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const ErrandScreen())),
                child: Container(
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
                      Container(
                        width: 42,
                        height: 42,
                        decoration: BoxDecoration(
                          color: const Color(0xFFEFF6FF),
                          borderRadius: BorderRadius.circular(14),
                        ),
                        child: const Center(child: Text('🛍️', style: TextStyle(fontSize: 22))),
                      ),
                      const SizedBox(height: 12),
                      const Text('Titip Belanja', style: TextStyle(fontWeight: FontWeight.w800, fontSize: 15)),
                      const SizedBox(height: 2),
                      const Text('Belanja pasar bareng', style: TextStyle(fontSize: 11, color: AppColors.textMuted)),
                    ],
                  ),
                ),
              ),
            ),
          ],
        ),
        const SizedBox(height: 16),

        // Approval Queue for Admin RT
        if (_isRtAdmin && _pendingResidents.isNotEmpty) ...[
          Container(
            padding: const EdgeInsets.all(16),
            decoration: BoxDecoration(
              color: const Color(0xFFFFFBEB),
              borderRadius: BorderRadius.circular(20),
              border: Border.all(color: const Color(0xFFFDE68A), width: 1.5),
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    const Text('⏳ Antrean Approval Warga', style: TextStyle(fontWeight: FontWeight.w800, fontSize: 14, color: Color(0xFF92400E))),
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                      decoration: BoxDecoration(color: const Color(0xFFFDE68A), borderRadius: BorderRadius.circular(12)),
                      child: Text('${_pendingResidents.length} Menunggu', style: const TextStyle(fontSize: 10.5, fontWeight: FontWeight.bold, color: Color(0xFF92400E))),
                    ),
                  ],
                ),
                const SizedBox(height: 12),
                ..._pendingResidents.map((pr) => Container(
                      margin: const EdgeInsets.only(bottom: 8),
                      padding: const EdgeInsets.all(12),
                      decoration: BoxDecoration(
                        color: Colors.white,
                        borderRadius: BorderRadius.circular(14),
                        border: Border.all(color: const Color(0xFFFEF3C7)),
                      ),
                      child: Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(pr.name, style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 13)),
                              Text('Rumah: ${pr.houseNumber ?? "-"} • ${pr.residenceStatus == "permanent" ? "Tetap" : "Domisili"}',
                                  style: const TextStyle(fontSize: 11, color: AppColors.textMuted)),
                            ],
                          ),
                          Row(
                            children: [
                              IconButton(
                                icon: const Icon(Icons.close_rounded, color: AppColors.expense, size: 20),
                                onPressed: () => _verifyResident(pr.id, 'reject'),
                              ),
                              IconButton(
                                icon: const Icon(Icons.check_circle_rounded, color: AppColors.primary, size: 22),
                                onPressed: () => _verifyResident(pr.id, 'approve'),
                              ),
                            ],
                          ),
                        ],
                      ),
                    )),
              ],
            ),
          ),
          const SizedBox(height: 16),
        ],

        // Verified Residents List
        Container(
          padding: const EdgeInsets.all(18),
          decoration: BoxDecoration(
            color: AppColors.card,
            borderRadius: BorderRadius.circular(22),
            border: Border.all(color: AppColors.border),
            boxShadow: AppColors.cardShadow,
          ),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  const Text('👥 Warga Terdaftar', style: TextStyle(fontWeight: FontWeight.w800, fontSize: 14)),
                  Text('${_residents.length} Total', style: const TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: AppColors.primary)),
                ],
              ),
              const SizedBox(height: 12),
              if (_residents.isEmpty)
                const Center(
                  child: Padding(
                    padding: EdgeInsets.all(16),
                    child: Text('Belum ada warga lain yang terdaftar.', style: TextStyle(color: AppColors.textMuted, fontSize: 12)),
                  ),
                )
              else
                ..._residents.map((r) => Container(
                      margin: const EdgeInsets.only(bottom: 8),
                      padding: const EdgeInsets.all(12),
                      decoration: BoxDecoration(
                        color: AppColors.bg,
                        borderRadius: BorderRadius.circular(14),
                      ),
                      child: Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          Row(
                            children: [
                              CircleAvatar(
                                radius: 16,
                                backgroundColor: AppColors.primary.withValues(alpha: 0.12),
                                child: Text(
                                  r.name.isNotEmpty ? r.name[0].toUpperCase() : 'W',
                                  style: const TextStyle(color: AppColors.primary, fontWeight: FontWeight.bold, fontSize: 12),
                                ),
                              ),
                              const SizedBox(width: 10),
                              Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Text(r.name, style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 13)),
                                  Text('Rumah: ${r.houseNumber ?? "-"}', style: const TextStyle(fontSize: 11, color: AppColors.textMuted)),
                                ],
                              ),
                            ],
                          ),
                          Container(
                            padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                            decoration: BoxDecoration(
                              color: r.residenceStatus == 'permanent'
                                  ? AppColors.primary.withValues(alpha: 0.1)
                                  : Colors.blue.withValues(alpha: 0.1),
                              borderRadius: BorderRadius.circular(8),
                            ),
                            child: Text(
                              r.residenceStatus == 'permanent' ? '🏠 Tetap' : '🏢 Domisili',
                              style: TextStyle(
                                fontSize: 10.5,
                                fontWeight: FontWeight.bold,
                                color: r.residenceStatus == 'permanent' ? AppColors.primary : Colors.blue,
                              ),
                            ),
                          ),
                        ],
                      ),
                    )),
            ],
          ),
        ),
      ],
    );
  }
}
