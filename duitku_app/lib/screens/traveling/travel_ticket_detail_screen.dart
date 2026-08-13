import 'package:flutter/material.dart';
import 'package:qr_flutter/qr_flutter.dart';

import '../../models/travel_ticket.dart';
import '../../theme.dart';
import '../../utils/travel_icons.dart';

class TravelTicketDetailScreen extends StatelessWidget {
  final TravelTicket ticket;

  const TravelTicketDetailScreen({super.key, required this.ticket});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text(ticket.typeLabel),
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(20),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            Container(
              padding: const EdgeInsets.all(20),
              decoration: BoxDecoration(
                color: AppColors.card,
                borderRadius: BorderRadius.circular(20),
                border: Border.all(color: AppColors.border),
                boxShadow: AppColors.cardShadow,
              ),
              child: Column(
                children: [
                  Icon(travelTicketIcon(ticket.type), size: 40, color: AppColors.primary),
                  const SizedBox(height: 12),
                  Text(
                    ticket.typeLabel,
                    style: const TextStyle(fontSize: 14, color: AppColors.textSecondary),
                  ),
                  if (ticket.code != null) ...[
                    const SizedBox(height: 4),
                    Text(
                      ticket.code!,
                      style: const TextStyle(fontSize: 24, fontWeight: FontWeight.w800, letterSpacing: 1.5),
                    ),
                  ],
                ],
              ),
            ),
            const SizedBox(height: 16),
            Container(
              padding: const EdgeInsets.all(20),
              decoration: BoxDecoration(
                color: AppColors.card,
                borderRadius: BorderRadius.circular(20),
                border: Border.all(color: AppColors.border),
              ),
              child: Column(
                children: [
                  _row('Penumpang', ticket.passengerName),
                  _row('Berangkat', ticket.departure),
                  _row('Tujuan', ticket.arrival),
                  _row('Waktu', ticket.departureTime),
                  _row('Kursi', ticket.seat),
                  _row('Catatan', ticket.notes),
                ],
              ),
            ),
            if (ticket.qrData != null && ticket.qrData!.isNotEmpty) ...[
              const SizedBox(height: 20),
              Container(
                padding: const EdgeInsets.all(20),
                decoration: BoxDecoration(
                  color: AppColors.card,
                  borderRadius: BorderRadius.circular(20),
                  border: Border.all(color: AppColors.border),
                ),
                child: Column(
                  children: [
                    const Text(
                      'Tunjukkan QR ini di loket',
                      style: TextStyle(fontSize: 14, fontWeight: FontWeight.w700, color: AppColors.textSecondary),
                    ),
                    const SizedBox(height: 16),
                    QrImageView(
                      data: ticket.qrData!,
                      version: QrVersions.auto,
                      size: 240,
                      backgroundColor: Colors.white,
                    ),
                    const SizedBox(height: 12),
                    Text(
                      ticket.qrData!,
                      textAlign: TextAlign.center,
                      style: const TextStyle(fontSize: 11, color: AppColors.textMuted),
                    ),
                  ],
                ),
              ),
            ],
          ],
        ),
      ),
    );
  }

  Widget _row(String label, String? value) {
    if (value == null || value.isEmpty) return const SizedBox.shrink();
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 8),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Text(label, style: const TextStyle(fontSize: 13, color: AppColors.textSecondary)),
          Text(value, style: const TextStyle(fontSize: 14, fontWeight: FontWeight.w600)),
        ],
      ),
    );
  }
}
