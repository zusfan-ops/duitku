import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import '../../models/neighborhood.dart';
import '../../services/api_service.dart';
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

      if (res['success'] == true) {
        ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(res['message']?.toString() ?? 'Berhasil bergabung.')));
        _loadData();
      } else {
        ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(res['message']?.toString() ?? 'Gagal bergabung.')));
      }
    } catch (e) {
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

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Komunitas & Sistem RT', style: TextStyle(fontWeight: FontWeight.w800)),
        centerTitle: false,
      ),
      body: _loading
          ? const Center(child: CircularProgressIndicator())
          : RefreshIndicator(
              onRefresh: _loadData,
              child: _joined && _neighborhood != null ? _buildDashboard() : _buildJoinView(),
            ),
    );
  }

  Widget _buildJoinView() {
    return ListView(
      padding: const EdgeInsets.all(20),
      children: [
        const SizedBox(height: 10),
        Center(
          child: Container(
            width: 72,
            height: 72,
            decoration: BoxDecoration(
              color: const Color(0xFFECFDF5),
              borderRadius: BorderRadius.circular(24),
            ),
            child: const Center(child: Text('🏘️', style: TextStyle(fontSize: 36))),
          ),
        ),
        const SizedBox(height: 16),
        const Text(
          'Gabung Komunitas RT',
          style: TextStyle(fontSize: 20, fontWeight: FontWeight.w900, color: Color(0xFF111827)),
          textAlign: TextAlign.center,
        ),
        const SizedBox(height: 6),
        const Text(
          'Hubungkan akun Anda dengan lingkungan RT setempat untuk meminjam alat warga dan menikmati titip belanja tetangga.',
          style: TextStyle(fontSize: 13, color: Color(0xFF6B7280)),
          textAlign: TextAlign.center,
        ),
        const SizedBox(height: 24),

        // Input Kode RT
        TextField(
          controller: _codeCtrl,
          textCapitalization: TextCapitalization.characters,
          style: const TextStyle(fontWeight: FontWeight.w800, letterSpacing: 1),
          decoration: InputDecoration(
            labelText: 'Kode Unik RT',
            hintText: 'Contoh: RT04-RW02-GRIYA-2026',
            prefixIcon: const Icon(Icons.key),
            filled: true,
            fillColor: const Color(0xFFF9FAFB),
            border: OutlineInputBorder(borderRadius: BorderRadius.circular(16)),
          ),
        ),
        const SizedBox(height: 16),

        // Status Tempat Tinggal
        const Text('Status Tempat Tinggal', style: TextStyle(fontSize: 13, fontWeight: FontWeight.w800)),
        const SizedBox(height: 8),
        Row(
          children: [
            Expanded(
              child: GestureDetector(
                onTap: () => setState(() => _residenceStatus = 'permanent'),
                child: Container(
                  padding: const EdgeInsets.all(12),
                  decoration: BoxDecoration(
                    color: _residenceStatus == 'permanent' ? const Color(0xFFECFDF5) : Colors.white,
                    borderRadius: BorderRadius.circular(14),
                    border: Border.all(
                      color: _residenceStatus == 'permanent' ? const Color(0xFF059669) : const Color(0xFFE5E7EB),
                      width: 1.5,
                    ),
                  ),
                  child: const Column(
                    children: [
                      Text('🏠 Warga Tetap', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13)),
                      SizedBox(height: 2),
                      Text('KTP beralamat di RT ini', style: TextStyle(fontSize: 10, color: Colors.grey)),
                    ],
                  ),
                ),
              ),
            ),
            const SizedBox(width: 10),
            Expanded(
              child: GestureDetector(
                onTap: () => setState(() => _residenceStatus = 'temporary'),
                child: Container(
                  padding: const EdgeInsets.all(12),
                  decoration: BoxDecoration(
                    color: _residenceStatus == 'temporary' ? const Color(0xFFECFDF5) : Colors.white,
                    borderRadius: BorderRadius.circular(14),
                    border: Border.all(
                      color: _residenceStatus == 'temporary' ? const Color(0xFF059669) : const Color(0xFFE5E7EB),
                      width: 1.5,
                    ),
                  ),
                  child: const Column(
                    children: [
                      Text('🏢 Domisili', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13)),
                      SizedBox(height: 2),
                      Text('Kontrak / kost', style: TextStyle(fontSize: 10, color: Colors.grey)),
                    ],
                  ),
                ),
              ),
            ),
          ],
        ),
        const SizedBox(height: 16),

        // Nomor Rumah
        TextField(
          controller: _houseCtrl,
          decoration: InputDecoration(
            labelText: 'Nomor Rumah / Blok',
            hintText: 'Contoh: Blok B No. 12',
            prefixIcon: const Icon(Icons.home),
            filled: true,
            fillColor: const Color(0xFFF9FAFB),
            border: OutlineInputBorder(borderRadius: BorderRadius.circular(16)),
          ),
        ),
        const SizedBox(height: 24),

        ElevatedButton(
          style: ElevatedButton.styleFrom(
            backgroundColor: const Color(0xFF059669),
            foregroundColor: Colors.white,
            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(30)),
            padding: const EdgeInsets.symmetric(vertical: 14),
          ),
          onPressed: _joinRt,
          child: const Text('Gabung Sekarang', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 15)),
        ),
      ],
    );
  }

  Widget _buildDashboard() {
    final rt = _neighborhood!;

    return ListView(
      padding: const EdgeInsets.all(16),
      children: [
        // Hero Card
        Container(
          padding: const EdgeInsets.all(20),
          decoration: BoxDecoration(
            gradient: const LinearGradient(
              colors: [Color(0xFF065F46), Color(0xFF059669), Color(0xFF10B981)],
              begin: Alignment.topLeft,
              end: Alignment.bottomRight,
            ),
            borderRadius: BorderRadius.circular(24),
            boxShadow: [
              BoxShadow(
                color: const Color(0xFF059669).withOpacity(0.3),
                blurRadius: 20,
                offset: const Offset(0, 8),
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
                      color: Colors.white.withOpacity(0.2),
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
                style: TextStyle(fontSize: 12, color: Colors.white.withOpacity(0.85)),
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
                        color: Colors.white.withOpacity(0.2),
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
                    style: TextStyle(color: Colors.white.withOpacity(0.9), fontSize: 12, fontWeight: FontWeight.bold),
                  ),
                ],
              ),
            ],
          ),
        ),
        const SizedBox(height: 16),

        // Quick Modules Grid (Tool Sharing & Errands)
        Row(
          children: [
            Expanded(
              child: GestureDetector(
                onTap: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const ToolSharingScreen())),
                child: Container(
                  padding: const EdgeInsets.all(16),
                  decoration: BoxDecoration(
                    color: Colors.white,
                    borderRadius: BorderRadius.circular(20),
                    border: Border.all(color: const Color(0xFFE5E7EB)),
                    boxShadow: [
                      BoxShadow(color: Colors.black.withOpacity(0.02), blurRadius: 8, offset: const Offset(0, 2)),
                    ],
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
                      const Text('Alat RT & sharing warga', style: TextStyle(fontSize: 11, color: Colors.grey)),
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
                    color: Colors.white,
                    borderRadius: BorderRadius.circular(20),
                    border: Border.all(color: const Color(0xFFE5E7EB)),
                    boxShadow: [
                      BoxShadow(color: Colors.black.withOpacity(0.02), blurRadius: 8, offset: const Offset(0, 2)),
                    ],
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
                      const Text('Jastip antar-tetangga', style: TextStyle(fontSize: 11, color: Colors.grey)),
                    ],
                  ),
                ),
              ),
            ),
          ],
        ),
        const SizedBox(height: 20),

        // Pending Approval Queue (If RT Admin)
        if (_isRtAdmin && _pendingResidents.isNotEmpty) ...[
          Container(
            padding: const EdgeInsets.all(16),
            decoration: BoxDecoration(
              color: const Color(0xFFFFFBEB),
              borderRadius: BorderRadius.circular(20),
              border: Border.all(color: const Color(0xFFFDE68A)),
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  children: [
                    const Icon(Icons.person_add, color: Colors.amber, size: 20),
                    const SizedBox(width: 8),
                    Text(
                      'Antrean Verifikasi Warga (${_pendingResidents.length})',
                      style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14),
                    ),
                  ],
                ),
                const SizedBox(height: 12),
                ..._pendingResidents.map((p) => Container(
                  margin: const EdgeInsets.only(bottom: 8),
                  padding: const EdgeInsets.all(12),
                  decoration: BoxDecoration(
                    color: Colors.white,
                    borderRadius: BorderRadius.circular(14),
                  ),
                  child: Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(p.name, style: const TextStyle(fontWeight: FontWeight.bold)),
                          Text('Rumah No. ${p.houseNumber ?? "-"} • ${p.residenceStatus == "permanent" ? "KTP" : "Domisili"}', style: const TextStyle(fontSize: 11, color: Colors.grey)),
                        ],
                      ),
                      Row(
                        children: [
                          IconButton(
                            icon: const Icon(Icons.check_circle, color: Colors.green),
                            onPressed: () => _verifyResident(p.id, 'approve'),
                          ),
                          IconButton(
                            icon: const Icon(Icons.cancel, color: Colors.red),
                            onPressed: () => _verifyResident(p.id, 'reject'),
                          ),
                        ],
                      ),
                    ],
                  ),
                )),
              ],
            ),
          ),
          const SizedBox(height: 20),
        ],

        // Residents List
        Row(
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          children: [
            Text('Daftar Warga (${_residents.length})', style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 14)),
          ],
        ),
        const SizedBox(height: 10),
        ..._residents.map((r) => Container(
          margin: const EdgeInsets.only(bottom: 8),
          padding: const EdgeInsets.all(12),
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(16),
            border: Border.all(color: const Color(0xFFE5E7EB)),
          ),
          child: Row(
            children: [
              CircleAvatar(
                backgroundColor: const Color(0xFF059669),
                child: Text(r.name.isNotEmpty ? r.name.substring(0, 1).toUpperCase() : 'U', style: const TextStyle(color: Colors.white, fontWeight: FontWeight.bold)),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(r.name, style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14)),
                    Text('Rumah No. ${r.houseNumber ?? "-"} • ${r.residenceStatus == "permanent" ? "Warga Tetap" : "Warga Domisili"}', style: const TextStyle(fontSize: 11, color: Colors.grey)),
                  ],
                ),
              ),
            ],
          ),
        )),
      ],
    );
  }
}
