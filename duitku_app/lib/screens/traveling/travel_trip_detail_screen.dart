import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../../models/transaction.dart';
import '../../models/travel_item.dart';
import '../../models/travel_ticket.dart';
import '../../models/travel_trip.dart';
import '../../providers/app_data_provider.dart';
import '../../providers/travel_provider.dart';
import '../../theme.dart';
import '../../utils/format.dart';
import '../../utils/travel_icons.dart';
import 'travel_checklist_sheet.dart';
import 'travel_qr_scanner_screen.dart';
import 'travel_ticket_detail_screen.dart';
import 'travel_ticket_sheet.dart';
import 'travel_transaction_sheet.dart';
import 'travel_trip_sheet.dart';

class TravelTripDetailScreen extends StatefulWidget {
  final String tripId;

  const TravelTripDetailScreen({super.key, required this.tripId});

  @override
  State<TravelTripDetailScreen> createState() => _TravelTripDetailScreenState();
}

class _TravelTripDetailScreenState extends State<TravelTripDetailScreen>
    with SingleTickerProviderStateMixin {
  late final TabController _tabCtrl;

  @override
  void initState() {
    super.initState();
    _tabCtrl = TabController(length: 3, vsync: this);
    WidgetsBinding.instance.addPostFrameCallback((_) {
      context.read<TravelProvider>().ensureLoaded(force: true);
      context.read<AppDataProvider>().ensureLoaded();
    });
  }

  @override
  void dispose() {
    _tabCtrl.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Consumer<TravelProvider>(
      builder: (context, travel, _) {
        final trip = travel.tripById(widget.tripId);
        if (trip == null) {
          return const Scaffold(body: Center(child: Text('Trip tidak ditemukan')));
        }
        final cost = travel.totalCostForTrip(trip.id);
        final items = travel.itemsForTrip(trip.id);
        final tickets = travel.ticketsForTrip(trip.id);
        final transactions = travel.transactionsForTrip(trip.id);

        return Scaffold(
          appBar: AppBar(
            title: Text(trip.destination),
            actions: [
              IconButton(
                icon: const Icon(Icons.edit_outlined),
                onPressed: () => _editTrip(trip),
              ),
              IconButton(
                icon: const Icon(Icons.delete_outline),
                onPressed: () => _confirmDelete(trip),
              ),
            ],
            bottom: TabBar(
              controller: _tabCtrl,
              labelColor: AppColors.primary,
              unselectedLabelColor: AppColors.textMuted,
              indicatorColor: AppColors.primary,
              tabs: const [
                Tab(text: 'Checklist', icon: Icon(Icons.inventory_2_outlined)),
                Tab(text: 'Tiket', icon: Icon(Icons.confirmation_num_outlined)),
                Tab(text: 'Biaya', icon: Icon(Icons.account_balance_wallet_outlined)),
              ],
            ),
          ),
          body: TabBarView(
            controller: _tabCtrl,
            children: [
              _ChecklistTab(tripId: trip.id, items: items),
              _TicketsTab(tripId: trip.id, tickets: tickets),
              _CostTab(
                tripId: trip.id,
                transactions: transactions,
                cost: cost,
                budget: trip.budget,
              ),
            ],
          ),
        );
      },
    );
  }

  Future<void> _editTrip(TravelTrip trip) async {
    await showModalBottomSheet<bool>(
      context: context,
      isScrollControlled: true,
      backgroundColor: AppColors.card,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
      ),
      builder: (_) => TravelTripSheet(trip: trip),
    );
  }

  Future<void> _confirmDelete(TravelTrip trip) async {
    final confirmed = await showDialog<bool>(
      context: context,
      builder: (_) => AlertDialog(
        title: const Text('Hapus Trip'),
        content: Text('Hapus trip ke ${trip.destination}? Semua checklist, tiket, dan catatan biayanya ikut terhapus.'),
        actions: [
          TextButton(onPressed: () => Navigator.pop(context, false), child: const Text('Batal')),
          FilledButton(
            onPressed: () => Navigator.pop(context, true),
            style: FilledButton.styleFrom(backgroundColor: AppColors.expense),
            child: const Text('Hapus'),
          ),
        ],
      ),
    );
    if (confirmed == true && mounted) {
      await context.read<TravelProvider>().deleteTrip(trip.id);
      if (mounted) Navigator.pop(context);
    }
  }
}

class _ChecklistTab extends StatelessWidget {
  final String tripId;
  final List<TravelItem> items;

  const _ChecklistTab({required this.tripId, required this.items});

  @override
  Widget build(BuildContext context) {
    final packedCount = items.where((i) => i.isPacked).length;
    final progress = items.isEmpty ? 0.0 : packedCount / items.length;

    return Column(
      children: [
        if (items.isNotEmpty)
          Padding(
            padding: const EdgeInsets.fromLTRB(16, 12, 16, 0),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Text(
                      'Progress barang',
                      style: TextStyle(fontSize: 12, color: AppColors.textSecondary),
                    ),
                    Text(
                      '$packedCount/${items.length}',
                      style: TextStyle(fontSize: 12, fontWeight: FontWeight.w700, color: AppColors.primary),
                    ),
                  ],
                ),
                const SizedBox(height: 6),
                LinearProgressIndicator(
                  value: progress,
                  backgroundColor: AppColors.border,
                  valueColor: AlwaysStoppedAnimation(AppColors.primary),
                  borderRadius: BorderRadius.circular(4),
                ),
              ],
            ),
          ),
        Expanded(
          child: items.isEmpty
              ? _empty('Belum ada barang', 'Tambahkan barang bawaan agar tidak ketinggalan.')
              : ListView.builder(
                  padding: const EdgeInsets.all(16),
                  itemCount: items.length,
                  itemBuilder: (context, index) {
                    final item = items[index];
                    return Container(
                      margin: const EdgeInsets.only(bottom: 8),
                      decoration: BoxDecoration(
                        color: AppColors.card,
                        borderRadius: BorderRadius.circular(12),
                        border: Border.all(color: AppColors.border),
                      ),
                      child: ListTile(
                        leading: Checkbox(
                          value: item.isPacked,
                          activeColor: AppColors.primary,
                          onChanged: (_) => context.read<TravelProvider>().toggleItem(item.id),
                        ),
                        title: Text(
                          item.name,
                          style: TextStyle(
                            decoration: item.isPacked ? TextDecoration.lineThrough : null,
                            color: item.isPacked ? AppColors.textMuted : AppColors.textPrimary,
                          ),
                        ),
                        trailing: Row(
                          mainAxisSize: MainAxisSize.min,
                          children: [
                            IconButton(
                              icon: const Icon(Icons.edit_outlined, size: 18),
                              onPressed: () => _editItem(context, item),
                            ),
                            IconButton(
                              icon: const Icon(Icons.delete_outline, size: 18, color: AppColors.expense),
                              onPressed: () => context.read<TravelProvider>().deleteItem(item.id),
                            ),
                          ],
                        ),
                      ),
                    );
                  },
                ),
        ),
        Padding(
          padding: const EdgeInsets.all(16),
          child: FilledButton.icon(
            onPressed: () => _addItem(context),
            icon: const Icon(Icons.add),
            label: const Text('Tambah Barang'),
          ),
        ),
      ],
    );
  }

  Widget _empty(String title, String subtitle) {
    return Center(
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(Icons.checklist_outlined, size: 56, color: AppColors.textMuted.withValues(alpha: .5)),
          const SizedBox(height: 12),
          Text(title, style: const TextStyle(fontSize: 16, fontWeight: FontWeight.w700)),
          const SizedBox(height: 4),
          Text(subtitle, style: const TextStyle(fontSize: 13, color: AppColors.textSecondary)),
        ],
      ),
    );
  }

  Future<void> _addItem(BuildContext context) async {
    await showModalBottomSheet<bool>(
      context: context,
      isScrollControlled: true,
      backgroundColor: AppColors.card,
      shape: const RoundedRectangleBorder(borderRadius: BorderRadius.vertical(top: Radius.circular(20))),
      builder: (_) => TravelChecklistSheet(tripId: tripId),
    );
  }

  Future<void> _editItem(BuildContext context, TravelItem item) async {
    await showModalBottomSheet<bool>(
      context: context,
      isScrollControlled: true,
      backgroundColor: AppColors.card,
      shape: const RoundedRectangleBorder(borderRadius: BorderRadius.vertical(top: Radius.circular(20))),
      builder: (_) => TravelChecklistSheet(tripId: tripId, item: item),
    );
  }
}

class _TicketsTab extends StatelessWidget {
  final String tripId;
  final List<TravelTicket> tickets;

  const _TicketsTab({required this.tripId, required this.tickets});

  @override
  Widget build(BuildContext context) {
    return Column(
      children: [
        Expanded(
          child: tickets.isEmpty
              ? _empty('Belum ada tiket', 'Simpan tiket pesawat, kereta, atau bus untuk ditunjukkan nanti.')
              : ListView.builder(
                  padding: const EdgeInsets.all(16),
                  itemCount: tickets.length,
                  itemBuilder: (context, index) {
                    final ticket = tickets[index];
                    return GestureDetector(
                      onTap: () => Navigator.push(
                        context,
                        MaterialPageRoute(builder: (_) => TravelTicketDetailScreen(ticket: ticket)),
                      ),
                      child: Container(
                        margin: const EdgeInsets.only(bottom: 10),
                        padding: const EdgeInsets.all(14),
                        decoration: BoxDecoration(
                          color: AppColors.card,
                          borderRadius: BorderRadius.circular(16),
                          border: Border.all(color: AppColors.border),
                          boxShadow: AppColors.cardShadow,
                        ),
                        child: Row(
                          children: [
                            Container(
                              padding: const EdgeInsets.all(10),
                              decoration: BoxDecoration(
                                color: AppColors.primary.withValues(alpha: .1),
                                borderRadius: BorderRadius.circular(12),
                              ),
                              child: Icon(travelTicketIcon(ticket.type), color: AppColors.primary),
                            ),
                            const SizedBox(width: 12),
                            Expanded(
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Text(
                                    ticket.typeLabel,
                                    style: const TextStyle(fontSize: 12, color: AppColors.textSecondary),
                                  ),
                                  Text(
                                    ticket.code ?? 'Tiket',
                                    style: const TextStyle(fontSize: 15, fontWeight: FontWeight.w700),
                                  ),
                                  if (ticket.departure != null && ticket.arrival != null)
                                    Text(
                                      '${ticket.departure} → ${ticket.arrival}',
                                      style: const TextStyle(fontSize: 12, color: AppColors.textSecondary),
                                    ),
                                ],
                              ),
                            ),
                            const Icon(Icons.chevron_right, color: AppColors.textMuted),
                          ],
                        ),
                      ),
                    );
                  },
                ),
        ),
        Padding(
          padding: const EdgeInsets.all(16),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              FilledButton.icon(
                onPressed: () => _scanTicket(context),
                icon: const Icon(Icons.qr_code_scanner),
                label: const Text('Scan Tiket'),
              ),
              const SizedBox(height: 8),
              OutlinedButton.icon(
                onPressed: () => _addTicket(context),
                icon: const Icon(Icons.edit_note),
                label: const Text('Tambah Manual'),
              ),
            ],
          ),
        ),
      ],
    );
  }

  Widget _empty(String title, String subtitle) {
    return Center(
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(Icons.confirmation_num_outlined, size: 56, color: AppColors.textMuted.withValues(alpha: .5)),
          const SizedBox(height: 12),
          Text(title, style: const TextStyle(fontSize: 16, fontWeight: FontWeight.w700)),
          const SizedBox(height: 4),
          Text(subtitle, style: const TextStyle(fontSize: 13, color: AppColors.textSecondary)),
        ],
      ),
    );
  }

  Future<void> _scanTicket(BuildContext context) async {
    final qrData = await Navigator.push<String>(
      context,
      MaterialPageRoute(builder: (_) => const TravelQrScannerScreen()),
    );
    if (qrData != null && context.mounted) {
      await showModalBottomSheet<bool>(
        context: context,
        isScrollControlled: true,
        backgroundColor: AppColors.card,
        shape: const RoundedRectangleBorder(borderRadius: BorderRadius.vertical(top: Radius.circular(20))),
        builder: (_) => TravelTicketSheet(tripId: tripId, initialQrData: qrData),
      );
    }
  }

  Future<void> _addTicket(BuildContext context) async {
    await showModalBottomSheet<bool>(
      context: context,
      isScrollControlled: true,
      backgroundColor: AppColors.card,
      shape: const RoundedRectangleBorder(borderRadius: BorderRadius.vertical(top: Radius.circular(20))),
      builder: (_) => TravelTicketSheet(tripId: tripId),
    );
  }
}

class _CostTab extends StatelessWidget {
  final String tripId;
  final List<Transaction> transactions;
  final double cost;
  final double budget;

  const _CostTab({
    required this.tripId,
    required this.transactions,
    required this.cost,
    required this.budget,
  });

  @override
  Widget build(BuildContext context) {
    final expense = transactions.where((t) => t.type == 'expense').fold(0.0, (s, t) => s + t.amount);
    final income = transactions.where((t) => t.type == 'income').fold(0.0, (s, t) => s + t.amount);

    return Column(
      children: [
        Container(
          margin: const EdgeInsets.all(16),
          padding: const EdgeInsets.all(16),
          decoration: BoxDecoration(
            color: AppColors.card,
            borderRadius: BorderRadius.circular(16),
            border: Border.all(color: AppColors.border),
            boxShadow: AppColors.cardShadow,
          ),
          child: Column(
            children: [
              const Text('TOTAL PENGELUARAN', style: TextStyle(fontSize: 12, color: AppColors.textMuted)),
              const SizedBox(height: 6),
              Text(
                Fmt.money(cost),
                style: const TextStyle(fontSize: 28, fontWeight: FontWeight.w800, color: AppColors.expense),
              ),
              if (budget > 0) ...[
                const SizedBox(height: 12),
                LinearProgressIndicator(
                  value: (expense / budget).clamp(0, 1).toDouble(),
                  backgroundColor: AppColors.border,
                  valueColor: AlwaysStoppedAnimation(expense > budget ? AppColors.expense : AppColors.primary),
                  borderRadius: BorderRadius.circular(4),
                ),
                const SizedBox(height: 6),
                Text(
                  'Anggaran ${Fmt.money(budget)}',
                  style: const TextStyle(fontSize: 12, color: AppColors.textSecondary),
                ),
              ],
              const SizedBox(height: 12),
              Row(
                children: [
                  Expanded(
                    child: _moneyCard('Pengeluaran', expense, AppColors.expense),
                  ),
                  const SizedBox(width: 10),
                  Expanded(
                    child: _moneyCard('Pemasukan', income, AppColors.income),
                  ),
                ],
              ),
            ],
          ),
        ),
        Expanded(
          child: transactions.isEmpty
              ? _empty('Belum ada transaksi', 'Catat pengeluaran atau pemasukan selama traveling.')
              : ListView.builder(
                  padding: const EdgeInsets.symmetric(horizontal: 16),
                  itemCount: transactions.length,
                  itemBuilder: (context, index) {
                    final t = transactions[index];
                    final isExpense = t.type == 'expense';
                    return Container(
                      margin: const EdgeInsets.only(bottom: 8),
                      decoration: BoxDecoration(
                        color: AppColors.card,
                        borderRadius: BorderRadius.circular(12),
                        border: Border.all(color: AppColors.border),
                      ),
                      child: ListTile(
                        leading: CircleAvatar(
                          backgroundColor: (isExpense ? AppColors.expense : AppColors.income).withValues(alpha: .1),
                          child: Icon(
                            isExpense ? Icons.arrow_downward : Icons.arrow_upward,
                            color: isExpense ? AppColors.expense : AppColors.income,
                          ),
                        ),
                        title: Text(
                          t.note?.replaceFirst('[Trip:$tripId]', '').trim() ?? 'Tanpa catatan',
                          style: const TextStyle(fontWeight: FontWeight.w600),
                        ),
                        subtitle: Text(
                          '${t.categoryName ?? 'Umum'} • ${Fmt.dateDay(t.date)}',
                          style: const TextStyle(fontSize: 12, color: AppColors.textSecondary),
                        ),
                        trailing: Text(
                          Fmt.money(t.amount),
                          style: TextStyle(
                            fontWeight: FontWeight.w700,
                            color: isExpense ? AppColors.expense : AppColors.income,
                          ),
                        ),
                      ),
                    );
                  },
                ),
        ),
        Padding(
          padding: const EdgeInsets.all(16),
          child: FilledButton.icon(
            onPressed: () => _addTransaction(context),
            icon: const Icon(Icons.add),
            label: const Text('Catat Transaksi'),
          ),
        ),
      ],
    );
  }

  Widget _moneyCard(String label, double amount, Color color) {
    return Container(
      padding: const EdgeInsets.symmetric(vertical: 10),
      decoration: BoxDecoration(
        color: color.withValues(alpha: .08),
        borderRadius: BorderRadius.circular(10),
      ),
      child: Column(
        children: [
          Text(label, style: TextStyle(fontSize: 11, color: color)),
          const SizedBox(height: 2),
          Text(
            Fmt.money(amount),
            style: TextStyle(fontSize: 14, fontWeight: FontWeight.w800, color: color),
          ),
        ],
      ),
    );
  }

  Widget _empty(String title, String subtitle) {
    return Center(
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(Icons.receipt_long_outlined, size: 56, color: AppColors.textMuted.withValues(alpha: .5)),
          const SizedBox(height: 12),
          Text(title, style: const TextStyle(fontSize: 16, fontWeight: FontWeight.w700)),
          const SizedBox(height: 4),
          Text(subtitle, style: const TextStyle(fontSize: 13, color: AppColors.textSecondary)),
        ],
      ),
    );
  }

  Future<void> _addTransaction(BuildContext context) async {
    final data = context.read<AppDataProvider>();
    await data.ensureLoaded();
    if (!context.mounted) return;
    await showModalBottomSheet<bool>(
      context: context,
      isScrollControlled: true,
      backgroundColor: AppColors.card,
      shape: const RoundedRectangleBorder(borderRadius: BorderRadius.vertical(top: Radius.circular(20))),
      builder: (_) => TravelTransactionSheet(
        tripId: tripId,
        categories: data.categories,
        wallets: data.wallets,
      ),
    );
  }
}
