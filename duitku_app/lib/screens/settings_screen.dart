import 'package:flutter/material.dart';
import 'package:image_picker/image_picker.dart';
import 'package:provider/provider.dart';

import '../config/api_config.dart';
import '../models/category.dart';
import '../models/user.dart';
import '../providers/auth_provider.dart';
import '../services/api_service.dart';
import '../services/update_checker_service.dart';
import '../theme.dart';
import '../utils/format.dart';
import '../widgets/category_icon.dart';
import 'backup/backup_restore_screen.dart';
import 'developer_screen.dart';
import 'export/export_screen.dart';
import 'recurring/recurring_screen.dart';
import 'savings/savings_screen.dart';

class SettingsScreen extends StatefulWidget {
  const SettingsScreen({super.key});

  @override
  State<SettingsScreen> createState() => _SettingsScreenState();
}

class _SettingsScreenState extends State<SettingsScreen> {
  bool _loading = true;
  Map<String, dynamic>? _data;

  String get _currency => _data?['currency']?.toString() ?? 'IDR';
  String get _symbol => _data?['symbol']?.toString() ?? 'Rp';
  double get _budget => Fmt.toDouble(_data?['budget']);

  List<Category> get _categories => (_data?['categories'] as List<dynamic>? ?? [])
      .map((e) => Category.fromJson(e as Map<String, dynamic>))
      .toList();
  List<dynamic> get _recurring => _data?['recurring'] as List<dynamic>? ?? [];

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() => _loading = true);
    try {
      final res = await ApiService.instance.settings();
      if (!mounted) return;
      final user = res['user'] as Map<String, dynamic>?;
      if (user != null) {
        final current = context.read<AuthProvider>().user;
        if (current != null) {
          context.read<AuthProvider>().updateUser(User(
                id: current.id,
                name: user['name']?.toString() ?? current.name,
                email: user['email']?.toString() ?? current.email,
                initials: user['initials']?.toString() ?? current.initials,
                color: user['color']?.toString() ?? current.color,
                avatarImage: user['avatarImage']?.toString() ?? current.avatarImage,
              ));
        }
      }
      setState(() {
        _data = res;
        _loading = false;
      });
    } catch (_) {
      if (!mounted) return;
      setState(() => _loading = false);
    }
  }

  Future<void> _editProfile() async {
    final auth = context.read<AuthProvider>();
    final user = auth.user;
    if (user == null) return;
    final nameCtrl = TextEditingController(text: user.name);
    final emailCtrl = TextEditingController(text: user.email);
    final passCtrl = TextEditingController();

    final ok = await showModalBottomSheet<bool>(
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
              const Text('Edit Profil',
                  textAlign: TextAlign.center,
                  style: TextStyle(fontSize: 17, fontWeight: FontWeight.w800, color: AppColors.textPrimary)),
              const SizedBox(height: 16),
              TextField(controller: nameCtrl, decoration: const InputDecoration(labelText: 'NAMA')),
              const SizedBox(height: 14),
              TextField(controller: emailCtrl, decoration: const InputDecoration(labelText: 'EMAIL')),
              const SizedBox(height: 14),
              TextField(
                controller: passCtrl,
                obscureText: true,
                decoration: const InputDecoration(labelText: 'PASSWORD BARU (OPSIONAL)'),
              ),
              const SizedBox(height: 20),
              FilledButton(
                onPressed: () async {
                  try {
                    await ApiService.instance.saveProfile(
                      name: nameCtrl.text.trim(),
                      email: emailCtrl.text.trim(),
                      password: passCtrl.text,
                    );
                    if (ctx.mounted) Navigator.pop(ctx, true);
                  } on ApiException catch (e) {
                    if (ctx.mounted) {
                      ScaffoldMessenger.of(ctx).showSnackBar(SnackBar(content: Text(e.message)));
                    }
                  }
                },
                child: const Text('Simpan'),
              ),
            ],
          ),
        ),
      ),
    );
    if (ok == true) _load();
  }

  Future<void> _pickAvatar() async {
    final picker = ImagePicker();
    final img = await picker.pickImage(source: ImageSource.gallery, maxWidth: 1024, imageQuality: 80);
    if (img == null) return;
    final b64 = await ApiService.instance.base64FromFile(img.path);
    if (b64 == null) return;
    try {
      await ApiService.instance.saveAvatar('data:image/jpeg;base64,$b64');
      _load();
    } on ApiException catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(e.message)));
    }
  }

  Future<void> _changeCurrency() async {
    const currencies = [
      ('IDR', 'Rupiah', 'Rp'),
      ('USD', 'Dollar AS', r'$'),
      ('SGD', 'Dollar Singapura', r'S$'),
      ('MYR', 'Ringgit Malaysia', 'RM'),
    ];
    final selected = await showModalBottomSheet<String>(
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
              child: Text('Pilih Mata Uang',
                  style: TextStyle(fontSize: 16, fontWeight: FontWeight.w800, color: AppColors.textPrimary)),
            ),
            ...currencies.map((c) => ListTile(
                  leading: Text(c.$3, style: const TextStyle(fontSize: 20)),
                  title: Text(c.$2, style: const TextStyle(fontWeight: FontWeight.w600)),
                  trailing: _currency == c.$1 ? const Icon(Icons.check_circle, color: AppColors.primary) : null,
                  onTap: () => Navigator.pop(ctx, c.$1),
                )),
          ],
        ),
      ),
    );
    if (selected == null || selected == _currency) return;
    try {
      await ApiService.instance.saveCurrency(selected);
      _load();
    } on ApiException catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(e.message)));
    }
  }

  Future<void> _editBudget() async {
    final ctrl = TextEditingController(text: _budget > 0 ? Fmt.money0(_budget) : '');
    final ok = await showModalBottomSheet<bool>(
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
              const Text('Budget Bulan Ini',
                  textAlign: TextAlign.center,
                  style: TextStyle(fontSize: 17, fontWeight: FontWeight.w800, color: AppColors.textPrimary)),
              const SizedBox(height: 6),
              Text(Fmt.monthLabel(_data?['monthKey']?.toString() ?? ''),
                  textAlign: TextAlign.center,
                  style: const TextStyle(fontSize: 12, color: AppColors.textMuted)),
              const SizedBox(height: 16),
              TextField(
                controller: ctrl,
                keyboardType: TextInputType.number,
                decoration: const InputDecoration(labelText: 'NOMINAL BUDGET', prefixText: 'Rp '),
              ),
              const SizedBox(height: 20),
              FilledButton(
                onPressed: () async {
                  final amount = double.tryParse(Fmt.parseAmount(ctrl.text)) ?? 0;
                  try {
                    await ApiService.instance.saveBudget(amount);
                    if (ctx.mounted) Navigator.pop(ctx, true);
                  } on ApiException catch (e) {
                    if (ctx.mounted) {
                      ScaffoldMessenger.of(ctx).showSnackBar(SnackBar(content: Text(e.message)));
                    }
                  }
                },
                child: const Text('Simpan'),
              ),
            ],
          ),
        ),
      ),
    );
    if (ok == true) _load();
  }



  Future<void> _addCategory() async {
    final nameCtrl = TextEditingController();
    String type = 'expense';
    String icon = 'other';
    String color = '#6B7280';

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
                const Text('Kategori Baru',
                    textAlign: TextAlign.center,
                    style: TextStyle(fontSize: 17, fontWeight: FontWeight.w800, color: AppColors.textPrimary)),
                const SizedBox(height: 16),
                Container(
                  padding: const EdgeInsets.all(4),
                  decoration: BoxDecoration(
                    color: AppColors.bg,
                    borderRadius: BorderRadius.circular(12),
                  ),
                  child: Row(
                    children: [
                      Expanded(child: _typeBtn('Pengeluaran', 'expense', type, () => setSheetState(() => type = 'expense'))),
                      Expanded(child: _typeBtn('Pemasukan', 'income', type, () => setSheetState(() => type = 'income'))),
                    ],
                  ),
                ),
                const SizedBox(height: 14),
                TextField(controller: nameCtrl, decoration: const InputDecoration(labelText: 'NAMA KATEGORI')),
                const SizedBox(height: 14),
                Wrap(
                  spacing: 8,
                  runSpacing: 8,
                  children: ['food', 'transport', 'utilities', 'shopping', 'fun', 'health', 'home', 'salary', 'freelance', 'gift', 'other']
                      .map((e) {
                    final sel = icon == e;
                    return ChoiceChip(
                      avatar: Icon(categoryIcon(e), size: 16, color: sel ? Colors.white : AppColors.textSecondary),
                      label: const Text(''),
                      selected: sel,
                      onSelected: (_) => setSheetState(() => icon = e),
                      selectedColor: AppColors.primary,
                      backgroundColor: AppColors.bg,
                      showCheckmark: false,
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                    );
                  }).toList(),
                ),
                const SizedBox(height: 14),
                Wrap(
                  spacing: 8,
                  runSpacing: 8,
                  children: ['#0AA956', '#2563EB', '#8B5CF6', '#DC2626', '#F59E0B', '#0D9488', '#EC4899', '#64748B'].map((e) {
                    final sel = color == e;
                    return GestureDetector(
                      onTap: () => setSheetState(() => color = e),
                      child: Container(
                        width: 30,
                        height: 30,
                        decoration: BoxDecoration(
                          color: parseColor(e),
                          shape: BoxShape.circle,
                          border: Border.all(
                            color: sel ? AppColors.textPrimary : Colors.transparent,
                            width: 2.5,
                          ),
                        ),
                      ),
                    );
                  }).toList(),
                ),
                const SizedBox(height: 20),
                FilledButton(
                  onPressed: () {
                    final name = nameCtrl.text.trim();
                    if (name.isEmpty) return;
                    Navigator.pop(ctx, {'name': name, 'type': type, 'icon': icon, 'color': color});
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
      try {
        await ApiService.instance.storeCategory(
          name: saved['name'] as String,
          type: saved['type'] as String,
          icon: saved['icon'] as String,
          color: saved['color'] as String,
        );
        _load();
      } on ApiException catch (e) {
        if (!mounted) return;
        ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(e.message)));
      }
    }
  }

  Widget _typeBtn(String label, String value, String current, VoidCallback onTap) {
    final active = current == value;
    final color = value == 'expense' ? AppColors.expense : AppColors.income;
    return GestureDetector(
      onTap: onTap,
      child: Container(
        padding: const EdgeInsets.symmetric(vertical: 10),
        decoration: BoxDecoration(
          color: active ? color : Colors.transparent,
          borderRadius: BorderRadius.circular(9),
        ),
        child: Text(label,
            textAlign: TextAlign.center,
            style: TextStyle(
                fontSize: 13,
                fontWeight: FontWeight.w700,
                color: active ? Colors.white : AppColors.textSecondary)),
      ),
    );
  }

  Future<void> _deleteCategory(Category c) async {
    final ok = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        title: const Text('Hapus kategori?'),
        content: Text('Kategori "${c.name}" akan dihapus.'),
        actions: [
          TextButton(onPressed: () => Navigator.pop(ctx, false), child: const Text('Batal')),
          TextButton(
            onPressed: () => Navigator.pop(ctx, true),
            style: TextButton.styleFrom(foregroundColor: AppColors.expense),
            child: const Text('Hapus'),
          ),
        ],
      ),
    );
    if (ok != true) return;
    try {
      await ApiService.instance.deleteCategory(c.id);
      _load();
    } on ApiException catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(e.message)));
    }
  }



  void _confirmLogout() {
    showDialog<void>(
      context: context,
      builder: (ctx) => AlertDialog(
        title: const Text('Keluar?'),
        content: const Text('Anda akan keluar dari akun ini.'),
        actions: [
          TextButton(onPressed: () => Navigator.pop(ctx), child: const Text('Batal')),
          FilledButton(
            onPressed: () {
              Navigator.pop(ctx);
              context.read<AuthProvider>().logout();
            },
            child: const Text('Keluar'),
          ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final auth = context.watch<AuthProvider>();
    final user = auth.user;

    return Scaffold(
      backgroundColor: AppColors.bg,
      appBar: AppBar(title: const Text('Pengaturan')),
      body: _loading
          ? const Center(child: CircularProgressIndicator())
          : RefreshIndicator(
              onRefresh: _load,
              child: ListView(
                padding: const EdgeInsets.fromLTRB(16, 8, 16, 100),
                children: [
                  // Profile
                  Container(
                    padding: const EdgeInsets.all(16),
                    decoration: BoxDecoration(
                      color: AppColors.card,
                      borderRadius: BorderRadius.circular(18),
                      border: Border.all(color: AppColors.border),
        boxShadow: AppColors.cardShadow,
                    ),
                    child: Row(
                      children: [
                        GestureDetector(
                          onTap: _pickAvatar,
                          child: Container(
                            width: 52,
                            height: 52,
                            decoration: BoxDecoration(
                              color: parseColor(user?.color ?? '#2D5A27'),
                              shape: BoxShape.circle,
                            ),
                            child: user?.avatarImage != null
                                ? ClipOval(
                                    child: Image.network(
                                      '${ApiConfig.baseUrl}${user!.avatarImage}',
                                      fit: BoxFit.cover,
                                      width: 52,
                                      height: 52,
                                      errorBuilder: (_, _, _) => Center(
                                        child: Text(user.initials,
                                            style: const TextStyle(color: Colors.white, fontWeight: FontWeight.w700)),
                                      ),
                                    ),
                                  )
                                : Center(
                                    child: Text(user?.initials ?? 'U',
                                        style: const TextStyle(color: Colors.white, fontWeight: FontWeight.w700, fontSize: 16)),
                                  ),
                          ),
                        ),
                        const SizedBox(width: 14),
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(user?.name ?? '',
                                  style: const TextStyle(fontSize: 16, fontWeight: FontWeight.w800, color: AppColors.textPrimary)),
                              Text(user?.email ?? '',
                                  style: const TextStyle(fontSize: 12, color: AppColors.textMuted)),
                              const SizedBox(height: 4),
                              const Text('Ketuk foto untuk ganti avatar',
                                  style: TextStyle(fontSize: 10, color: AppColors.textMuted)),
                            ],
                          ),
                        ),
                        IconButton(
                          onPressed: _editProfile,
                          icon: const Icon(Icons.edit_outlined, color: AppColors.primary),
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(height: 16),
                  const Text('PREFERENSI',
                      style: TextStyle(fontSize: 11, fontWeight: FontWeight.w700, letterSpacing: .5, color: AppColors.textMuted)),
                  const SizedBox(height: 8),
                  _card(
                    ListTile(
                      leading: _icon(Icons.currency_exchange),
                      title: const Text('Mata Uang', style: TextStyle(fontSize: 14, fontWeight: FontWeight.w600)),
                      subtitle: Text('$_currency ($_symbol)', style: const TextStyle(fontSize: 12)),
                      trailing: const Icon(Icons.chevron_right, color: AppColors.textMuted),
                      onTap: _changeCurrency,
                    ),
                  ),
                  _card(
                    ListTile(
                      leading: _icon(Icons.track_changes),
                      title: const Text('Budget Bulan Ini', style: TextStyle(fontSize: 14, fontWeight: FontWeight.w600)),
                      subtitle: Text(_budget > 0 ? Fmt.money(_budget, symbol: _symbol) : 'Belum diatur',
                          style: const TextStyle(fontSize: 12)),
                      trailing: const Icon(Icons.chevron_right, color: AppColors.textMuted),
                      onTap: _editBudget,
                    ),
                  ),
                  _card(
                    ListTile(
                      leading: _icon(Icons.savings_outlined, color: const Color(0xFFC026D3)),
                      title: const Text('Target Menabung', style: TextStyle(fontSize: 14, fontWeight: FontWeight.w600)),
                      subtitle: const Text('Multi-goal · Setor tabungan berkala', style: TextStyle(fontSize: 12)),
                      trailing: const Icon(Icons.chevron_right, color: AppColors.textMuted),
                      onTap: () async {
                        await Navigator.push(context, MaterialPageRoute(builder: (_) => const SavingsScreen()));
                        _load();
                      },
                    ),
                  ),
                  _card(
                    ListTile(
                      leading: _icon(Icons.sync_rounded, color: const Color(0xFF0F766E)),
                      title: const Text('Transaksi Rutin', style: TextStyle(fontSize: 14, fontWeight: FontWeight.w600)),
                      subtitle: Text(
                        _recurring.isNotEmpty
                            ? '${_recurring.length} transaksi aktif'
                            : 'Kelola transaksi otomatis',
                        style: const TextStyle(fontSize: 12),
                      ),
                      trailing: const Icon(Icons.chevron_right, color: AppColors.textMuted),
                      onTap: () async {
                        await Navigator.push(context, MaterialPageRoute(builder: (_) => const RecurringScreen()));
                        _load();
                      },
                    ),
                  ),
                  _card(
                    ListTile(
                      leading: _icon(Icons.description_outlined, color: const Color(0xFFE11D48)),
                      title: const Text('Export Laporan', style: TextStyle(fontSize: 14, fontWeight: FontWeight.w600)),
                      subtitle: const Text('Cetak PDF & unduh CSV Excel', style: TextStyle(fontSize: 12)),
                      trailing: const Icon(Icons.chevron_right, color: AppColors.textMuted),
                      onTap: () {
                        Navigator.push(context, MaterialPageRoute(builder: (_) => const ExportScreen()));
                      },
                    ),
                  ),
                  const SizedBox(height: 16),
                  const Text('KATEGORI',
                      style: TextStyle(fontSize: 11, fontWeight: FontWeight.w700, letterSpacing: .5, color: AppColors.textMuted)),
                  const SizedBox(height: 8),
                  ..._categories.map((c) => _card(
                        ListTile(
                          leading: Container(
                            width: 36,
                            height: 36,
                            decoration: BoxDecoration(
                              color: parseColor(c.color).withValues(alpha: .12),
                              borderRadius: BorderRadius.circular(10),
                            ),
                            child: Icon(categoryIcon(c.icon), color: parseColor(c.color), size: 18),
                          ),
                          title: Text(c.name, style: const TextStyle(fontSize: 14, fontWeight: FontWeight.w600)),
                          trailing: Row(
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              Container(
                                padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                                decoration: BoxDecoration(
                                  color: (c.type == 'income' ? AppColors.income : AppColors.expense).withValues(alpha: .1),
                                  borderRadius: BorderRadius.circular(10),
                                ),
                                child: Text(c.type == 'income' ? 'Masuk' : 'Keluar',
                                    style: TextStyle(
                                        fontSize: 9,
                                        fontWeight: FontWeight.w700,
                                        color: c.type == 'income' ? AppColors.income : AppColors.expense)),
                              ),
                              if (!c.isDefault)
                                IconButton(
                                  onPressed: () => _deleteCategory(c),
                                  icon: const Icon(Icons.delete_outline, size: 20, color: AppColors.textMuted),
                                ),
                            ],
                          ),
                        ),
                      )),
                  _card(
                    ListTile(
                      leading: _icon(Icons.add_circle_outline, color: AppColors.primary),
                      title: const Text('Tambah Kategori',
                          style: TextStyle(fontSize: 14, fontWeight: FontWeight.w700, color: AppColors.primary)),
                      onTap: _addCategory,
                    ),
                  ),

                  const SizedBox(height: 16),
                  const Text('CADANGAN & INFORMASI',
                      style: TextStyle(fontSize: 11, fontWeight: FontWeight.w700, letterSpacing: .5, color: AppColors.textMuted)),
                  const SizedBox(height: 8),
                  _card(
                    ListTile(
                      leading: _icon(Icons.backup_rounded, color: const Color(0xFF2563EB)),
                      title: const Text('Cadangan & Pemulihan (JSON)', style: TextStyle(fontSize: 14, fontWeight: FontWeight.w600)),
                      subtitle: const Text('Ekspor & impor data cadangan', style: TextStyle(fontSize: 12)),
                      trailing: const Icon(Icons.chevron_right, color: AppColors.textMuted),
                      onTap: () async {
                        final ok = await Navigator.push<bool>(context, MaterialPageRoute(builder: (_) => const BackupRestoreScreen()));
                        if (ok == true) _load();
                      },
                    ),
                  ),
                  _card(
                    ListTile(
                      leading: _icon(Icons.system_update_rounded, color: const Color(0xFFE11D48)),
                      title: const Text('Pembaruan Aplikasi', style: TextStyle(fontSize: 14, fontWeight: FontWeight.w600)),
                      subtitle: const Text('Periksa rilis terbaru di GitHub Releases', style: TextStyle(fontSize: 12)),
                      trailing: const Icon(Icons.chevron_right, color: AppColors.textMuted),
                      onTap: () => UpdateCheckerService.instance.checkAndShowUpdateDialog(context, isManualCheck: true),
                    ),
                  ),
                  _card(
                    ListTile(
                      leading: _icon(Icons.person_pin_rounded, color: AppColors.primary),
                      title: const Text('Tentang Developer', style: TextStyle(fontSize: 14, fontWeight: FontWeight.w600)),
                      subtitle: const Text('Zusfan Mashuri · Profil & Kontak', style: TextStyle(fontSize: 12)),
                      trailing: const Icon(Icons.chevron_right, color: AppColors.textMuted),
                      onTap: () {
                        Navigator.push(context, MaterialPageRoute(builder: (_) => const DeveloperScreen()));
                      },
                    ),
                  ),

                  const SizedBox(height: 24),
                  OutlinedButton.icon(
                    onPressed: _confirmLogout,
                    style: OutlinedButton.styleFrom(
                      foregroundColor: AppColors.expense,
                      side: const BorderSide(color: AppColors.expense),
                      minimumSize: const Size.fromHeight(50),
                    ),
                    icon: const Icon(Icons.logout, size: 18),
                    label: const Text('Keluar', style: TextStyle(fontWeight: FontWeight.w700)),
                  ),
                ],
              ),
            ),
    );
  }

  Widget _icon(IconData icon, {Color? color}) {
    return Container(
      width: 36,
      height: 36,
      decoration: BoxDecoration(
        color: (color ?? AppColors.primaryLight).withValues(alpha: .1),
        borderRadius: BorderRadius.circular(10),
      ),
      child: Icon(icon, color: color ?? AppColors.primaryLight, size: 18),
    );
  }

  Widget _card(Widget child) {
    return Container(
      margin: const EdgeInsets.only(bottom: 8),
      decoration: BoxDecoration(
        color: AppColors.card,
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: AppColors.border),
        boxShadow: AppColors.cardShadow,
      ),
      child: child,
    );
  }
}
