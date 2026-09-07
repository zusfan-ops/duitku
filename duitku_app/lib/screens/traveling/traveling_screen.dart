import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../../providers/travel_provider.dart';
import '../../theme.dart';
import '../../utils/format.dart';
import 'currency_converter_sheet.dart';
import 'travel_trip_detail_screen.dart';
import 'travel_trip_sheet.dart';

class TravelingScreen extends StatefulWidget {
  const TravelingScreen({super.key});

  @override
  State<TravelingScreen> createState() => _TravelingScreenState();
}

class _TravelingScreenState extends State<TravelingScreen> {
  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      context.read<TravelProvider>().ensureLoaded(force: true);
    });
  }

  void _openCurrencyConverter() {
    showModalBottomSheet(
      context: context,
      useRootNavigator: true,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (_) => const CurrencyConverterSheet(),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Traveling & Trip'),
        actions: [
          _buildAddTripButton(),
          IconButton(
            tooltip: 'Kalkulator Kurs Valas',
            icon: const Icon(Icons.currency_exchange_rounded),
            onPressed: _openCurrencyConverter,
          ),
        ],
      ),
      body: Consumer<TravelProvider>(
        builder: (context, travel, _) {
          if (!travel.loaded) {
            return const Center(child: CircularProgressIndicator());
          }
          final trips = travel.trips;
          if (trips.isEmpty) {
            return RefreshIndicator(
              onRefresh: () => context.read<TravelProvider>().refresh(),
              child: ListView(
                physics: const AlwaysScrollableScrollPhysics(),
                children: [
                  SizedBox(
                    height: MediaQuery.of(context).size.height * 0.7,
                    child: _emptyState(),
                  ),
                ],
              ),
            );
          }
          return RefreshIndicator(
            onRefresh: () => context.read<TravelProvider>().refresh(),
            child: ListView.builder(
              physics: const AlwaysScrollableScrollPhysics(),
              padding: const EdgeInsets.all(16),
              itemCount: trips.length,
              itemBuilder: (context, index) {
              final trip = trips[index];
              final cost = travel.totalCostForTrip(trip.id);
              return GestureDetector(
                onTap: () => _openTripDetail(trip.id),
                child: Container(
                  margin: const EdgeInsets.only(bottom: 12),
                  padding: const EdgeInsets.all(16),
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
                          Container(
                            padding: const EdgeInsets.all(10),
                            decoration: BoxDecoration(
                              color: AppColors.primary.withValues(alpha: .1),
                              borderRadius: BorderRadius.circular(12),
                            ),
                            child: const Icon(Icons.flight_rounded, color: AppColors.primary),
                          ),
                          const SizedBox(width: 12),
                          Expanded(
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Text(
                                  trip.destination,
                                  style: const TextStyle(
                                    fontSize: 16,
                                    fontWeight: FontWeight.w700,
                                    color: AppColors.textPrimary,
                                  ),
                                ),
                                const SizedBox(height: 2),
                                Text(
                                  _dateRange(trip.startDate, trip.endDate),
                                  style: const TextStyle(fontSize: 12, color: AppColors.textSecondary),
                                ),
                              ],
                            ),
                          ),
                          const Icon(Icons.chevron_right, color: AppColors.textMuted),
                        ],
                      ),
                      const SizedBox(height: 12),
                      const Divider(height: 1, color: AppColors.border),
                      const SizedBox(height: 12),
                      Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          _meta(
                            icon: Icons.inventory_2_outlined,
                            label: '${travel.itemsForTrip(trip.id).where((i) => i.isPacked).length}/${travel.itemsForTrip(trip.id).length} barang',
                          ),
                          _meta(
                            icon: Icons.confirmation_num_outlined,
                            label: '${travel.ticketsForTrip(trip.id).length} tiket',
                          ),
                        ],
                      ),
                      const SizedBox(height: 10),
                      Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          const Text(
                            'Total pengeluaran',
                            style: TextStyle(fontSize: 12, color: AppColors.textSecondary),
                          ),
                          Text(
                            Fmt.money(cost),
                            style: const TextStyle(
                              fontSize: 14,
                              fontWeight: FontWeight.w800,
                              color: AppColors.expense,
                            ),
                          ),
                        ],
                      ),
                      if (trip.budget > 0)
                        Padding(
                          padding: const EdgeInsets.only(top: 8),
                          child: LinearProgressIndicator(
                            value: (cost / trip.budget).clamp(0, 1).toDouble(),
                            backgroundColor: AppColors.border,
                            valueColor: AlwaysStoppedAnimation(
                              cost > trip.budget ? AppColors.expense : AppColors.primary,
                            ),
                            borderRadius: BorderRadius.circular(4),
                          ),
                        ),
                    ],
                  ),
                ),
              );
            },
          ),
        );
      },
    ),
    );
  }

  Widget _emptyState() {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(24),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            const Text('✈️', style: TextStyle(fontSize: 56)),
            const SizedBox(height: 12),
            const Text(
              'Belum Ada Perjalanan',
              style: TextStyle(fontSize: 17, fontWeight: FontWeight.w800, color: AppColors.textPrimary),
            ),
            const SizedBox(height: 6),
            const Text(
              'Buat rencana trip liburan atau dinas Anda, simpan tiket digital, checklist barang, dan pantau pengeluarannya.',
              textAlign: TextAlign.center,
              style: TextStyle(fontSize: 13, color: AppColors.textSecondary),
            ),
            const SizedBox(height: 18),
            FilledButton.icon(
              onPressed: _addTrip,
              style: FilledButton.styleFrom(
                backgroundColor: const Color(0xFF0891B2),
                foregroundColor: Colors.white,
                padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                textStyle: const TextStyle(fontSize: 13, fontWeight: FontWeight.w700),
              ),
              icon: const Icon(Icons.add, size: 18),
              label: const Text('Tambah Trip Pertama'),
            ),
          ],
        ),
      ),
    );
  }

  Widget _meta({required IconData icon, required String label}) {
    return Row(
      mainAxisSize: MainAxisSize.min,
      children: [
        Icon(icon, size: 14, color: AppColors.textMuted),
        const SizedBox(width: 4),
        Text(label, style: const TextStyle(fontSize: 12, color: AppColors.textSecondary)),
      ],
    );
  }

  Widget _buildAddTripButton() {
    return Padding(
      padding: const EdgeInsets.only(right: 8),
      child: FilledButton.icon(
        onPressed: _addTrip,
        style: FilledButton.styleFrom(
          backgroundColor: const Color(0xFF0891B2),
          foregroundColor: Colors.white,
          padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
          minimumSize: Size.zero,
          tapTargetSize: MaterialTapTargetSize.shrinkWrap,
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
          textStyle: const TextStyle(fontSize: 12.5, fontWeight: FontWeight.w700),
        ),
        icon: const Icon(Icons.add, size: 16),
        label: const Text('Buat Trip'),
      ),
    );
  }

  String _dateRange(String start, String? end) {
    if (end == null || end.isEmpty) return Fmt.dateDay(start);
    return '${Fmt.dateDay(start)} – ${Fmt.dateDay(end)}';
  }

  Future<void> _addTrip() async {
    final created = await showModalBottomSheet<bool>(
      context: context,
      useRootNavigator: true,
      isScrollControlled: true,
      backgroundColor: AppColors.card,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
      ),
      builder: (_) => const TravelTripSheet(),
    );
    if (created == true && mounted) {
      setState(() {});
    }
  }

  void _openTripDetail(String tripId) {
    Navigator.of(context, rootNavigator: true).push(
      MaterialPageRoute(builder: (_) => TravelTripDetailScreen(tripId: tripId)),
    );
  }
}
