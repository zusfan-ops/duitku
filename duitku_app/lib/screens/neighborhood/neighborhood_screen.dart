import 'dart:io';
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:image_picker/image_picker.dart';
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
  bool _isTreasurer = false;
  bool _canManageKas = false;
  int _currentUserId = 0;

  // Kas & Activity & Tools summary data
  NeighborhoodKasSummary _kasSummary = NeighborhoodKasSummary();
  List<NeighborhoodKasItem> _kasLedger = [];
  List<NeighborhoodActivity> _activities = [];
  List<NeighborhoodAnnouncement> _announcements = [];
  List<NeighborhoodDiscussion> _discussions = [];
  int _toolsCount = 0;
  int _toolsAvailable = 0;
  int _toolsRented = 0;

  final TextEditingController _codeCtrl = TextEditingController();
  final TextEditingController _houseCtrl = TextEditingController();
  String _residenceStatus = 'permanent';

  @override
  void initState() {
    super.initState();
    _loadData();
  }

  String _formatRupiah(num value) {
    final isNegative = value < 0;
    final absVal = value.abs().toInt();
    final str = absVal.toString();
    final reg = RegExp(r'(\d{1,3})(?=(\d{3})+(?!\d))');
    final formatted = str.replaceAllMapped(reg, (Match m) => '${m[1]}.');
    return '${isNegative ? "-Rp " : "Rp "}$formatted';
  }

  Future<void> _loadData() async {
    setState(() => _loading = true);
    try {
      final prof = await ApiService.instance.get('profile');
      if (prof['success'] == true && prof['user'] != null) {
        _currentUserId = int.tryParse(prof['user']['id']?.toString() ?? '0') ?? 0;
      }

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
          _isTreasurer = res['is_treasurer'] == true;
          _canManageKas = res['can_manage_kas'] == true;
          _toolsCount = int.tryParse(res['tools_count']?.toString() ?? '0') ?? 0;
          _toolsAvailable = int.tryParse(res['tools_available']?.toString() ?? '0') ?? 0;
          _toolsRented = int.tryParse(res['tools_rented']?.toString() ?? '0') ?? 0;

          if (res['kas_summary'] != null) {
            _kasSummary = NeighborhoodKasSummary.fromJson(res['kas_summary'] as Map<String, dynamic>);
          } else {
            _kasSummary = NeighborhoodKasSummary();
          }

          _kasLedger = (res['kas_ledger'] as List? ?? [])
              .map((k) => NeighborhoodKasItem.fromJson(k as Map<String, dynamic>))
              .toList();

          _activities = (res['upcoming_activities'] as List? ?? [])
              .map((a) => NeighborhoodActivity.fromJson(a as Map<String, dynamic>))
              .toList();

          _announcements = (res['announcements'] as List? ?? [])
              .map((an) => NeighborhoodAnnouncement.fromJson(an as Map<String, dynamic>))
              .toList();

          _discussions = (res['discussions'] as List? ?? [])
              .map((d) => NeighborhoodDiscussion.fromJson(d as Map<String, dynamic>))
              .toList();
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

  Future<void> _verifyResident(int userId, String action, String residentName) async {
    final isApprove = action == 'approve';
    final confirmed = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
        title: Text(isApprove ? 'Setujui Warga?' : 'Tolak Pengajuan?', style: const TextStyle(fontWeight: FontWeight.w900, fontSize: 17)),
        content: Text(
          isApprove
              ? 'Apakah Anda yakin ingin menyetujui $residentName sebagai warga resmi di RT Anda?'
              : 'Apakah Anda yakin ingin menolak permohonan bergabung dari $residentName?',
          style: const TextStyle(fontSize: 13, color: AppColors.textSecondary),
        ),
        actions: [
          TextButton(onPressed: () => Navigator.pop(ctx, false), child: const Text('Batal')),
          ElevatedButton(
            style: ElevatedButton.styleFrom(
              backgroundColor: isApprove ? const Color(0xFF059669) : const Color(0xFFEF4444),
              foregroundColor: Colors.white,
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
            ),
            onPressed: () => Navigator.pop(ctx, true),
            child: Text(isApprove ? 'Setujui' : 'Tolak'),
          ),
        ],
      ),
    );

    if (confirmed != true) return;

    try {
      final res = await ApiService.instance.post('neighborhood/resident/verify', {
        'target_user_id': userId,
        'action': action,
      });
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(res['message']?.toString() ?? (isApprove ? 'Warga berhasil disetujui!' : 'Pengajuan ditolak.')),
          backgroundColor: isApprove ? const Color(0xFF059669) : const Color(0xFFEF4444),
        ),
      );
      if (res['success'] == true) {
        _loadData();
      }
    } catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Error: $e')));
    }
  }

  Future<void> _changeMemberRole(int targetUserId, String newRole, String memberName) async {
    final isTreasurer = newRole == 'rt_treasurer';
    final confirmed = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
        title: Text(isTreasurer ? 'Tunjuk Bendahara RT?' : 'Hapus Status Bendahara?', style: const TextStyle(fontWeight: FontWeight.w900, fontSize: 17)),
        content: Text(
          isTreasurer
              ? 'Apakah Anda yakin ingin menunjuk $memberName sebagai Bendahara RT? Bendahara memiliki akses untuk mencatat pemasukan dan pengeluaran kas RT.'
              : 'Kembalikan peran $memberName menjadi Warga biasa?',
          style: const TextStyle(fontSize: 13, color: AppColors.textSecondary),
        ),
        actions: [
          TextButton(onPressed: () => Navigator.pop(ctx, false), child: const Text('Batal')),
          ElevatedButton(
            style: ElevatedButton.styleFrom(
              backgroundColor: isTreasurer ? const Color(0xFF4F46E5) : const Color(0xFF64748B),
              foregroundColor: Colors.white,
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
            ),
            onPressed: () => Navigator.pop(ctx, true),
            child: Text(isTreasurer ? 'Tunjuk Bendahara' : 'Ubah ke Warga'),
          ),
        ],
      ),
    );

    if (confirmed != true) return;

    try {
      final res = await ApiService.instance.post('neighborhood/member/role', {
        'target_user_id': targetUserId,
        'role': newRole,
      });
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(res['message']?.toString() ?? 'Peran anggota berhasil diperbarui.'),
          backgroundColor: const Color(0xFF059669),
        ),
      );
      if (res['success'] == true) {
        _loadData();
      }
    } catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Error: $e')));
    }
  }

  // ── 1. DIALOG PENGUMUMAN RT ──
  void _showAddAnnouncementDialog() {
    final titleCtrl = TextEditingController();
    final contentCtrl = TextEditingController();
    String badge = 'Info';
    bool isPinned = false;
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
            child: ConstrainedBox(
              constraints: BoxConstraints(maxHeight: MediaQuery.of(context).size.height * 0.88),
              child: SingleChildScrollView(
                physics: const BouncingScrollPhysics(),
                padding: const EdgeInsets.only(bottom: 28),
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
                    const Text('Terbitkan Pengumuman Resmi RT', textAlign: TextAlign.center, style: TextStyle(fontSize: 18, fontWeight: FontWeight.w900)),
                    const SizedBox(height: 4),
                    const Text('Pengumuman akan disiarkan di papan pengumuman RT seluruh warga.', textAlign: TextAlign.center, style: TextStyle(fontSize: 12, color: AppColors.textMuted)),
                    const SizedBox(height: 18),

                    TextField(
                      controller: titleCtrl,
                      decoration: InputDecoration(
                        labelText: 'Judul Pengumuman',
                        hintText: 'Misal: Pemadaman Air PDAM Sementara',
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
                          child: DropdownButtonFormField<String>(
                            initialValue: badge,
                            decoration: InputDecoration(
                              labelText: 'Kategori Badge',
                              filled: true,
                              fillColor: AppColors.bg,
                              border: OutlineInputBorder(borderRadius: BorderRadius.circular(14), borderSide: const BorderSide(color: AppColors.border)),
                              enabledBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(14), borderSide: const BorderSide(color: AppColors.border)),
                            ),
                            items: const [
                              DropdownMenuItem(value: 'Info', child: Text('ℹ️ Info')),
                              DropdownMenuItem(value: 'Penting', child: Text('⚠️ Penting')),
                              DropdownMenuItem(value: 'Darurat', child: Text('🚨 Darurat')),
                              DropdownMenuItem(value: 'Kegiatan', child: Text('📅 Kegiatan')),
                            ],
                            onChanged: (v) {
                              if (v != null) setDlgState(() => badge = v);
                            },
                          ),
                        ),
                        const SizedBox(width: 10),
                        Expanded(
                          child: SwitchListTile(
                            contentPadding: EdgeInsets.zero,
                            title: const Text('Sematkan (Pin)', style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold)),
                            value: isPinned,
                            activeThumbColor: const Color(0xFF059669),
                            onChanged: (val) => setDlgState(() => isPinned = val),
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 12),

                    TextField(
                      controller: contentCtrl,
                      maxLines: 4,
                      decoration: InputDecoration(
                        labelText: 'Isi Lengkap Pengumuman',
                        hintText: 'Tuliskan rincian pengumuman yang jelas...',
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
                              if (titleCtrl.text.trim().isEmpty || contentCtrl.text.trim().isEmpty) {
                                ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Judul dan isi pengumuman wajib diisi.')));
                                return;
                              }

                              setDlgState(() => isSubmitting = true);
                              try {
                                final res = await ApiService.instance.post('neighborhood/announcement/store', {
                                  'title': titleCtrl.text.trim(),
                                  'content': contentCtrl.text.trim(),
                                  'badge': badge,
                                  'is_pinned': isPinned ? 1 : 0,
                                });
                                if (!mounted) return;
                                if (ctx.mounted) Navigator.pop(ctx);
                                if (res['success'] == true) {
                                  ScaffoldMessenger.of(context).showSnackBar(SnackBar(
                                    content: Text(res['message']?.toString() ?? 'Pengumuman berhasil disiarkan!'),
                                    backgroundColor: const Color(0xFF059669),
                                  ));
                                  _loadData();
                                } else {
                                  ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(res['message']?.toString() ?? 'Gagal menyiarkan pengumuman.')));
                                }
                              } catch (e) {
                                if (!mounted) return;
                                setDlgState(() => isSubmitting = false);
                                ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Error: $e')));
                              }
                            },
                      child: isSubmitting
                          ? const SizedBox(height: 20, width: 20, child: CircularProgressIndicator(strokeWidth: 2.2, color: Colors.white))
                          : const Text('Siarkan Pengumuman Sekarang', style: TextStyle(fontWeight: FontWeight.w800, fontSize: 14)),
                    ),
                  ],
                ),
              ),
            ),
          ),
        ),
      ),
    );
  }

  Future<void> _deleteAnnouncement(int id) async {
    final confirmed = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
        title: const Text('Hapus Pengumuman?', style: TextStyle(fontWeight: FontWeight.w900, fontSize: 17)),
        content: const Text('Apakah Anda yakin ingin menghapus pengumuman ini?', style: TextStyle(fontSize: 13, color: AppColors.textSecondary)),
        actions: [
          TextButton(onPressed: () => Navigator.pop(ctx, false), child: const Text('Batal')),
          ElevatedButton(
            style: ElevatedButton.styleFrom(
              backgroundColor: const Color(0xFFEF4444),
              foregroundColor: Colors.white,
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
            ),
            onPressed: () => Navigator.pop(ctx, true),
            child: const Text('Hapus'),
          ),
        ],
      ),
    );

    if (confirmed != true) return;

    try {
      final res = await ApiService.instance.post('neighborhood/announcement/delete/$id', {});
      if (!mounted) return;
      if (res['success'] == true) {
        ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Pengumuman berhasil dihapus.')));
        _loadData();
      }
    } catch (_) {}
  }

  // ── 2. DIALOG FORUM DISKUSI WARGA ──
  void _showAddDiscussionDialog() {
    final titleCtrl = TextEditingController();
    final contentCtrl = TextEditingController();
    String category = 'Umum';
    bool isSubmitting = false;

    final categories = ['Umum', 'Usul & Saran', 'Keamanan', 'Kebersihan', 'Info Warga', 'Kehilangan'];

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
            child: ConstrainedBox(
              constraints: BoxConstraints(maxHeight: MediaQuery.of(context).size.height * 0.88),
              child: SingleChildScrollView(
                physics: const BouncingScrollPhysics(),
                padding: const EdgeInsets.only(bottom: 28),
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
                    const Text('Buat Postingan Diskusi Warga', textAlign: TextAlign.center, style: TextStyle(fontSize: 18, fontWeight: FontWeight.w900)),
                    const SizedBox(height: 4),
                    const Text('Sampaikan saran, ide, atau info lingkungan secara terbuka.', textAlign: TextAlign.center, style: TextStyle(fontSize: 12, color: AppColors.textMuted)),
                    const SizedBox(height: 18),

                    DropdownButtonFormField<String>(
                      initialValue: category,
                      decoration: InputDecoration(
                        labelText: 'Kategori Topik',
                        filled: true,
                        fillColor: AppColors.bg,
                        border: OutlineInputBorder(borderRadius: BorderRadius.circular(14), borderSide: const BorderSide(color: AppColors.border)),
                        enabledBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(14), borderSide: const BorderSide(color: AppColors.border)),
                      ),
                      items: categories.map((c) => DropdownMenuItem(value: c, child: Text(c, style: const TextStyle(fontSize: 13.5, fontWeight: FontWeight.w600)))).toList(),
                      onChanged: (v) {
                        if (v != null) setDlgState(() => category = v);
                      },
                    ),
                    const SizedBox(height: 12),

                    TextField(
                      controller: titleCtrl,
                      decoration: InputDecoration(
                        labelText: 'Judul Topik (Opsional)',
                        hintText: 'Misal: Usul jadwal pos ronda malam diperbarui',
                        filled: true,
                        fillColor: AppColors.bg,
                        border: OutlineInputBorder(borderRadius: BorderRadius.circular(14), borderSide: const BorderSide(color: AppColors.border)),
                        enabledBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(14), borderSide: const BorderSide(color: AppColors.border)),
                      ),
                    ),
                    const SizedBox(height: 12),

                    TextField(
                      controller: contentCtrl,
                      maxLines: 4,
                      decoration: InputDecoration(
                        labelText: 'Isi Diskusi',
                        hintText: 'Tuliskan hal yang ingin didiskusikan bersama warga...',
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
                              if (contentCtrl.text.trim().isEmpty) {
                                ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Isi diskusi tidak boleh kosong.')));
                                return;
                              }

                              setDlgState(() => isSubmitting = true);
                              try {
                                final res = await ApiService.instance.post('neighborhood/discussion/store', {
                                  'title': titleCtrl.text.trim(),
                                  'content': contentCtrl.text.trim(),
                                  'category': category,
                                });
                                if (!mounted) return;
                                if (ctx.mounted) Navigator.pop(ctx);
                                if (res['success'] == true) {
                                  ScaffoldMessenger.of(context).showSnackBar(SnackBar(
                                    content: Text(res['message']?.toString() ?? 'Postingan berhasil dibagikan!'),
                                    backgroundColor: const Color(0xFF059669),
                                  ));
                                  _loadData();
                                } else {
                                  ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(res['message']?.toString() ?? 'Gagal membagikan postingan.')));
                                }
                              } catch (e) {
                                if (!mounted) return;
                                setDlgState(() => isSubmitting = false);
                                ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Error: $e')));
                              }
                            },
                      child: isSubmitting
                          ? const SizedBox(height: 20, width: 20, child: CircularProgressIndicator(strokeWidth: 2.2, color: Colors.white))
                          : const Text('Kirim ke Forum Warga', style: TextStyle(fontWeight: FontWeight.w800, fontSize: 14)),
                    ),
                  ],
                ),
              ),
            ),
          ),
        ),
      ),
    );
  }

  Future<void> _deleteDiscussion(int id) async {
    final confirmed = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
        title: const Text('Hapus Postingan?', style: TextStyle(fontWeight: FontWeight.w900, fontSize: 17)),
        content: const Text('Apakah Anda yakin ingin menghapus postingan diskusi ini beserta seluruh komentarnya?', style: TextStyle(fontSize: 13, color: AppColors.textSecondary)),
        actions: [
          TextButton(onPressed: () => Navigator.pop(ctx, false), child: const Text('Batal')),
          ElevatedButton(
            style: ElevatedButton.styleFrom(
              backgroundColor: const Color(0xFFEF4444),
              foregroundColor: Colors.white,
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
            ),
            onPressed: () => Navigator.pop(ctx, true),
            child: const Text('Hapus'),
          ),
        ],
      ),
    );

    if (confirmed != true) return;

    try {
      final res = await ApiService.instance.post('neighborhood/discussion/delete/$id', {});
      if (!mounted) return;
      if (res['success'] == true) {
        ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Postingan berhasil dihapus.')));
        _loadData();
      }
    } catch (_) {}
  }

  void _showDiscussionDetailSheet(NeighborhoodDiscussion disc) {
    final commentInputCtrl = TextEditingController();
    bool isSending = false;

    showModalBottomSheet(
      context: context,
      useRootNavigator: true,
      isScrollControlled: true,
      backgroundColor: AppColors.card,
      shape: const RoundedRectangleBorder(borderRadius: BorderRadius.vertical(top: Radius.circular(24))),
      builder: (ctx) => StatefulBuilder(
        builder: (ctx, setDlgState) => FutureBuilder<Map<String, dynamic>>(
          future: ApiService.instance.get('neighborhood/discussion/${disc.id}'),
          builder: (ctx, snapshot) {
            if (snapshot.connectionState == ConnectionState.waiting) {
              return const SizedBox(
                height: 320,
                child: Center(child: CircularProgressIndicator()),
              );
            }

            final discussionData = snapshot.data?['discussion'] ?? {};
            final detailedDisc = NeighborhoodDiscussion.fromJson(discussionData as Map<String, dynamic>);
            final comments = detailedDisc.comments;

            return SafeArea(
              child: Padding(
                padding: EdgeInsets.only(
                  bottom: MediaQuery.of(ctx).viewInsets.bottom,
                  left: 20,
                  right: 20,
                  top: 16,
                ),
                child: ConstrainedBox(
                  constraints: BoxConstraints(maxHeight: MediaQuery.of(context).size.height * 0.88),
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

                      // Thread Header
                      Row(
                        children: [
                          CircleAvatar(
                            radius: 18,
                            backgroundColor: const Color(0xFF059669),
                            child: Text(
                              detailedDisc.authorName != null && detailedDisc.authorName!.isNotEmpty ? detailedDisc.authorName![0].toUpperCase() : 'W',
                              style: const TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 13),
                            ),
                          ),
                          const SizedBox(width: 10),
                          Expanded(
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Text(detailedDisc.authorName ?? 'Warga', style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 13)),
                                Text('Rumah ${detailedDisc.houseNumber ?? "-"} • ${detailedDisc.createdAt}', style: const TextStyle(fontSize: 10.5, color: AppColors.textMuted)),
                              ],
                            ),
                          ),
                          Container(
                            padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                            decoration: BoxDecoration(color: const Color(0xFFEFF6FF), borderRadius: BorderRadius.circular(8)),
                            child: Text(detailedDisc.category, style: const TextStyle(fontSize: 10, fontWeight: FontWeight.bold, color: Color(0xFF2563EB))),
                          ),
                        ],
                      ),
                      const SizedBox(height: 10),

                      if (detailedDisc.title != null && detailedDisc.title!.isNotEmpty) ...[
                        Text(detailedDisc.title!, style: const TextStyle(fontSize: 15, fontWeight: FontWeight.w900)),
                        const SizedBox(height: 4),
                      ],
                      Text(detailedDisc.content, style: const TextStyle(fontSize: 13, color: AppColors.textSecondary, height: 1.4)),
                      const SizedBox(height: 12),
                      const Divider(),

                      // Comments List
                      Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          const Text('Komentar Warga', style: TextStyle(fontWeight: FontWeight.w800, fontSize: 13)),
                          Text('${comments.length} Balasan', style: const TextStyle(fontSize: 11.5, fontWeight: FontWeight.bold, color: AppColors.primary)),
                        ],
                      ),
                      const SizedBox(height: 8),

                      Expanded(
                        child: comments.isEmpty
                            ? const Center(
                                child: Text('Belum ada balasan. Jadilah yang pertama berkomentar!', style: TextStyle(fontSize: 12, color: AppColors.textMuted)),
                              )
                            : ListView.builder(
                                physics: const BouncingScrollPhysics(),
                                itemCount: comments.length,
                                itemBuilder: (ctx, i) {
                                  final c = comments[i];
                                  return Container(
                                    margin: const EdgeInsets.only(bottom: 8),
                                    padding: const EdgeInsets.all(12),
                                    decoration: BoxDecoration(
                                      color: AppColors.bg,
                                      borderRadius: BorderRadius.circular(12),
                                      border: Border.all(color: AppColors.border),
                                    ),
                                    child: Column(
                                      crossAxisAlignment: CrossAxisAlignment.start,
                                      children: [
                                        Row(
                                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                          children: [
                                            Text(
                                              '${c.authorName ?? "Warga"} (Rumah ${c.houseNumber ?? "-"})',
                                              style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 11.5),
                                            ),
                                            Text(c.createdAt, style: const TextStyle(fontSize: 10, color: AppColors.textMuted)),
                                          ],
                                        ),
                                        const SizedBox(height: 4),
                                        Text(c.comment, style: const TextStyle(fontSize: 12, color: AppColors.textSecondary)),
                                      ],
                                    ),
                                  );
                                },
                              ),
                      ),

                      // Input Comment Bar
                      Padding(
                        padding: const EdgeInsets.symmetric(vertical: 12),
                        child: Row(
                          children: [
                            Expanded(
                              child: TextField(
                                controller: commentInputCtrl,
                                decoration: InputDecoration(
                                  hintText: 'Tulis komentar balasan...',
                                  filled: true,
                                  fillColor: AppColors.bg,
                                  contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
                                  border: OutlineInputBorder(borderRadius: BorderRadius.circular(20), borderSide: const BorderSide(color: AppColors.border)),
                                  enabledBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(20), borderSide: const BorderSide(color: AppColors.border)),
                                ),
                              ),
                            ),
                            const SizedBox(width: 8),
                            ElevatedButton(
                              style: ElevatedButton.styleFrom(
                                backgroundColor: const Color(0xFF059669),
                                foregroundColor: Colors.white,
                                padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
                                elevation: 0,
                              ),
                              onPressed: isSending
                                  ? null
                                  : () async {
                                      final text = commentInputCtrl.text.trim();
                                      if (text.isEmpty) return;

                                      setDlgState(() => isSending = true);
                                      try {
                                        final res = await ApiService.instance.post('neighborhood/discussion/${disc.id}/comment', {
                                          'comment': text,
                                        });
                                        if (res['success'] == true) {
                                          commentInputCtrl.clear();
                                          setDlgState(() => isSending = false);
                                          _loadData();
                                        } else {
                                          setDlgState(() => isSending = false);
                                        }
                                      } catch (_) {
                                        setDlgState(() => isSending = false);
                                      }
                                    },
                              child: isSending
                                  ? const SizedBox(height: 16, width: 16, child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white))
                                  : const Icon(Icons.send_rounded, size: 18),
                            ),
                          ],
                        ),
                      ),
                    ],
                  ),
                ),
              ),
            );
          },
        ),
      ),
    );
  }

  // ── 3. DIALOG KAS RT ──
  void _showRecordKasDialog() {
    String type = 'in';
    String category = 'Iuran Warga';
    final amountCtrl = TextEditingController();
    final dateCtrl = TextEditingController(text: DateTime.now().toIso8601String().substring(0, 10));
    final descCtrl = TextEditingController();
    bool isSubmitting = false;

    final inCategories = ['Iuran Warga', 'Biaya Pinjam Alat', 'Donasi Warga', 'Bantuan Sosial', 'Lainnya'];
    final outCategories = ['Sampah & Kebersihan', 'Keamanan & Ronda', 'Perbaikan Sarana/Lampu', 'Konsumsi & Acara', 'Bantuan Warga Sakit', 'Lainnya'];

    showModalBottomSheet(
      context: context,
      useRootNavigator: true,
      isScrollControlled: true,
      backgroundColor: AppColors.card,
      shape: const RoundedRectangleBorder(borderRadius: BorderRadius.vertical(top: Radius.circular(24))),
      builder: (ctx) => StatefulBuilder(
        builder: (ctx, setDlgState) {
          final activeCategories = type == 'in' ? inCategories : outCategories;
          if (!activeCategories.contains(category)) {
            category = activeCategories.first;
          }

          return SafeArea(
            child: Padding(
              padding: EdgeInsets.only(
                bottom: MediaQuery.of(ctx).viewInsets.bottom,
                left: 20,
                right: 20,
                top: 20,
              ),
              child: ConstrainedBox(
                constraints: BoxConstraints(
                  maxHeight: MediaQuery.of(context).size.height * 0.88,
                ),
                child: SingleChildScrollView(
                  physics: const BouncingScrollPhysics(),
                  padding: const EdgeInsets.only(bottom: 28),
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
                      const Text('Catat Transaksi Kas RT', textAlign: TextAlign.center, style: TextStyle(fontSize: 18, fontWeight: FontWeight.w900)),
                      const SizedBox(height: 4),
                      const Text('Catatan kas ini akan transparan dan dapat dipantau oleh seluruh warga RT.', textAlign: TextAlign.center, style: TextStyle(fontSize: 12, color: AppColors.textMuted)),
                      const SizedBox(height: 18),

                      // Toggle Type
                      Row(
                        children: [
                          Expanded(
                            child: GestureDetector(
                              onTap: () => setDlgState(() {
                                type = 'in';
                                category = inCategories.first;
                              }),
                              child: Container(
                                padding: const EdgeInsets.symmetric(vertical: 12),
                                decoration: BoxDecoration(
                                  color: type == 'in' ? const Color(0xFFECFDF5) : AppColors.bg,
                                  borderRadius: BorderRadius.circular(14),
                                  border: Border.all(color: type == 'in' ? const Color(0xFF059669) : AppColors.border, width: type == 'in' ? 1.5 : 1.0),
                                ),
                                child: Row(
                                  mainAxisAlignment: MainAxisAlignment.center,
                                  children: [
                                    Icon(Icons.arrow_downward_rounded, size: 18, color: type == 'in' ? const Color(0xFF059669) : AppColors.textMuted),
                                    const SizedBox(width: 6),
                                    Text('Pemasukan (+)', style: TextStyle(fontWeight: FontWeight.w800, fontSize: 13, color: type == 'in' ? const Color(0xFF065F46) : AppColors.textMuted)),
                                  ],
                                ),
                              ),
                            ),
                          ),
                          const SizedBox(width: 10),
                          Expanded(
                            child: GestureDetector(
                              onTap: () => setDlgState(() {
                                type = 'out';
                                category = outCategories.first;
                              }),
                              child: Container(
                                padding: const EdgeInsets.symmetric(vertical: 12),
                                decoration: BoxDecoration(
                                  color: type == 'out' ? const Color(0xFFFEF2F2) : AppColors.bg,
                                  borderRadius: BorderRadius.circular(14),
                                  border: Border.all(color: type == 'out' ? const Color(0xFFEF4444) : AppColors.border, width: type == 'out' ? 1.5 : 1.0),
                                ),
                                child: Row(
                                  mainAxisAlignment: MainAxisAlignment.center,
                                  children: [
                                    Icon(Icons.arrow_upward_rounded, size: 18, color: type == 'out' ? const Color(0xFFEF4444) : AppColors.textMuted),
                                    const SizedBox(width: 6),
                                    Text('Pengeluaran (-)', style: TextStyle(fontWeight: FontWeight.w800, fontSize: 13, color: type == 'out' ? const Color(0xFF991B1B) : AppColors.textMuted)),
                                  ],
                                ),
                              ),
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 14),

                      // Nominal
                      TextField(
                        controller: amountCtrl,
                        keyboardType: TextInputType.number,
                        style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 16),
                        decoration: InputDecoration(
                          labelText: 'Jumlah Nominal (Rp)',
                          hintText: 'Contoh: 50000',
                          prefixIcon: const Icon(Icons.payments_outlined, size: 20),
                          filled: true,
                          fillColor: AppColors.bg,
                          border: OutlineInputBorder(borderRadius: BorderRadius.circular(14), borderSide: const BorderSide(color: AppColors.border)),
                          enabledBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(14), borderSide: const BorderSide(color: AppColors.border)),
                        ),
                      ),
                      const SizedBox(height: 12),

                      // Category Dropdown
                      DropdownButtonFormField<String>(
                        initialValue: category,
                        decoration: InputDecoration(
                          labelText: 'Kategori Kas',
                          filled: true,
                          fillColor: AppColors.bg,
                          border: OutlineInputBorder(borderRadius: BorderRadius.circular(14), borderSide: const BorderSide(color: AppColors.border)),
                          enabledBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(14), borderSide: const BorderSide(color: AppColors.border)),
                        ),
                        items: activeCategories.map((c) => DropdownMenuItem(value: c, child: Text(c, style: const TextStyle(fontSize: 13.5, fontWeight: FontWeight.w600)))).toList(),
                        onChanged: (v) {
                          if (v != null) setDlgState(() => category = v);
                        },
                      ),
                      const SizedBox(height: 12),

                      // Date
                      TextField(
                        controller: dateCtrl,
                        readOnly: true,
                        decoration: InputDecoration(
                          labelText: 'Tanggal Transaksi',
                          prefixIcon: const Icon(Icons.calendar_today_outlined, size: 20),
                          filled: true,
                          fillColor: AppColors.bg,
                          border: OutlineInputBorder(borderRadius: BorderRadius.circular(14), borderSide: const BorderSide(color: AppColors.border)),
                          enabledBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(14), borderSide: const BorderSide(color: AppColors.border)),
                        ),
                        onTap: () async {
                          final picked = await showDatePicker(
                            context: context,
                            initialDate: DateTime.now(),
                            firstDate: DateTime(2020),
                            lastDate: DateTime(2035),
                          );
                          if (picked != null) {
                            dateCtrl.text = picked.toIso8601String().substring(0, 10);
                          }
                        },
                      ),
                      const SizedBox(height: 12),

                      // Description
                      TextField(
                        controller: descCtrl,
                        maxLines: 2,
                        decoration: InputDecoration(
                          labelText: 'Keterangan (Opsional)',
                          hintText: 'Misal: Iuran Agustusan warga Blok A',
                          filled: true,
                          fillColor: AppColors.bg,
                          border: OutlineInputBorder(borderRadius: BorderRadius.circular(14), borderSide: const BorderSide(color: AppColors.border)),
                          enabledBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(14), borderSide: const BorderSide(color: AppColors.border)),
                        ),
                      ),
                      const SizedBox(height: 20),

                      ElevatedButton(
                        style: ElevatedButton.styleFrom(
                          backgroundColor: type == 'in' ? const Color(0xFF059669) : const Color(0xFFEF4444),
                          foregroundColor: Colors.white,
                          padding: const EdgeInsets.symmetric(vertical: 14),
                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(30)),
                          elevation: 0,
                        ),
                        onPressed: isSubmitting
                            ? null
                            : () async {
                                final amt = double.tryParse(amountCtrl.text.replaceAll(RegExp(r'[^0-9]'), '')) ?? 0.0;
                                if (amt <= 0) {
                                  ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Nominal harus lebih besar dari 0.')));
                                  return;
                                }

                                setDlgState(() => isSubmitting = true);
                                try {
                                  final res = await ApiService.instance.post('neighborhood/kas/store', {
                                    'type': type,
                                    'category': category,
                                    'amount': amt,
                                    'date': dateCtrl.text.trim(),
                                    'description': descCtrl.text.trim(),
                                  });
                                  if (!mounted) return;
                                  if (ctx.mounted) Navigator.pop(ctx);
                                  if (res['success'] == true) {
                                    ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(res['message']?.toString() ?? 'Transaksi kas berhasil dicatat!'), backgroundColor: const Color(0xFF059669)));
                                    _loadData();
                                  } else {
                                    ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(res['message']?.toString() ?? 'Gagal mencatat kas.')));
                                  }
                                } catch (e) {
                                  if (!mounted) return;
                                  setDlgState(() => isSubmitting = false);
                                  ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Error: $e')));
                                }
                              },
                        child: isSubmitting
                            ? const SizedBox(height: 20, width: 20, child: CircularProgressIndicator(strokeWidth: 2.2, color: Colors.white))
                            : const Text('Simpan Catatan Kas RT', style: TextStyle(fontWeight: FontWeight.w800, fontSize: 14)),
                      ),
                    ],
                  ),
                ),
              ),
            ),
          );
        },
      ),
    );
  }

  Future<void> _deleteKas(int id) async {
    final confirmed = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
        title: const Text('Hapus Catatan Kas?', style: TextStyle(fontWeight: FontWeight.w900, fontSize: 17)),
        content: const Text('Apakah Anda yakin ingin menghapus catatan kas ini? Tindakan ini tidak dapat dibatalkan.', style: TextStyle(fontSize: 13, color: AppColors.textSecondary)),
        actions: [
          TextButton(onPressed: () => Navigator.pop(ctx, false), child: const Text('Batal')),
          ElevatedButton(
            style: ElevatedButton.styleFrom(
              backgroundColor: const Color(0xFFEF4444),
              foregroundColor: Colors.white,
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
            ),
            onPressed: () => Navigator.pop(ctx, true),
            child: const Text('Hapus'),
          ),
        ],
      ),
    );

    if (confirmed != true) return;

    try {
      final res = await ApiService.instance.post('neighborhood/kas/delete/$id', {});
      if (!mounted) return;
      if (res['success'] == true) {
        ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Catatan kas berhasil dihapus.')));
        _loadData();
      } else {
        ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(res['message']?.toString() ?? 'Gagal menghapus.')));
      }
    } catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Error: $e')));
    }
  }

  // ── 4. DIALOG AGENDA RT ──
  void _showAddActivityDialog() {
    final titleCtrl = TextEditingController();
    String category = 'Kerja Bakti';
    final dateCtrl = TextEditingController(text: DateTime.now().add(const Duration(days: 1)).toIso8601String().substring(0, 10));
    final timeCtrl = TextEditingController(text: '08:00');
    final locCtrl = TextEditingController(text: 'Lingkungan RT');
    final descCtrl = TextEditingController();
    bool isSubmitting = false;

    final categories = ['Kerja Bakti', 'Rapat Warga', 'Posyandu', 'Perayaan / 17-an', 'Keamanan / Ronda', 'Pengajian / Ibadah', 'Lainnya'];

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
            child: ConstrainedBox(
              constraints: BoxConstraints(
                maxHeight: MediaQuery.of(context).size.height * 0.88,
              ),
              child: SingleChildScrollView(
                physics: const BouncingScrollPhysics(),
                padding: const EdgeInsets.only(bottom: 28),
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
                    const Text('Tambah Agenda Kegiatan RT', textAlign: TextAlign.center, style: TextStyle(fontSize: 18, fontWeight: FontWeight.w900)),
                    const SizedBox(height: 4),
                    const Text('Jadwal kegiatan akan diumumkan secara langsung ke seluruh warga RT.', textAlign: TextAlign.center, style: TextStyle(fontSize: 12, color: AppColors.textMuted)),
                    const SizedBox(height: 18),

                    TextField(
                      controller: titleCtrl,
                      decoration: InputDecoration(
                        labelText: 'Nama Kegiatan',
                        hintText: 'Misal: Kerja Bakti Saluran Air Bersih',
                        filled: true,
                        fillColor: AppColors.bg,
                        border: OutlineInputBorder(borderRadius: BorderRadius.circular(14), borderSide: const BorderSide(color: AppColors.border)),
                        enabledBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(14), borderSide: const BorderSide(color: AppColors.border)),
                      ),
                    ),
                    const SizedBox(height: 12),

                    DropdownButtonFormField<String>(
                      initialValue: category,
                      decoration: InputDecoration(
                        labelText: 'Kategori Kegiatan',
                        filled: true,
                        fillColor: AppColors.bg,
                        border: OutlineInputBorder(borderRadius: BorderRadius.circular(14), borderSide: const BorderSide(color: AppColors.border)),
                        enabledBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(14), borderSide: const BorderSide(color: AppColors.border)),
                      ),
                      items: categories.map((c) => DropdownMenuItem(value: c, child: Text(c, style: const TextStyle(fontSize: 13.5, fontWeight: FontWeight.w600)))).toList(),
                      onChanged: (v) {
                        if (v != null) setDlgState(() => category = v);
                      },
                    ),
                    const SizedBox(height: 12),

                    Row(
                      children: [
                        Expanded(
                          child: TextField(
                            controller: dateCtrl,
                            readOnly: true,
                            decoration: InputDecoration(
                              labelText: 'Tanggal',
                              prefixIcon: const Icon(Icons.calendar_today_outlined, size: 18),
                              filled: true,
                              fillColor: AppColors.bg,
                              border: OutlineInputBorder(borderRadius: BorderRadius.circular(14), borderSide: const BorderSide(color: AppColors.border)),
                              enabledBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(14), borderSide: const BorderSide(color: AppColors.border)),
                            ),
                            onTap: () async {
                              final picked = await showDatePicker(
                                context: context,
                                initialDate: DateTime.now().add(const Duration(days: 1)),
                                firstDate: DateTime.now(),
                                lastDate: DateTime(2035),
                              );
                              if (picked != null) {
                                dateCtrl.text = picked.toIso8601String().substring(0, 10);
                              }
                            },
                          ),
                        ),
                        const SizedBox(width: 10),
                        Expanded(
                          child: TextField(
                            controller: timeCtrl,
                            decoration: InputDecoration(
                              labelText: 'Jam (WIB)',
                              hintText: '08:00',
                              prefixIcon: const Icon(Icons.access_time, size: 18),
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
                      controller: locCtrl,
                      decoration: InputDecoration(
                        labelText: 'Lokasi Kegiatan',
                        hintText: 'Misal: Balai Warga / Lapangan Depan',
                        prefixIcon: const Icon(Icons.location_on_outlined, size: 18),
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
                        labelText: 'Deskripsi / Catatan Tambahan',
                        hintText: 'Misal: Harap membawa sapu lidi dan cangkul masing-masing.',
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
                        padding: const EdgeInsets.symmetric(vertical: 14),
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(30)),
                        elevation: 0,
                      ),
                      onPressed: isSubmitting
                          ? null
                          : () async {
                              if (titleCtrl.text.trim().isEmpty) {
                                ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Nama kegiatan wajib diisi.')));
                                return;
                              }

                              setDlgState(() => isSubmitting = true);
                              try {
                                final res = await ApiService.instance.post('neighborhood/activity/store', {
                                  'title': titleCtrl.text.trim(),
                                  'category': category,
                                  'event_date': dateCtrl.text.trim(),
                                  'event_time': timeCtrl.text.trim(),
                                  'location': locCtrl.text.trim(),
                                  'description': descCtrl.text.trim(),
                                });
                                if (!mounted) return;
                                if (ctx.mounted) Navigator.pop(ctx);
                                if (res['success'] == true) {
                                  ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(res['message']?.toString() ?? 'Agenda berhasil ditambahkan!'), backgroundColor: const Color(0xFF059669)));
                                  _loadData();
                                } else {
                                  ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(res['message']?.toString() ?? 'Gagal menambahkan agenda.')));
                                }
                              } catch (e) {
                                if (!mounted) return;
                                setDlgState(() => isSubmitting = false);
                                ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Error: $e')));
                              }
                            },
                      child: isSubmitting
                          ? const SizedBox(height: 20, width: 20, child: CircularProgressIndicator(strokeWidth: 2.2, color: Colors.white))
                          : const Text('Terbitkan Jadwal Kegiatan', style: TextStyle(fontWeight: FontWeight.w800, fontSize: 14)),
                    ),
                  ],
                ),
              ),
            ),
          ),
        ),
      ),
    );
  }

  Future<void> _deleteActivity(int id) async {
    final confirmed = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
        title: const Text('Hapus Agenda Kegiatan?', style: TextStyle(fontWeight: FontWeight.w900, fontSize: 17)),
        content: const Text('Apakah Anda yakin ingin menghapus agenda kegiatan ini?', style: TextStyle(fontSize: 13, color: AppColors.textSecondary)),
        actions: [
          TextButton(onPressed: () => Navigator.pop(ctx, false), child: const Text('Batal')),
          ElevatedButton(
            style: ElevatedButton.styleFrom(
              backgroundColor: const Color(0xFFEF4444),
              foregroundColor: Colors.white,
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
            ),
            onPressed: () => Navigator.pop(ctx, true),
            child: const Text('Hapus'),
          ),
        ],
      ),
    );

    if (confirmed != true) return;

    try {
      final res = await ApiService.instance.post('neighborhood/activity/delete/$id', {});
      if (!mounted) return;
      if (res['success'] == true) {
        ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Agenda kegiatan berhasil dihapus.')));
        _loadData();
      } else {
        ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(res['message']?.toString() ?? 'Gagal menghapus.')));
      }
    } catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Error: $e')));
    }
  }

  void _showCreateRtDialog() {
    final nameCtrl = TextEditingController();
    final rtCtrl = TextEditingController();
    final rwCtrl = TextEditingController();
    final subdistrictCtrl = TextEditingController();
    final districtCtrl = TextEditingController();
    final cityCtrl = TextEditingController();
    final provinceCtrl = TextEditingController();
    final skNumberCtrl = TextEditingController();
    final addressNoteCtrl = TextEditingController();

    XFile? skImage;
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
            child: ConstrainedBox(
              constraints: BoxConstraints(
                maxHeight: MediaQuery.of(context).size.height * 0.88,
              ),
              child: SingleChildScrollView(
                physics: const BouncingScrollPhysics(),
                padding: const EdgeInsets.only(bottom: 28),
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
                    const Text('Daftarkan RT Baru', textAlign: TextAlign.center, style: TextStyle(fontSize: 18, fontWeight: FontWeight.w900)),
                    const SizedBox(height: 4),
                    const Text('Lengkapi wilayah & lampirkan foto/dokumen SK untuk verifikasi Superadmin', textAlign: TextAlign.center, style: TextStyle(fontSize: 12, color: AppColors.textMuted)),
                    const SizedBox(height: 18),
                    TextField(controller: nameCtrl, decoration: const InputDecoration(labelText: 'Nama RT / Perumahan', hintText: 'Misal: RT 04 Jasmine Park')),
                    const SizedBox(height: 12),
                    Row(
                      children: [
                        Expanded(child: TextField(controller: rtCtrl, decoration: const InputDecoration(labelText: 'RT', hintText: '04'))),
                        const SizedBox(width: 10),
                        Expanded(child: TextField(controller: rwCtrl, decoration: const InputDecoration(labelText: 'RW', hintText: '05'))),
                      ],
                    ),
                    const SizedBox(height: 12),
                    Row(
                      children: [
                        Expanded(child: TextField(controller: subdistrictCtrl, decoration: const InputDecoration(labelText: 'Kelurahan / Desa', hintText: 'Mranggen'))),
                        const SizedBox(width: 10),
                        Expanded(child: TextField(controller: districtCtrl, decoration: const InputDecoration(labelText: 'Kecamatan', hintText: 'Batursari'))),
                      ],
                    ),
                    const SizedBox(height: 12),
                    Row(
                      children: [
                        Expanded(child: TextField(controller: cityCtrl, decoration: const InputDecoration(labelText: 'Kota / Kab', hintText: 'Demak'))),
                        const SizedBox(width: 10),
                        Expanded(child: TextField(controller: provinceCtrl, decoration: const InputDecoration(labelText: 'Provinsi', hintText: 'Jawa Tengah'))),
                      ],
                    ),
                    const SizedBox(height: 12),
                    TextField(
                      controller: skNumberCtrl,
                      decoration: const InputDecoration(
                        labelText: 'Nomor Surat Keputusan (SK) RT',
                        hintText: 'Misal: SK/04/RW05/2026',
                        prefixIcon: Icon(Icons.verified_outlined, size: 20),
                      ),
                    ),
                    const SizedBox(height: 14),

                    // SECTION UPLOAD FOTO / DOKUMEN SK
                    const Text(
                      '📄 Foto / Dokumen Fisik SK Penunjukan RT',
                      style: TextStyle(fontSize: 12.5, fontWeight: FontWeight.w800, color: AppColors.textPrimary),
                    ),
                    const SizedBox(height: 6),
                    if (skImage == null)
                      Container(
                        padding: const EdgeInsets.all(14),
                        decoration: BoxDecoration(
                          color: AppColors.bg,
                          borderRadius: BorderRadius.circular(16),
                          border: Border.all(color: AppColors.border),
                        ),
                        child: Column(
                          children: [
                            const Icon(Icons.document_scanner_rounded, size: 36, color: AppColors.primary),
                            const SizedBox(height: 6),
                            const Text(
                              'Unggah foto SK atau surat penunjukan resmi dari RW / Kelurahan',
                              textAlign: TextAlign.center,
                              style: TextStyle(fontSize: 11.5, color: AppColors.textSecondary),
                            ),
                            const SizedBox(height: 12),
                            Row(
                              children: [
                                Expanded(
                                  child: OutlinedButton.icon(
                                    style: OutlinedButton.styleFrom(
                                      padding: const EdgeInsets.symmetric(vertical: 10),
                                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                                    ),
                                    icon: const Icon(Icons.camera_alt_rounded, size: 16),
                                    label: const Text('Ambil Foto', style: TextStyle(fontSize: 12, fontWeight: FontWeight.w700)),
                                    onPressed: () async {
                                      final picker = ImagePicker();
                                      final img = await picker.pickImage(source: ImageSource.camera, maxWidth: 1600, imageQuality: 85);
                                      if (img != null) {
                                        setDlgState(() => skImage = img);
                                      }
                                    },
                                  ),
                                ),
                                const SizedBox(width: 8),
                                Expanded(
                                  child: OutlinedButton.icon(
                                    style: OutlinedButton.styleFrom(
                                      padding: const EdgeInsets.symmetric(vertical: 10),
                                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                                    ),
                                    icon: const Icon(Icons.photo_library_rounded, size: 16),
                                    label: const Text('Dari Galeri', style: TextStyle(fontSize: 12, fontWeight: FontWeight.w700)),
                                    onPressed: () async {
                                      final picker = ImagePicker();
                                      final img = await picker.pickImage(source: ImageSource.gallery, maxWidth: 1600, imageQuality: 85);
                                      if (img != null) {
                                        setDlgState(() => skImage = img);
                                      }
                                    },
                                  ),
                                ),
                              ],
                            ),
                          ],
                        ),
                      )
                    else
                      Container(
                        padding: const EdgeInsets.all(12),
                        decoration: BoxDecoration(
                          color: const Color(0xFFECFDF5),
                          borderRadius: BorderRadius.circular(16),
                          border: Border.all(color: const Color(0xFFA7F3D0)),
                        ),
                        child: Row(
                          children: [
                            ClipRRect(
                              borderRadius: BorderRadius.circular(10),
                              child: Image.file(
                                File(skImage!.path),
                                width: 56,
                                height: 56,
                                fit: BoxFit.cover,
                              ),
                            ),
                            const SizedBox(width: 12),
                            Expanded(
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Row(
                                    children: const [
                                      Icon(Icons.check_circle_rounded, size: 16, color: Color(0xFF059669)),
                                      SizedBox(width: 4),
                                      Text(
                                        'Foto SK Terlampir',
                                        style: TextStyle(fontSize: 12.5, fontWeight: FontWeight.w800, color: Color(0xFF065F46)),
                                      ),
                                    ],
                                  ),
                                  const SizedBox(height: 2),
                                  Text(
                                    skImage!.name,
                                    maxLines: 1,
                                    overflow: TextOverflow.ellipsis,
                                    style: const TextStyle(fontSize: 11, color: Color(0xFF047857)),
                                  ),
                                ],
                              ),
                            ),
                            IconButton(
                              icon: const Icon(Icons.delete_outline_rounded, color: Colors.redAccent, size: 22),
                              tooltip: 'Hapus Dokumen',
                              onPressed: () => setDlgState(() => skImage = null),
                            ),
                          ],
                        ),
                      ),

                    const SizedBox(height: 12),
                    TextField(
                      controller: addressNoteCtrl,
                      maxLines: 2,
                      decoration: const InputDecoration(
                        labelText: 'Catatan Alamat / Kawasan (Opsional)',
                        hintText: 'Misal: Perumahan Jasmine Park Blok A - F',
                      ),
                    ),
                    const SizedBox(height: 22),
                    ElevatedButton(
                      style: ElevatedButton.styleFrom(
                        backgroundColor: AppColors.primary,
                        foregroundColor: Colors.white,
                        padding: const EdgeInsets.symmetric(vertical: 14),
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(30)),
                        elevation: 0,
                      ),
                      onPressed: isSubmitting
                          ? null
                          : () async {
                              if (nameCtrl.text.trim().isEmpty || rtCtrl.text.trim().isEmpty || rwCtrl.text.trim().isEmpty || subdistrictCtrl.text.trim().isEmpty) {
                                ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Harap lengkapi Nama RT, RT, RW, dan Kelurahan.')));
                                return;
                              }

                              setDlgState(() => isSubmitting = true);

                              String? b64Document;
                              if (skImage != null) {
                                try {
                                  final rawB64 = await ApiService.instance.base64FromFile(skImage!.path);
                                  if (rawB64 != null && rawB64.isNotEmpty) {
                                    b64Document = 'data:image/jpeg;base64,$rawB64';
                                  }
                                } catch (_) {}
                              }

                              try {
                                final payload = <String, dynamic>{
                                  'name': nameCtrl.text.trim(),
                                  'rt': rtCtrl.text.trim(),
                                  'rw': rwCtrl.text.trim(),
                                  'subdistrict': subdistrictCtrl.text.trim(),
                                  'district': districtCtrl.text.trim(),
                                  'city': cityCtrl.text.trim(),
                                  'province': provinceCtrl.text.trim(),
                                  'sk_number': skNumberCtrl.text.trim(),
                                  'address_note': addressNoteCtrl.text.trim(),
                                };
                                if (b64Document != null) {
                                  payload['sk_document_base64'] = b64Document;
                                }

                                final res = await ApiService.instance.post('neighborhood/register', payload);
                                if (!mounted) return;
                                if (ctx.mounted) {
                                  Navigator.pop(ctx);
                                }
                                if (res['success'] == true) {
                                  ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(res['message']?.toString() ?? 'Pengajuan RT berhasil dikirim!')));
                                  _loadData();
                                } else {
                                  ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(res['message']?.toString() ?? 'Gagal mendaftar RT.')));
                                }
                              } catch (e) {
                                if (!mounted) return;
                                setDlgState(() => isSubmitting = false);
                                ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Error: $e')));
                              }
                            },
                      child: isSubmitting
                          ? const SizedBox(
                              height: 20,
                              width: 20,
                              child: CircularProgressIndicator(strokeWidth: 2.2, color: Colors.white),
                            )
                          : const Text('Ajukan Pendaftaran RT Sekarang →', style: TextStyle(fontWeight: FontWeight.w800, fontSize: 14)),
                    ),
                    const SizedBox(height: 16),
                  ],
                ),
              ),
            ),
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
                'Hubungkan akun Anda dengan lingkungan RT setempat untuk menikmati pinjam alat bersama, pencatatan kas RT transparan, forum diskusi, dan titip belanja tetangga.',
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
        // ── 1. Hero RT Card ──
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
                  Row(
                    children: [
                      if (_isRtAdmin)
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                          decoration: BoxDecoration(
                            color: Colors.amber,
                            borderRadius: BorderRadius.circular(20),
                          ),
                          child: const Text('👑 Ketua RT', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 11, color: Colors.black)),
                        )
                      else if (_isTreasurer)
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                          decoration: BoxDecoration(
                            color: const Color(0xFF818CF8),
                            borderRadius: BorderRadius.circular(20),
                          ),
                          child: const Text('💼 Bendahara RT', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 11, color: Colors.white)),
                        ),
                    ],
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

        // ── 2. CARD PENGUMUMAN RESMI RT ──
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
                  Row(
                    children: [
                      Container(
                        padding: const EdgeInsets.all(8),
                        decoration: BoxDecoration(
                          color: const Color(0xFFEF4444).withValues(alpha: 0.12),
                          borderRadius: BorderRadius.circular(10),
                        ),
                        child: const Text('📢', style: TextStyle(fontSize: 16)),
                      ),
                      const SizedBox(width: 8),
                      Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: const [
                          Text('Papan Pengumuman RT', style: TextStyle(fontWeight: FontWeight.w900, fontSize: 14)),
                          Text('Informasi resmi pengurus', style: TextStyle(fontSize: 10.5, color: AppColors.textMuted)),
                        ],
                      ),
                    ],
                  ),
                  if (_canManageKas)
                    ElevatedButton.icon(
                      style: ElevatedButton.styleFrom(
                        backgroundColor: const Color(0xFF059669),
                        foregroundColor: Colors.white,
                        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                        minimumSize: Size.zero,
                        tapTargetSize: MaterialTapTargetSize.shrinkWrap,
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                        elevation: 0,
                      ),
                      icon: const Icon(Icons.add, size: 14),
                      label: const Text('Pengumuman', style: TextStyle(fontSize: 11, fontWeight: FontWeight.w800)),
                      onPressed: _showAddAnnouncementDialog,
                    ),
                ],
              ),
              const SizedBox(height: 14),

              if (_announcements.isEmpty)
                Container(
                  padding: const EdgeInsets.all(14),
                  width: double.infinity,
                  decoration: BoxDecoration(color: AppColors.bg, borderRadius: BorderRadius.circular(12)),
                  child: const Center(
                    child: Text('Belum ada pengumuman resmi dari pengurus RT.', style: TextStyle(fontSize: 11.5, color: AppColors.textMuted)),
                  ),
                )
              else
                ..._announcements.map((ann) => Container(
                      margin: const EdgeInsets.only(bottom: 8),
                      padding: const EdgeInsets.all(12),
                      decoration: BoxDecoration(
                        color: ann.isPinned ? const Color(0xFFFFFBEB) : AppColors.bg,
                        borderRadius: BorderRadius.circular(14),
                        border: Border.all(color: ann.isPinned ? const Color(0xFFFDE68A) : AppColors.border),
                      ),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Row(
                            mainAxisAlignment: MainAxisAlignment.spaceBetween,
                            children: [
                              Container(
                                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                                decoration: BoxDecoration(
                                  color: ann.badge == 'Penting'
                                      ? const Color(0xFFFEE2E2)
                                      : (ann.badge == 'Darurat' ? const Color(0xFFFEF3C7) : const Color(0xFFEFF6FF)),
                                  borderRadius: BorderRadius.circular(6),
                                ),
                                child: Text(
                                  '${ann.isPinned ? "📌 " : ""}${ann.badge}',
                                  style: TextStyle(
                                    fontSize: 10,
                                    fontWeight: FontWeight.w800,
                                    color: ann.badge == 'Penting'
                                        ? const Color(0xFF991B1B)
                                        : (ann.badge == 'Darurat' ? const Color(0xFF92400E) : const Color(0xFF1E40AF)),
                                  ),
                                ),
                              ),
                              if (_canManageKas)
                                InkWell(
                                  onTap: () => _deleteAnnouncement(ann.id),
                                  borderRadius: BorderRadius.circular(6),
                                  child: const Padding(
                                    padding: EdgeInsets.all(2),
                                    child: Icon(Icons.close_rounded, size: 16, color: AppColors.textMuted),
                                  ),
                                ),
                            ],
                          ),
                          const SizedBox(height: 6),
                          Text(ann.title, style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 13.5)),
                          const SizedBox(height: 4),
                          Text(ann.content, style: const TextStyle(fontSize: 12, color: AppColors.textSecondary, height: 1.4)),
                          const SizedBox(height: 6),
                          Text('Oleh: ${ann.authorName ?? "Pengurus RT"} • ${ann.createdAt}', style: const TextStyle(fontSize: 10, color: AppColors.textMuted)),
                        ],
                      ),
                    )),
            ],
          ),
        ),
        const SizedBox(height: 16),

        // ── 3. CARD INFORMATIF 1: LAPORAN KEUANGAN KAS RT ──
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
                  Row(
                    children: [
                      Container(
                        padding: const EdgeInsets.all(8),
                        decoration: BoxDecoration(
                          color: const Color(0xFF059669).withValues(alpha: 0.12),
                          borderRadius: BorderRadius.circular(10),
                        ),
                        child: const Text('🏛️', style: TextStyle(fontSize: 16)),
                      ),
                      const SizedBox(width: 8),
                      Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: const [
                          Text('Buku Kas RT', style: TextStyle(fontWeight: FontWeight.w900, fontSize: 14)),
                          Text('Transparansi keuangan warga', style: TextStyle(fontSize: 10.5, color: AppColors.textMuted)),
                        ],
                      ),
                    ],
                  ),
                  if (_canManageKas)
                    ElevatedButton.icon(
                      style: ElevatedButton.styleFrom(
                        backgroundColor: const Color(0xFF059669),
                        foregroundColor: Colors.white,
                        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                        minimumSize: Size.zero,
                        tapTargetSize: MaterialTapTargetSize.shrinkWrap,
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                        elevation: 0,
                      ),
                      icon: const Icon(Icons.add, size: 14),
                      label: const Text('Catat Kas', style: TextStyle(fontSize: 11, fontWeight: FontWeight.w800)),
                      onPressed: _showRecordKasDialog,
                    ),
                ],
              ),
              const SizedBox(height: 14),

              // Saldo Box
              Container(
                width: double.infinity,
                padding: const EdgeInsets.all(16),
                decoration: BoxDecoration(
                  gradient: LinearGradient(
                    colors: [
                      const Color(0xFF064E3B).withValues(alpha: 0.06),
                      const Color(0xFF059669).withValues(alpha: 0.1),
                    ],
                  ),
                  borderRadius: BorderRadius.circular(16),
                  border: Border.all(color: const Color(0xFFA7F3D0)),
                ),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Text('TOTAL SALDO KAS RT', style: TextStyle(fontSize: 10.5, fontWeight: FontWeight.w800, color: Color(0xFF065F46), letterSpacing: 0.5)),
                    const SizedBox(height: 4),
                    Text(
                      _formatRupiah(_kasSummary.balance),
                      style: const TextStyle(fontSize: 22, fontWeight: FontWeight.w900, color: Color(0xFF065F46)),
                    ),
                  ],
                ),
              ),
              const SizedBox(height: 10),

              // Masuk vs Keluar Summary Grid
              Row(
                children: [
                  Expanded(
                    child: Container(
                      padding: const EdgeInsets.all(12),
                      decoration: BoxDecoration(
                        color: const Color(0xFFECFDF5),
                        borderRadius: BorderRadius.circular(14),
                        border: Border.all(color: const Color(0xFFA7F3D0)),
                      ),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Row(
                            children: const [
                              Icon(Icons.arrow_downward_rounded, size: 13, color: Color(0xFF059669)),
                              SizedBox(width: 4),
                              Text('Total Masuk', style: TextStyle(fontSize: 10.5, fontWeight: FontWeight.bold, color: Color(0xFF065F46))),
                            ],
                          ),
                          const SizedBox(height: 4),
                          Text(
                            _formatRupiah(_kasSummary.totalIn),
                            style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w900, color: Color(0xFF047857)),
                          ),
                        ],
                      ),
                    ),
                  ),
                  const SizedBox(width: 10),
                  Expanded(
                    child: Container(
                      padding: const EdgeInsets.all(12),
                      decoration: BoxDecoration(
                        color: const Color(0xFFFEF2F2),
                        borderRadius: BorderRadius.circular(14),
                        border: Border.all(color: const Color(0xFFFECACA)),
                      ),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Row(
                            children: const [
                              Icon(Icons.arrow_upward_rounded, size: 13, color: Color(0xFFEF4444)),
                              SizedBox(width: 4),
                              Text('Total Keluar', style: TextStyle(fontSize: 10.5, fontWeight: FontWeight.bold, color: Color(0xFF991B1B))),
                            ],
                          ),
                          const SizedBox(height: 4),
                          Text(
                            _formatRupiah(_kasSummary.totalOut),
                            style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w900, color: Color(0xFFDC2626)),
                          ),
                        ],
                      ),
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 14),

              // Recent Kas Entries
              const Text('Catatan Kas Terakhir', style: TextStyle(fontWeight: FontWeight.w800, fontSize: 12, color: AppColors.textPrimary)),
              const SizedBox(height: 8),
              if (_kasLedger.isEmpty)
                Container(
                  padding: const EdgeInsets.all(14),
                  width: double.infinity,
                  decoration: BoxDecoration(color: AppColors.bg, borderRadius: BorderRadius.circular(12)),
                  child: const Center(
                    child: Text('Belum ada transaksi kas yang dicatat.', style: TextStyle(fontSize: 11.5, color: AppColors.textMuted)),
                  ),
                )
              else
                ..._kasLedger.take(5).map((kas) => Container(
                      margin: const EdgeInsets.only(bottom: 6),
                      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
                      decoration: BoxDecoration(
                        color: AppColors.bg,
                        borderRadius: BorderRadius.circular(12),
                        border: Border.all(color: AppColors.border),
                      ),
                      child: Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          Expanded(
                            child: Row(
                              children: [
                                Container(
                                  padding: const EdgeInsets.all(6),
                                  decoration: BoxDecoration(
                                    color: kas.type == 'in' ? const Color(0xFFECFDF5) : const Color(0xFFFEF2F2),
                                    shape: BoxShape.circle,
                                  ),
                                  child: Icon(
                                    kas.type == 'in' ? Icons.arrow_downward_rounded : Icons.arrow_upward_rounded,
                                    size: 14,
                                    color: kas.type == 'in' ? const Color(0xFF059669) : const Color(0xFFEF4444),
                                  ),
                                ),
                                const SizedBox(width: 10),
                                Expanded(
                                  child: Column(
                                    crossAxisAlignment: CrossAxisAlignment.start,
                                    children: [
                                      Text(
                                        kas.category,
                                        style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 12.5),
                                      ),
                                      if (kas.description != null && kas.description!.isNotEmpty)
                                        Text(
                                          kas.description!,
                                          maxLines: 1,
                                          overflow: TextOverflow.ellipsis,
                                          style: const TextStyle(fontSize: 11, color: AppColors.textMuted),
                                        ),
                                      Text(
                                        '${kas.date}${kas.recordedByName != null ? " • oleh ${kas.recordedByName}" : ""}',
                                        style: const TextStyle(fontSize: 10, color: AppColors.textMuted),
                                      ),
                                    ],
                                  ),
                                ),
                              ],
                            ),
                          ),
                          Row(
                            children: [
                              Text(
                                '${kas.type == "in" ? "+" : "-"}${_formatRupiah(kas.amount)}',
                                style: TextStyle(
                                  fontWeight: FontWeight.w900,
                                  fontSize: 12.5,
                                  color: kas.type == 'in' ? const Color(0xFF059669) : const Color(0xFFEF4444),
                                ),
                              ),
                              if (_canManageKas) ...[
                                const SizedBox(width: 4),
                                InkWell(
                                  onTap: () => _deleteKas(kas.id),
                                  borderRadius: BorderRadius.circular(8),
                                  child: const Padding(
                                    padding: EdgeInsets.all(4),
                                    child: Icon(Icons.close_rounded, size: 16, color: AppColors.textMuted),
                                  ),
                                ),
                              ],
                            ],
                          ),
                        ],
                      ),
                    )),
            ],
          ),
        ),
        const SizedBox(height: 16),

        // ── 4. CARD INFORMATIF 2: DATA ALAT YANG BISA DIPINJAM ──
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
                  Row(
                    children: [
                      Container(
                        padding: const EdgeInsets.all(8),
                        decoration: BoxDecoration(
                          color: const Color(0xFFF59E0B).withValues(alpha: 0.12),
                          borderRadius: BorderRadius.circular(10),
                        ),
                        child: const Text('🪚', style: TextStyle(fontSize: 16)),
                      ),
                      const SizedBox(width: 8),
                      Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: const [
                          Text('Fasilitas & Pinjam Alat RT', style: TextStyle(fontWeight: FontWeight.w900, fontSize: 14)),
                          Text('Alat inventaris RT & sharing warga', style: TextStyle(fontSize: 10.5, color: AppColors.textMuted)),
                        ],
                      ),
                    ],
                  ),
                  OutlinedButton(
                    style: OutlinedButton.styleFrom(
                      foregroundColor: AppColors.primary,
                      side: const BorderSide(color: AppColors.primary),
                      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                      minimumSize: Size.zero,
                      tapTargetSize: MaterialTapTargetSize.shrinkWrap,
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                    ),
                    onPressed: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const ToolSharingScreen())),
                    child: const Text('Buka Alat →', style: TextStyle(fontSize: 11, fontWeight: FontWeight.w800)),
                  ),
                ],
              ),
              const SizedBox(height: 14),

              // Tool Stats Grid
              Row(
                children: [
                  Expanded(
                    child: Container(
                      padding: const EdgeInsets.all(12),
                      decoration: BoxDecoration(
                        color: AppColors.bg,
                        borderRadius: BorderRadius.circular(14),
                        border: Border.all(color: AppColors.border),
                      ),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          const Text('Total Inventaris', style: TextStyle(fontSize: 10.5, color: AppColors.textMuted)),
                          const SizedBox(height: 2),
                          Text('$_toolsCount Alat', style: const TextStyle(fontSize: 15, fontWeight: FontWeight.w900)),
                        ],
                      ),
                    ),
                  ),
                  const SizedBox(width: 8),
                  Expanded(
                    child: Container(
                      padding: const EdgeInsets.all(12),
                      decoration: BoxDecoration(
                        color: const Color(0xFFECFDF5),
                        borderRadius: BorderRadius.circular(14),
                        border: Border.all(color: const Color(0xFFA7F3D0)),
                      ),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          const Text('Siap Dipinjam', style: TextStyle(fontSize: 10.5, fontWeight: FontWeight.bold, color: Color(0xFF065F46))),
                          const SizedBox(height: 2),
                          Text('$_toolsAvailable Tersedia', style: const TextStyle(fontSize: 15, fontWeight: FontWeight.w900, color: Color(0xFF047857))),
                        ],
                      ),
                    ),
                  ),
                  const SizedBox(width: 8),
                  Expanded(
                    child: Container(
                      padding: const EdgeInsets.all(12),
                      decoration: BoxDecoration(
                        color: const Color(0xFFEFF6FF),
                        borderRadius: BorderRadius.circular(14),
                        border: Border.all(color: const Color(0xFFBFDBFE)),
                      ),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          const Text('Sedang Dipinjam', style: TextStyle(fontSize: 10.5, fontWeight: FontWeight.bold, color: Color(0xFF1E40AF))),
                          const SizedBox(height: 2),
                          Text('$_toolsRented Dipinjam', style: const TextStyle(fontSize: 15, fontWeight: FontWeight.w900, color: Color(0xFF2563EB))),
                        ],
                      ),
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 10),
              Container(
                padding: const EdgeInsets.all(10),
                decoration: BoxDecoration(
                  color: const Color(0xFFFEF3C7),
                  borderRadius: BorderRadius.circular(12),
                ),
                child: Row(
                  children: const [
                    Icon(Icons.info_outline_rounded, size: 16, color: Color(0xFF92400E)),
                    SizedBox(width: 8),
                    Expanded(
                      child: Text(
                        'Setiap sewa alat menyumbang kas RT untuk dana pemeliharaan dan perawatan bersama.',
                        style: TextStyle(fontSize: 10.5, color: Color(0xFF92400E), fontWeight: FontWeight.w600),
                      ),
                    ),
                  ],
                ),
              ),
            ],
          ),
        ),
        const SizedBox(height: 16),

        // ── 5. CARD INFORMATIF 3: AGENDA KEGIATAN RT ──
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
                  Row(
                    children: [
                      Container(
                        padding: const EdgeInsets.all(8),
                        decoration: BoxDecoration(
                          color: const Color(0xFF3B82F6).withValues(alpha: 0.12),
                          borderRadius: BorderRadius.circular(10),
                        ),
                        child: const Text('📅', style: TextStyle(fontSize: 16)),
                      ),
                      const SizedBox(width: 8),
                      Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: const [
                          Text('Agenda & Kegiatan RT', style: TextStyle(fontWeight: FontWeight.w900, fontSize: 14)),
                          Text('Jadwal kerja bakti, rapat, & posyandu', style: TextStyle(fontSize: 10.5, color: AppColors.textMuted)),
                        ],
                      ),
                    ],
                  ),
                  if (_canManageKas)
                    ElevatedButton.icon(
                      style: ElevatedButton.styleFrom(
                        backgroundColor: const Color(0xFF3B82F6),
                        foregroundColor: Colors.white,
                        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                        minimumSize: Size.zero,
                        tapTargetSize: MaterialTapTargetSize.shrinkWrap,
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                        elevation: 0,
                      ),
                      icon: const Icon(Icons.add, size: 14),
                      label: const Text('Agenda', style: TextStyle(fontSize: 11, fontWeight: FontWeight.w800)),
                      onPressed: _showAddActivityDialog,
                    ),
                ],
              ),
              const SizedBox(height: 14),
              if (_activities.isEmpty)
                Container(
                  padding: const EdgeInsets.all(14),
                  width: double.infinity,
                  decoration: BoxDecoration(color: AppColors.bg, borderRadius: BorderRadius.circular(12)),
                  child: const Center(
                    child: Text('Belum ada agenda kegiatan RT mendatang.', style: TextStyle(fontSize: 11.5, color: AppColors.textMuted)),
                  ),
                )
              else
                ..._activities.map((act) => Container(
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
                          Container(
                            padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 8),
                            decoration: BoxDecoration(
                              color: const Color(0xFFEFF6FF),
                              borderRadius: BorderRadius.circular(10),
                              border: Border.all(color: const Color(0xFFBFDBFE)),
                            ),
                            child: Column(
                              children: [
                                Text(
                                  act.eventDate.length >= 10 ? act.eventDate.substring(8, 10) : '📅',
                                  style: const TextStyle(fontWeight: FontWeight.w900, fontSize: 16, color: Color(0xFF1D4ED8)),
                                ),
                                Text(
                                  act.eventTime.length >= 5 ? act.eventTime.substring(0, 5) : act.eventTime,
                                  style: const TextStyle(fontSize: 9.5, fontWeight: FontWeight.bold, color: Color(0xFF2563EB)),
                                ),
                              ],
                            ),
                          ),
                          const SizedBox(width: 12),
                          Expanded(
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Row(
                                  children: [
                                    Container(
                                      padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                                      decoration: BoxDecoration(
                                        color: const Color(0xFFDBEAFE),
                                        borderRadius: BorderRadius.circular(6),
                                      ),
                                      child: Text(
                                        act.category,
                                        style: const TextStyle(fontSize: 9.5, fontWeight: FontWeight.bold, color: Color(0xFF1E40AF)),
                                      ),
                                    ),
                                    const Spacer(),
                                    if (_canManageKas)
                                      InkWell(
                                        onTap: () => _deleteActivity(act.id),
                                        borderRadius: BorderRadius.circular(6),
                                        child: const Padding(
                                          padding: EdgeInsets.all(2),
                                          child: Icon(Icons.close_rounded, size: 16, color: AppColors.textMuted),
                                        ),
                                      ),
                                  ],
                                ),
                                const SizedBox(height: 4),
                                Text(act.title, style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 13)),
                                const SizedBox(height: 2),
                                Row(
                                  children: [
                                    const Icon(Icons.location_on_outlined, size: 13, color: AppColors.textMuted),
                                    const SizedBox(width: 4),
                                    Expanded(
                                      child: Text(
                                        act.location,
                                        maxLines: 1,
                                        overflow: TextOverflow.ellipsis,
                                        style: const TextStyle(fontSize: 11, color: AppColors.textMuted),
                                      ),
                                    ),
                                  ],
                                ),
                                if (act.description != null && act.description!.isNotEmpty) ...[
                                  const SizedBox(height: 4),
                                  Text(
                                    act.description!,
                                    style: const TextStyle(fontSize: 11, color: AppColors.textSecondary),
                                  ),
                                ],
                              ],
                            ),
                          ),
                        ],
                      ),
                    )),
            ],
          ),
        ),
        const SizedBox(height: 16),

        // ── 6. CARD FORUM DISKUSI WARGA ──
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
                  Row(
                    children: [
                      Container(
                        padding: const EdgeInsets.all(8),
                        decoration: BoxDecoration(
                          color: const Color(0xFF6366F1).withValues(alpha: 0.12),
                          borderRadius: BorderRadius.circular(10),
                        ),
                        child: const Text('💬', style: TextStyle(fontSize: 16)),
                      ),
                      const SizedBox(width: 8),
                      Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: const [
                          Text('Forum Diskusi Warga', style: TextStyle(fontWeight: FontWeight.w900, fontSize: 14)),
                          Text('Ruang usul & obrolan RT', style: TextStyle(fontSize: 10.5, color: AppColors.textMuted)),
                        ],
                      ),
                    ],
                  ),
                  ElevatedButton.icon(
                    style: ElevatedButton.styleFrom(
                      backgroundColor: const Color(0xFF6366F1),
                      foregroundColor: Colors.white,
                      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                      minimumSize: Size.zero,
                      tapTargetSize: MaterialTapTargetSize.shrinkWrap,
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                      elevation: 0,
                    ),
                    icon: const Icon(Icons.edit, size: 14),
                    label: const Text('Tulis Topik', style: TextStyle(fontSize: 11, fontWeight: FontWeight.w800)),
                    onPressed: _showAddDiscussionDialog,
                  ),
                ],
              ),
              const SizedBox(height: 14),

              if (_discussions.isEmpty)
                Container(
                  padding: const EdgeInsets.all(14),
                  width: double.infinity,
                  decoration: BoxDecoration(color: AppColors.bg, borderRadius: BorderRadius.circular(12)),
                  child: const Center(
                    child: Text('Belum ada postingan di forum diskusi warga. Yuk mulai topik pertama!', style: TextStyle(fontSize: 11.5, color: AppColors.textMuted)),
                  ),
                )
              else
                ..._discussions.map((disc) => Container(
                      margin: const EdgeInsets.only(bottom: 10),
                      padding: const EdgeInsets.all(14),
                      decoration: BoxDecoration(
                        color: AppColors.bg,
                        borderRadius: BorderRadius.circular(16),
                        border: Border.all(color: AppColors.border),
                      ),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Row(
                            children: [
                              CircleAvatar(
                                radius: 14,
                                backgroundColor: const Color(0xFF6366F1).withValues(alpha: 0.15),
                                child: Text(
                                  disc.authorName != null && disc.authorName!.isNotEmpty ? disc.authorName![0].toUpperCase() : 'W',
                                  style: const TextStyle(fontSize: 11, fontWeight: FontWeight.bold, color: Color(0xFF6366F1)),
                                ),
                              ),
                              const SizedBox(width: 8),
                              Expanded(
                                child: Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    Text(
                                      '${disc.authorName ?? "Warga"}${disc.houseNumber != null ? " (Rumah ${disc.houseNumber})" : ""}',
                                      style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 12),
                                    ),
                                    Text(disc.createdAt, style: const TextStyle(fontSize: 9.5, color: AppColors.textMuted)),
                                  ],
                                ),
                              ),
                              Container(
                                padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                                decoration: BoxDecoration(color: const Color(0xFFE0E7FF), borderRadius: BorderRadius.circular(6)),
                                child: Text(disc.category, style: const TextStyle(fontSize: 9.5, fontWeight: FontWeight.bold, color: Color(0xFF4338CA))),
                              ),
                            ],
                          ),
                          const SizedBox(height: 8),
                          if (disc.title != null && disc.title!.isNotEmpty) ...[
                            Text(disc.title!, style: const TextStyle(fontWeight: FontWeight.w900, fontSize: 13.5)),
                            const SizedBox(height: 2),
                          ],
                          Text(disc.content, style: const TextStyle(fontSize: 12, color: AppColors.textSecondary, height: 1.4)),
                          const SizedBox(height: 10),
                          Row(
                            mainAxisAlignment: MainAxisAlignment.spaceBetween,
                            children: [
                              InkWell(
                                onTap: () => _showDiscussionDetailSheet(disc),
                                borderRadius: BorderRadius.circular(8),
                                child: Container(
                                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                                  decoration: BoxDecoration(
                                    color: Colors.white,
                                    borderRadius: BorderRadius.circular(8),
                                    border: Border.all(color: AppColors.border),
                                  ),
                                  child: Row(
                                    children: [
                                      const Icon(Icons.chat_bubble_outline_rounded, size: 13, color: AppColors.textMuted),
                                      const SizedBox(width: 4),
                                      Text('${disc.commentsCount} Komentar', style: const TextStyle(fontSize: 11, fontWeight: FontWeight.w700, color: AppColors.textPrimary)),
                                    ],
                                  ),
                                ),
                              ),
                              if (_isRtAdmin || disc.userId == _currentUserId)
                                InkWell(
                                  onTap: () => _deleteDiscussion(disc.id),
                                  child: const Text('Hapus', style: TextStyle(fontSize: 11, color: Colors.redAccent, fontWeight: FontWeight.w700)),
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

        // ── 7. Quick Shortcut Titip Belanja ──
        GestureDetector(
          onTap: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const ErrandScreen())),
          child: Container(
            padding: const EdgeInsets.all(16),
            decoration: BoxDecoration(
              color: AppColors.card,
              borderRadius: BorderRadius.circular(20),
              border: Border.all(color: AppColors.border),
              boxShadow: AppColors.cardShadow,
            ),
            child: Row(
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
                const SizedBox(width: 12),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: const [
                      Text('Titip Belanja Warga', style: TextStyle(fontWeight: FontWeight.w800, fontSize: 14)),
                      Text('Saling bantu belanja pasar & toko sekitar', style: TextStyle(fontSize: 11, color: AppColors.textMuted)),
                    ],
                  ),
                ),
                const Icon(Icons.chevron_right_rounded, color: AppColors.textMuted),
              ],
            ),
          ),
        ),
        const SizedBox(height: 16),

        // ── 8. Approval Queue for Admin RT ──
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
                              OutlinedButton(
                                style: OutlinedButton.styleFrom(
                                  foregroundColor: const Color(0xFFEF4444),
                                  side: const BorderSide(color: Color(0xFFFCA5A5)),
                                  padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                                  minimumSize: Size.zero,
                                  tapTargetSize: MaterialTapTargetSize.shrinkWrap,
                                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                                ),
                                onPressed: () => _verifyResident(pr.id, 'reject', pr.name),
                                child: const Text('Tolak', style: TextStyle(fontSize: 11, fontWeight: FontWeight.w800)),
                              ),
                              const SizedBox(width: 6),
                              ElevatedButton(
                                style: ElevatedButton.styleFrom(
                                  backgroundColor: const Color(0xFF059669),
                                  foregroundColor: Colors.white,
                                  padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 4),
                                  minimumSize: Size.zero,
                                  tapTargetSize: MaterialTapTargetSize.shrinkWrap,
                                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                                  elevation: 0,
                                ),
                                onPressed: () => _verifyResident(pr.id, 'approve', pr.name),
                                child: const Text('Setujui ✓', style: TextStyle(fontSize: 11, fontWeight: FontWeight.w800)),
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

        // ── 9. Verified Residents & Officers List ──
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
                  const Text('👥 Warga & Pengurus RT', style: TextStyle(fontWeight: FontWeight.w800, fontSize: 14)),
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
                          Expanded(
                            child: Row(
                              children: [
                                CircleAvatar(
                                  radius: 16,
                                  backgroundColor: r.isRtAdmin
                                      ? Colors.amber.withValues(alpha: 0.2)
                                      : (r.isTreasurer ? const Color(0xFF4F46E5).withValues(alpha: 0.15) : AppColors.primary.withValues(alpha: 0.12)),
                                  child: Text(
                                    r.name.isNotEmpty ? r.name[0].toUpperCase() : 'W',
                                    style: TextStyle(
                                      color: r.isRtAdmin ? Colors.orange[800] : (r.isTreasurer ? const Color(0xFF4F46E5) : AppColors.primary),
                                      fontWeight: FontWeight.bold,
                                      fontSize: 12,
                                    ),
                                  ),
                                ),
                                const SizedBox(width: 10),
                                Expanded(
                                  child: Column(
                                    crossAxisAlignment: CrossAxisAlignment.start,
                                    children: [
                                      Row(
                                        children: [
                                          Flexible(
                                            child: Text(
                                              r.name,
                                              style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 13),
                                              overflow: TextOverflow.ellipsis,
                                            ),
                                          ),
                                          if (r.isRtAdmin) ...[
                                            const SizedBox(width: 6),
                                            Container(
                                              padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 1.5),
                                              decoration: BoxDecoration(color: Colors.amber, borderRadius: BorderRadius.circular(6)),
                                              child: const Text('👑 Ketua RT', style: TextStyle(fontSize: 9.5, fontWeight: FontWeight.bold, color: Colors.black)),
                                            ),
                                          ] else if (r.isTreasurer) ...[
                                            const SizedBox(width: 6),
                                            Container(
                                              padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 1.5),
                                              decoration: BoxDecoration(color: const Color(0xFF4F46E5), borderRadius: BorderRadius.circular(6)),
                                              child: const Text('💼 Bendahara', style: TextStyle(fontSize: 9.5, fontWeight: FontWeight.bold, color: Colors.white)),
                                            ),
                                          ],
                                        ],
                                      ),
                                      Text('Rumah: ${r.houseNumber ?? "-"} • ${r.residenceStatus == "permanent" ? "Tetap" : "Domisili"}', style: const TextStyle(fontSize: 11, color: AppColors.textMuted)),
                                    ],
                                  ),
                                ),
                              ],
                            ),
                          ),

                          // RT Admin action to promote/demote Bendahara
                          if (_isRtAdmin && !r.isRtAdmin)
                            PopupMenuButton<String>(
                              icon: const Icon(Icons.more_vert_rounded, size: 18, color: AppColors.textMuted),
                              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                              onSelected: (val) {
                                if (val == 'make_treasurer') {
                                  _changeMemberRole(r.id, 'rt_treasurer', r.name);
                                } else if (val == 'remove_treasurer') {
                                  _changeMemberRole(r.id, 'user', r.name);
                                }
                              },
                              itemBuilder: (ctx) => [
                                if (!r.isTreasurer)
                                  const PopupMenuItem(
                                    value: 'make_treasurer',
                                    child: Row(
                                      children: [
                                        Icon(Icons.badge_outlined, size: 16, color: Color(0xFF4F46E5)),
                                        SizedBox(width: 8),
                                        Text('Jadikan Bendahara RT', style: TextStyle(fontSize: 12.5, fontWeight: FontWeight.w600)),
                                      ],
                                    ),
                                  )
                                else
                                  const PopupMenuItem(
                                    value: 'remove_treasurer',
                                    child: Row(
                                      children: [
                                        Icon(Icons.person_remove_outlined, size: 16, color: Colors.redAccent),
                                        SizedBox(width: 8),
                                        Text('Hapus Jabatan Bendahara', style: TextStyle(fontSize: 12.5, fontWeight: FontWeight.w600, color: Colors.redAccent)),
                                      ],
                                    ),
                                  ),
                              ],
                            )
                          else
                            Container(
                              padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                              decoration: BoxDecoration(
                                color: r.residenceStatus == 'permanent' ? AppColors.primary.withValues(alpha: 0.1) : Colors.blue.withValues(alpha: 0.1),
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
