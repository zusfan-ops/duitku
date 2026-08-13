import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../../models/travel_ticket.dart';
import '../../providers/travel_provider.dart';
import '../../theme.dart';

class TravelTicketSheet extends StatefulWidget {
  final String tripId;
  final TravelTicket? ticket;
  final String? initialQrData;

  const TravelTicketSheet({super.key, required this.tripId, this.ticket, this.initialQrData});

  @override
  State<TravelTicketSheet> createState() => _TravelTicketSheetState();
}

class _TravelTicketSheetState extends State<TravelTicketSheet> {
  final _codeCtrl = TextEditingController();
  final _passengerCtrl = TextEditingController();
  final _departureCtrl = TextEditingController();
  final _arrivalCtrl = TextEditingController();
  final _timeCtrl = TextEditingController();
  final _seatCtrl = TextEditingController();
  final _notesCtrl = TextEditingController();
  String _type = 'flight';
  String? _qrData;
  bool _saving = false;

  final List<Map<String, dynamic>> _types = [
    {'value': 'flight', 'label': 'Pesawat', 'icon': Icons.flight_takeoff_rounded},
    {'value': 'train', 'label': 'Kereta', 'icon': Icons.train_rounded},
    {'value': 'bus', 'label': 'Bus', 'icon': Icons.directions_bus_rounded},
    {'value': 'ship', 'label': 'Kapal', 'icon': Icons.directions_boat_rounded},
    {'value': 'other', 'label': 'Lainnya', 'icon': Icons.confirmation_num_rounded},
  ];

  @override
  void initState() {
    super.initState();
    _qrData = widget.initialQrData;
    final t = widget.ticket;
    if (t != null) {
      _type = t.type;
      _codeCtrl.text = t.code ?? '';
      _qrData = t.qrData;
      _passengerCtrl.text = t.passengerName ?? '';
      _departureCtrl.text = t.departure ?? '';
      _arrivalCtrl.text = t.arrival ?? '';
      _timeCtrl.text = t.departureTime ?? '';
      _seatCtrl.text = t.seat ?? '';
      _notesCtrl.text = t.notes ?? '';
    }
  }

  @override
  void dispose() {
    _codeCtrl.dispose();
    _passengerCtrl.dispose();
    _departureCtrl.dispose();
    _arrivalCtrl.dispose();
    _timeCtrl.dispose();
    _seatCtrl.dispose();
    _notesCtrl.dispose();
    super.dispose();
  }

  Future<void> _save() async {
    setState(() => _saving = true);
    final travel = context.read<TravelProvider>();
    try {
      final ticket = TravelTicket(
        id: widget.ticket?.id ?? generateId(),
        tripId: widget.tripId,
        type: _type,
        code: _codeCtrl.text.trim().isEmpty ? null : _codeCtrl.text.trim(),
        qrData: _qrData,
        passengerName: _passengerCtrl.text.trim().isEmpty ? null : _passengerCtrl.text.trim(),
        departure: _departureCtrl.text.trim().isEmpty ? null : _departureCtrl.text.trim(),
        arrival: _arrivalCtrl.text.trim().isEmpty ? null : _arrivalCtrl.text.trim(),
        departureTime: _timeCtrl.text.trim().isEmpty ? null : _timeCtrl.text.trim(),
        seat: _seatCtrl.text.trim().isEmpty ? null : _seatCtrl.text.trim(),
        notes: _notesCtrl.text.trim().isEmpty ? null : _notesCtrl.text.trim(),
      );
      if (widget.ticket == null) {
        await travel.addTicket(ticket);
      } else {
        await travel.updateTicket(ticket);
      }
      if (!mounted) return;
      Navigator.pop(context, true);
    } catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Gagal menyimpan: $e')));
    } finally {
      if (mounted) setState(() => _saving = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: EdgeInsets.only(bottom: MediaQuery.of(context).viewInsets.bottom),
      child: SingleChildScrollView(
        padding: const EdgeInsets.fromLTRB(20, 16, 20, 28),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            Container(
              width: 40,
              height: 4,
              margin: const EdgeInsets.only(bottom: 14),
              decoration: BoxDecoration(color: AppColors.border, borderRadius: BorderRadius.circular(2)),
            ),
            Text(
              widget.ticket == null ? 'Tambah Tiket' : 'Edit Tiket',
              style: const TextStyle(fontSize: 18, fontWeight: FontWeight.w800, color: AppColors.textPrimary),
            ),
            const SizedBox(height: 16),
            SizedBox(
              height: 40,
              child: ListView.separated(
                scrollDirection: Axis.horizontal,
                itemCount: _types.length,
                separatorBuilder: (_, __) => const SizedBox(width: 8),
                itemBuilder: (context, index) {
                  final t = _types[index];
                  final selected = _type == t['value'];
                  return ChoiceChip(
                    label: Row(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        Icon(t['icon'] as IconData, size: 16, color: selected ? Colors.white : AppColors.textSecondary),
                        const SizedBox(width: 4),
                        Text(t['label'] as String),
                      ],
                    ),
                    selected: selected,
                    onSelected: (_) => setState(() => _type = t['value'] as String),
                    selectedColor: AppColors.primary,
                    labelStyle: TextStyle(
                      color: selected ? Colors.white : AppColors.textPrimary,
                      fontWeight: FontWeight.w600,
                    ),
                    backgroundColor: AppColors.bg,
                    side: BorderSide(color: selected ? AppColors.primary : AppColors.border),
                    showCheckmark: false,
                  );
                },
              ),
            ),
            const SizedBox(height: 16),
            TextField(
              controller: _codeCtrl,
              decoration: const InputDecoration(labelText: 'KODE TIKET / BOOKING', hintText: 'Contoh: ABC123'),
            ),
            if (_qrData != null) ...[
              const SizedBox(height: 12),
              Container(
                padding: const EdgeInsets.all(10),
                decoration: BoxDecoration(
                  color: AppColors.bg,
                  borderRadius: BorderRadius.circular(10),
                ),
                child: Row(
                  children: [
                    const Icon(Icons.qr_code, color: AppColors.primary),
                    const SizedBox(width: 8),
                    Expanded(
                      child: Text(
                        'Data QR tersimpan',
                        style: const TextStyle(fontSize: 13, color: AppColors.textSecondary),
                      ),
                    ),
                    TextButton(
                      onPressed: () => setState(() => _qrData = null),
                      child: const Text('Hapus', style: TextStyle(color: AppColors.expense)),
                    ),
                  ],
                ),
              ),
            ],
            const SizedBox(height: 14),
            TextField(
              controller: _passengerCtrl,
              decoration: const InputDecoration(labelText: 'NAMA PENUMPANG'),
            ),
            const SizedBox(height: 14),
            Row(
              children: [
                Expanded(
                  child: TextField(
                    controller: _departureCtrl,
                    decoration: const InputDecoration(labelText: 'BERANGKAT'),
                  ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: TextField(
                    controller: _arrivalCtrl,
                    decoration: const InputDecoration(labelText: 'TUJUAN'),
                  ),
                ),
              ],
            ),
            const SizedBox(height: 14),
            Row(
              children: [
                Expanded(
                  child: TextField(
                    controller: _timeCtrl,
                    decoration: const InputDecoration(labelText: 'WAKTU'),
                  ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: TextField(
                    controller: _seatCtrl,
                    decoration: const InputDecoration(labelText: 'KURSI'),
                  ),
                ),
              ],
            ),
            const SizedBox(height: 14),
            TextField(
              controller: _notesCtrl,
              decoration: const InputDecoration(labelText: 'CATATAN'),
              maxLines: 2,
            ),
            const SizedBox(height: 20),
            FilledButton(
              onPressed: _saving ? null : _save,
              child: _saving
                  ? const SizedBox(width: 22, height: 22, child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white))
                  : Text(widget.ticket == null ? 'Simpan Tiket' : 'Simpan Perubahan'),
            ),
          ],
        ),
      ),
    );
  }
}
