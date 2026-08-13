import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../../providers/travel_provider.dart';
import '../../theme.dart';
import '../../utils/format.dart';
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
      context.read<TravelProvider>().ensureLoaded();
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Traveling'),
      ),
      body: Consumer<TravelProvider>(
        builder: (context, travel, _) {
          if (!travel.loaded) {
            return const Center(child: CircularProgressIndicator());
          }
          final trips = travel.trips;
          if (trips.isEmpty) {
            return _emptyState();
          }
          return ListView.builder(
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
          );
        },
      ),
      floatingActionButton: FloatingActionButton.extended(
        onPressed: _addTrip,
        backgroundColor: AppColors.primary,
        icon: const Icon(Icons.add, color: Colors.white),
        label: const Text('Trip Baru', style: TextStyle(color: Colors.white, fontWeight: FontWeight.w700)),
      ),
    );
  }

  Widget _emptyState() {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(32),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Icon(Icons.travel_explore_outlined, size: 72, color: AppColors.textMuted.withValues(alpha: .5)),
            const SizedBox(height: 16),
            const Text(
              'Belum ada perjalanan',
              style: TextStyle(fontSize: 18, fontWeight: FontWeight.w700, color: AppColors.textPrimary),
            ),
            const SizedBox(height: 6),
            const Text(
              'Catat barang bawaan, tiket, dan pengeluaran traveling Anda di sini.',
              textAlign: TextAlign.center,
              style: TextStyle(fontSize: 13, color: AppColors.textSecondary),
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

  String _dateRange(String start, String? end) {
    if (end == null || end.isEmpty) return Fmt.dateDay(start);
    return '${Fmt.dateDay(start)} – ${Fmt.dateDay(end)}';
  }

  Future<void> _addTrip() async {
    final created = await showModalBottomSheet<bool>(
      context: context,
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
    Navigator.push(
      context,
      MaterialPageRoute(builder: (_) => TravelTripDetailScreen(tripId: tripId)),
    );
  }
}
