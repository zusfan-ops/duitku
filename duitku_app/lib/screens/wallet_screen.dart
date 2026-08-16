import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../models/wallet.dart';
import '../providers/app_data_provider.dart';
import '../services/api_service.dart';
import '../theme.dart';
import '../utils/format.dart';
import '../widgets/category_icon.dart';

class WalletScreen extends StatefulWidget {
  const WalletScreen({super.key});

  @override
  State<WalletScreen> createState() => _WalletScreenState();
}

class _WalletScreenState extends State<WalletScreen> {
  bool _loading = true;
  bool _saving = false;
  String _symbol = 'Rp';

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() => _loading = true);
    try {
      final res = await ApiService.instance.wallets();
      if (!mounted) return;
      final data = context.read<AppDataProvider>();
      await data.reloadWallets();
      _symbol = res['symbol']?.toString() ?? _symbol;
      setState(() => _loading = false);
    } catch (_) {
      if (!mounted) return;
      setState(() => _loading = false);
    }
  }

  Future<void> _openAddEdit({Wallet? wallet}) async {
    final nameCtrl = TextEditingController(text: wallet?.name ?? '');
    final amountCtrl = TextEditingController(
        text: wallet == null ? '' : Fmt.money0(wallet.initialBalance));
    String type = wallet?.type ?? 'bank';
    String icon = wallet?.icon ?? '🏦';
    String color = wallet?.color ?? '#0AA956';

    final saved = await showModalBottomSheet<Map<String, dynamic>>(
      context: context,
      isScrollControlled: true,
      backgroundColor: AppColors.card,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
      ),
      builder: (ctx) => StatefulBuilder(
        builder: (ctx, setSheetState) => Padding(
          padding: EdgeInsets.only(
              bottom: MediaQuery.of(ctx).viewInsets.bottom),
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
                Text(wallet == null ? 'Rekening Baru' : 'Edit Rekening',
                    textAlign: TextAlign.center,
                    style: const TextStyle(
                        fontSize: 17, fontWeight: FontWeight.w800, color: AppColors.textPrimary)),
                const SizedBox(height: 16),
                TextField(
                  controller: nameCtrl,
                  decoration: const InputDecoration(
                      labelText: 'NAMA', hintText: 'contoh: BCA, GoPay, Dompet'),
                ),
                const SizedBox(height: 14),
                DropdownButtonFormField<String>(
                  initialValue: type,
                  decoration: const InputDecoration(labelText: 'JENIS'),
                  items: const [
                    DropdownMenuItem(value: 'bank', child: Text('🏦 Bank')),
                    DropdownMenuItem(value: 'e-wallet', child: Text('📱 E-Wallet')),
                    DropdownMenuItem(value: 'cash', child: Text('💵 Tunai')),
                    DropdownMenuItem(value: 'savings_home', child: Text('🏠 Tabungan')),
                  ],
                  onChanged: (v) => setSheetState(() => type = v!),
                ),
                const SizedBox(height: 14),
                TextField(
                  controller: amountCtrl,
                  keyboardType: TextInputType.number,
                  decoration: const InputDecoration(
                      labelText: 'SALDO AWAL',
                      prefixText: 'Rp '),
                ),
                const SizedBox(height: 14),
                const Text('IKON', style: TextStyle(
                    fontSize: 11, fontWeight: FontWeight.w700, letterSpacing: .5, color: AppColors.textMuted)),
                const SizedBox(height: 8),
                Wrap(
                  spacing: 8,
                  runSpacing: 8,
                  children: ['💵', '🏦', '🏧', '📱', '💳', '👛', '🏠', '💰', '🪙', '✈️'].map((e) {
                    final sel = icon == e;
                    return ChoiceChip(
                      label: Text(e, style: const TextStyle(fontSize: 18)),
                      selected: sel,
                      onSelected: (_) => setSheetState(() => icon = e),
                      selectedColor: AppColors.primary.withValues(alpha: .15),
                      showCheckmark: false,
                      backgroundColor: AppColors.bg,
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                    );
                  }).toList(),
                ),
                const SizedBox(height: 14),
                const Text('WARNA', style: TextStyle(
                    fontSize: 11, fontWeight: FontWeight.w700, letterSpacing: .5, color: AppColors.textMuted)),
                const SizedBox(height: 8),
                Wrap(
                  spacing: 8,
                  runSpacing: 8,
                  children: ['#0AA956', '#2D5A27', '#2563EB', '#8B5CF6', '#DC2626', '#F59E0B', '#0D9488', '#64748B'].map((e) {
                    final sel = color == e;
                    return GestureDetector(
                      onTap: () => setSheetState(() => color = e),
                      child: Container(
                        width: 32,
                        height: 32,
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
                  onPressed: () async {
                    final name = nameCtrl.text.trim();
                    if (name.isEmpty) return;
                    final amount = double.tryParse(Fmt.parseAmount(amountCtrl.text)) ?? 0;
                    Navigator.pop(ctx, {'name': name, 'type': type, 'icon': icon, 'color': color, 'amount': amount});
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
      setState(() => _saving = true);
      try {
        await ApiService.instance.storeWallet(
          id: wallet?.id,
          name: saved['name'] as String,
          type: saved['type'] as String,
          icon: saved['icon'] as String,
          color: saved['color'] as String,
          initialBalance: saved['amount'] as double,
        );
        if (!mounted) return;
        ScaffoldMessenger.of(context)
            .showSnackBar(const SnackBar(content: Text('Rekening disimpan')));
      } on ApiException catch (e) {
        if (!mounted) return;
        ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(e.message)));
      } finally {
        if (mounted) setState(() => _saving = false);
      }
      _load();
    }
  }

  Future<void> _openTransfer() async {
    final data = context.read<AppDataProvider>();
    if (data.wallets.length < 2) {
      ScaffoldMessenger.of(context)
          .showSnackBar(const SnackBar(content: Text('Butuh minimal 2 rekening untuk transfer.')));
      return;
    }
    int? fromId = data.wallets.first.id;
    int? toId = data.wallets.length > 1 ? data.wallets[1].id : null;
    final amountCtrl = TextEditingController();
    final noteCtrl = TextEditingController();

    final ok = await showModalBottomSheet<bool>(
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
                const Text('Transfer Antar Rekening',
                    textAlign: TextAlign.center,
                    style: TextStyle(fontSize: 17, fontWeight: FontWeight.w800, color: AppColors.textPrimary)),
                const SizedBox(height: 16),
                DropdownButtonFormField<int?>(
                  initialValue: fromId,
                  decoration: const InputDecoration(labelText: 'DARI'),
                  items: data.wallets.map((w) =>
                      DropdownMenuItem<int?>(value: w.id, child: Text('${w.icon} ${w.name}'))).toList(),
                  onChanged: (v) => setSheetState(() => fromId = v),
                ),
                const SizedBox(height: 14),
                DropdownButtonFormField<int?>(
                  initialValue: toId,
                  decoration: const InputDecoration(labelText: 'KE'),
                  items: data.wallets.map((w) =>
                      DropdownMenuItem<int?>(value: w.id, child: Text('${w.icon} ${w.name}'))).toList(),
                  onChanged: (v) => setSheetState(() => toId = v),
                ),
                const SizedBox(height: 14),
                TextField(
                  controller: amountCtrl,
                  keyboardType: TextInputType.number,
                  decoration: const InputDecoration(labelText: 'JUMLAH', prefixText: 'Rp '),
                ),
                const SizedBox(height: 14),
                TextField(
                  controller: noteCtrl,
                  decoration: const InputDecoration(labelText: 'CATATAN (OPSIONAL)'),
                ),
                const SizedBox(height: 20),
                FilledButton(
                  onPressed: () async {
                    final amount = double.tryParse(Fmt.parseAmount(amountCtrl.text)) ?? 0;
                    if (fromId == null || toId == null || fromId == toId || amount <= 0) return;
                    try {
                      await ApiService.instance.transferWallet(
                        fromId: fromId!,
                        toId: toId!,
                        amount: amount,
                        note: noteCtrl.text,
                      );
                      if (ctx.mounted) Navigator.pop(ctx, true);
                    } on ApiException catch (e) {
                      if (ctx.mounted) {
                        ScaffoldMessenger.of(ctx).showSnackBar(SnackBar(content: Text(e.message)));
                      }
                    }
                  },
                  child: const Text('Transfer'),
                ),
              ],
            ),
          ),
        ),
      ),
    );
    if (ok == true) {
      _load();
    }
  }

  @override
  Widget build(BuildContext context) {
    final data = context.watch<AppDataProvider>();
    final wallets = data.wallets;
    final total = wallets.fold<double>(0, (s, w) => s + w.balance);
    final symbol = _symbol;

    return Scaffold(
      backgroundColor: AppColors.bg,
      appBar: AppBar(title: const Text('Rekening & Dompet')),
      floatingActionButton: _saving
          ? null
          : FloatingActionButton.extended(
              onPressed: _openAddEdit,
              backgroundColor: AppColors.primary,
              icon: const Icon(Icons.add, color: Colors.white),
              label: const Text('Tambah', style: TextStyle(color: Colors.white, fontWeight: FontWeight.w700)),
            ),
      body: _loading
          ? const Center(child: CircularProgressIndicator())
          : RefreshIndicator(
              onRefresh: _load,
              child: ListView(
                padding: const EdgeInsets.fromLTRB(16, 8, 16, 100),
                children: [
                  Container(
                    padding: const EdgeInsets.all(18),
                    decoration: BoxDecoration(
                      gradient: const LinearGradient(
                        colors: [Color(0xFF043D22), Color(0xFF0AA956)],
                        begin: Alignment.topLeft,
                        end: Alignment.bottomRight,
                      ),
                      borderRadius: BorderRadius.circular(20),
                      boxShadow: [
                        BoxShadow(color: const Color(0xFF076836).withValues(alpha: .28), blurRadius: 20, offset: const Offset(0, 8)),
                      ],
                    ),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        const Text('TOTAL SALDO',
                            style: TextStyle(fontSize: 11, fontWeight: FontWeight.w700, letterSpacing: .6, color: Colors.white54)),
                        const SizedBox(height: 4),
                        Text(Fmt.money(total, symbol: symbol),
                            style: const TextStyle(fontSize: 26, fontWeight: FontWeight.w800, color: Colors.white)),
                        const SizedBox(height: 12),
                        OutlinedButton.icon(
                          onPressed: _openTransfer,
                          style: OutlinedButton.styleFrom(
                            foregroundColor: Colors.white,
                            side: const BorderSide(color: Colors.white38),
                            minimumSize: const Size.fromHeight(40),
                          ),
                          icon: const Icon(Icons.swap_horiz, size: 18),
                          label: const Text('Transfer Antar Rekening'),
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(height: 16),
                  if (wallets.isEmpty)
                    const Padding(
                      padding: EdgeInsets.symmetric(vertical: 60),
                      child: Column(
                        children: [
                          Icon(Icons.account_balance_wallet, size: 44, color: AppColors.textMuted),
                          SizedBox(height: 10),
                          Text('Belum ada rekening',
                              style: TextStyle(fontSize: 15, fontWeight: FontWeight.w700, color: AppColors.textSecondary)),
                          SizedBox(height: 4),
                          Text('Tekan tombol Tambah untuk membuat rekening pertama.',
                              style: TextStyle(fontSize: 12, color: AppColors.textMuted)),
                        ],
                      ),
                    )
                  else
                    ...wallets.map((w) => _WalletListTile(
                          wallet: w,
                          symbol: symbol,
                          isDefault: w.isDefault,
                          onTap: () => _setDefault(w),
                          onEdit: () => _openAddEdit(wallet: w),
                          onDelete: () => _deleteWallet(w),
                          onMembers: () => _openMembersDialog(w),
                        )),
                ],
              ),
            ),
    );
  }

  Future<void> _openMembersDialog(Wallet w) async {
    final emailCtrl = TextEditingController();
    final nameCtrl = TextEditingController();
    String role = 'editor';
    List<dynamic> members = [];
    bool loadingMembers = true;

    await showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: AppColors.card,
      shape: const RoundedRectangleBorder(borderRadius: BorderRadius.vertical(top: Radius.circular(20))),
      builder: (ctx) => StatefulBuilder(
        builder: (ctx, setSheetState) {
          Future<void> fetchMembers() async {
            try {
              final res = await ApiService.instance.getWalletMembers(w.id);
              if (ctx.mounted) {
                setSheetState(() {
                  members = (res['members'] as List?) ?? [];
                  loadingMembers = false;
                });
              }
            } catch (_) {
              if (ctx.mounted) setSheetState(() => loadingMembers = false);
            }
          }

          if (loadingMembers) {
            fetchMembers();
          }

          return Padding(
            padding: EdgeInsets.only(
              bottom: MediaQuery.of(ctx).viewInsets.bottom + 20,
              left: 20,
              right: 20,
              top: 16,
            ),
            child: SingleChildScrollView(
              child: Column(
                mainAxisSize: MainAxisSize.min,
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Text('👥 Anggota Dompet: ${w.name}', style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 16)),
                      IconButton(icon: const Icon(Icons.close), onPressed: () => Navigator.pop(ctx)),
                    ],
                  ),
                  const SizedBox(height: 12),
                  const Text('Undang Anggota / Keluarga:', style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: AppColors.primary)),
                  const SizedBox(height: 8),
                  TextField(
                    controller: emailCtrl,
                    keyboardType: TextInputType.emailAddress,
                    decoration: const InputDecoration(labelText: 'Email Anggota (Terdaftar di DuitKu)'),
                  ),
                  const SizedBox(height: 8),
                  Row(
                    children: [
                      Expanded(
                        child: TextField(
                          controller: nameCtrl,
                          decoration: const InputDecoration(labelText: 'Nama / Panggilan'),
                        ),
                      ),
                      const SizedBox(width: 8),
                      Expanded(
                        child: DropdownButtonFormField<String>(
                          initialValue: role,
                          decoration: const InputDecoration(labelText: 'Peran'),
                          items: const [
                            DropdownMenuItem(value: 'editor', child: Text('Editor')),
                            DropdownMenuItem(value: 'viewer', child: Text('Viewer')),
                          ],
                          onChanged: (v) => setSheetState(() => role = v ?? 'editor'),
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 12),
                  FilledButton.icon(
                    onPressed: () async {
                      if (emailCtrl.text.trim().isEmpty) return;
                      try {
                        await ApiService.instance.addWalletMember(
                          walletId: w.id,
                          email: emailCtrl.text.trim(),
                          role: role,
                          name: nameCtrl.text.trim(),
                        );
                        emailCtrl.clear();
                        nameCtrl.clear();
                        loadingMembers = true;
                        fetchMembers();
                      } catch (e) {
                        if (ctx.mounted) {
                          ScaffoldMessenger.of(ctx).showSnackBar(SnackBar(content: Text('Gagal: $e')));
                        }
                      }
                    },
                    icon: const Icon(Icons.person_add_rounded, size: 16),
                    label: const Text('Undang Anggota'),
                    style: FilledButton.styleFrom(minimumSize: const Size.fromHeight(42)),
                  ),
                  const Divider(height: 28),
                  const Text('Daftar Anggota Saat Ini:', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13)),
                  const SizedBox(height: 8),
                  if (loadingMembers)
                    const Center(child: Padding(padding: EdgeInsets.all(12), child: CircularProgressIndicator()))
                  else if (members.isEmpty)
                    const Text('Belum ada anggota yang diundang ke dompet ini.', style: TextStyle(fontSize: 12, color: AppColors.textMuted))
                  else
                    ...members.map((m) => Container(
                          margin: const EdgeInsets.only(bottom: 6),
                          padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                          decoration: BoxDecoration(
                            color: AppColors.borderLight,
                            borderRadius: BorderRadius.circular(10),
                          ),
                          child: Row(
                            mainAxisAlignment: MainAxisAlignment.spaceBetween,
                            children: [
                              Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Text(m['member_name'] ?? m['member_email'] ?? '', style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13)),
                                  Text('${m['member_email']} · ${(m['role'] ?? 'editor').toString().toUpperCase()}', style: const TextStyle(fontSize: 11, color: AppColors.textMuted)),
                                ],
                              ),
                              IconButton(
                                icon: const Icon(Icons.delete_outline_rounded, size: 18, color: Colors.red),
                                onPressed: () async {
                                  await ApiService.instance.removeWalletMember(m['id'] as int);
                                  loadingMembers = true;
                                  fetchMembers();
                                },
                              ),
                            ],
                          ),
                        )),
                ],
              ),
            ),
          );
        },
      ),
    );
  }

  Future<void> _setDefault(Wallet w) async {
    if (w.isDefault) return;
    try {
      await ApiService.instance.setDefaultWallet(w.id);
      _load();
    } on ApiException catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(e.message)));
    }
  }

  Future<void> _deleteWallet(Wallet w) async {
    final ok = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        title: const Text('Hapus rekening?'),
        content: Text('${w.icon} ${w.name} akan dihapus permanen.'),
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
      await ApiService.instance.deleteWallet(w.id);
      _load();
    } on ApiException catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(e.message)));
    }
  }
}

class _WalletListTile extends StatelessWidget {
  final Wallet wallet;
  final String symbol;
  final bool isDefault;
  final VoidCallback onTap;
  final VoidCallback onEdit;
  final VoidCallback onDelete;
  final VoidCallback onMembers;

  const _WalletListTile({
    required this.wallet,
    required this.symbol,
    required this.isDefault,
    required this.onTap,
    required this.onEdit,
    required this.onDelete,
    required this.onMembers,
  });

  @override
  Widget build(BuildContext context) {
    final color = parseColor(wallet.color);
    return Container(
      margin: const EdgeInsets.only(bottom: 10),
      decoration: BoxDecoration(
        color: AppColors.card,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: isDefault ? AppColors.primaryLight : AppColors.border, width: isDefault ? 1.5 : 1),
        boxShadow: AppColors.cardShadow,
      ),
      child: ListTile(
        onTap: onTap,
        leading: Container(
          width: 44,
          height: 44,
          decoration: BoxDecoration(
            color: color.withValues(alpha: .12),
            borderRadius: BorderRadius.circular(12),
          ),
          child: Center(child: Text(wallet.icon, style: const TextStyle(fontSize: 22))),
        ),
        title: Row(
          children: [
            Flexible(child: Text(wallet.name,
                overflow: TextOverflow.ellipsis,
                style: const TextStyle(fontSize: 14, fontWeight: FontWeight.w700, color: AppColors.textPrimary))),
            if (isDefault) ...[
              const SizedBox(width: 6),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                decoration: BoxDecoration(
                  color: AppColors.primaryLight.withValues(alpha: .12),
                  borderRadius: BorderRadius.circular(10),
                ),
                child: const Text('DEFAULT',
                    style: TextStyle(fontSize: 8, fontWeight: FontWeight.w800, color: AppColors.primaryLight)),
              ),
            ],
            if (wallet.isShared) ...[
              const SizedBox(width: 6),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                decoration: BoxDecoration(
                  color: const Color(0xFF0284C7).withValues(alpha: .15),
                  borderRadius: BorderRadius.circular(10),
                ),
                child: Text('👥 BERSAMA (${wallet.role.toUpperCase()})',
                    style: const TextStyle(fontSize: 8, fontWeight: FontWeight.w800, color: Color(0xFF0284C7))),
              ),
            ],
          ],
        ),
        subtitle: Text(
          wallet.isShared && wallet.ownerName != null
              ? 'Milik ${wallet.ownerName} · saldo awal ${Fmt.money0(wallet.initialBalance)}'
              : '${wallet.typeLabel} · saldo awal ${Fmt.money0(wallet.initialBalance)}',
          style: const TextStyle(fontSize: 11, color: AppColors.textMuted),
        ),
        trailing: Row(
          mainAxisSize: MainAxisSize.min,
          children: [
            Text(Fmt.money(wallet.balance, symbol: symbol),
                style: const TextStyle(fontSize: 14, fontWeight: FontWeight.w800, color: AppColors.textPrimary)),
            PopupMenuButton<String>(
              onSelected: (v) {
                if (v == 'members') onMembers();
                if (v == 'edit') onEdit();
                if (v == 'delete') onDelete();
              },
              itemBuilder: (_) => [
                if (!wallet.isShared)
                  const PopupMenuItem(value: 'members', child: Row(children: [Icon(Icons.people_alt_rounded, size: 16), SizedBox(width: 6), Text('Anggota')])),
                if (!wallet.isShared || wallet.role == 'editor')
                  const PopupMenuItem(value: 'edit', child: Row(children: [Icon(Icons.edit_rounded, size: 16), SizedBox(width: 6), Text('Edit')])),
                if (!wallet.isShared)
                  const PopupMenuItem(value: 'delete', child: Row(children: [Icon(Icons.delete_outline_rounded, size: 16, color: Colors.red), SizedBox(width: 6), Text('Hapus')])),
              ],
            ),
          ],
        ),
      ),
    );
  }
}
