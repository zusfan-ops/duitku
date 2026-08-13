import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../../models/travel_trip.dart';
import '../../providers/travel_provider.dart';
import '../../theme.dart';
import '../../utils/format.dart';

class TravelTripSheet extends StatefulWidget {
  final TravelTrip? trip;

  const TravelTripSheet({super.key, this.trip});

  @override
  State<TravelTripSheet> createState() => _TravelTripSheetState();
}

class _TravelTripSheetState extends State<TravelTripSheet> {
  final _destinationCtrl = TextEditingController();
  final _descCtrl = TextEditingController();
  final _budgetCtrl = TextEditingController();
  String _startDate = '';
  String? _endDate;
  bool _saving = false;

  @override
  void initState() {
    super.initState();
    final t = widget.trip;
    if (t != null) {
      _destinationCtrl.text = t.destination;
      _descCtrl.text = t.description ?? '';
      _budgetCtrl.text = t.budget > 0 ? Fmt.money0(t.budget) : '';
      _startDate = t.startDate;
      _endDate = t.endDate;
    } else {
      _startDate = DateTime.now().toIso8601String().substring(0, 10);
    }
  }

  @override
  void dispose() {
    _destinationCtrl.dispose();
    _descCtrl.dispose();
    _budgetCtrl.dispose();
    super.dispose();
  }

  Future<void> _pickDate({required bool isStart}) async {
    final now = DateTime.now();
    final initial = DateTime.tryParse(isStart ? _startDate : (_endDate ?? _startDate)) ?? now;
    final picked = await showDatePicker(
      context: context,
      initialDate: initial,
      firstDate: DateTime(now.year - 1),
      lastDate: DateTime(now.year + 5),
    );
    if (picked != null) {
      final formatted =
          '${picked.year.toString().padLeft(4, '0')}-${picked.month.toString().padLeft(2, '0')}-${picked.day.toString().padLeft(2, '0')}';
      setState(() {
        if (isStart) {
          _startDate = formatted;
        } else {
          _endDate = formatted;
        }
      });
    }
  }

  Future<void> _save() async {
    final destination = _destinationCtrl.text.trim();
    if (destination.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Masukkan destinasi')));
      return;
    }
    if (_startDate.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Pilih tanggal berangkat')));
      return;
    }

    setState(() => _saving = true);
    final budget = double.tryParse(Fmt.parseAmount(_budgetCtrl.text)) ?? 0;
    final travel = context.read<TravelProvider>();

    try {
      if (widget.trip == null) {
        await travel.addTrip(TravelTrip(
          id: generateId(),
          destination: destination,
          description: _descCtrl.text.trim(),
          startDate: _startDate,
          endDate: _endDate,
          budget: budget,
        ));
      } else {
        await travel.updateTrip(widget.trip!.copyWith(
          destination: destination,
          description: _descCtrl.text.trim(),
          startDate: _startDate,
          endDate: _endDate,
          budget: budget,
        ));
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
              widget.trip == null ? 'Trip Baru' : 'Edit Trip',
              style: const TextStyle(fontSize: 18, fontWeight: FontWeight.w800, color: AppColors.textPrimary),
            ),
            const SizedBox(height: 16),
            TextField(
              controller: _destinationCtrl,
              decoration: const InputDecoration(labelText: 'DESTINASI', hintText: 'Contoh: Bali, Yogyakarta'),
            ),
            const SizedBox(height: 14),
            TextField(
              controller: _descCtrl,
              decoration: const InputDecoration(labelText: 'DESKRIPSI (OPSIONAL)', hintText: 'Tujuan trip...'),
            ),
            const SizedBox(height: 14),
            InkWell(
              onTap: () => _pickDate(isStart: true),
              borderRadius: BorderRadius.circular(12),
              child: InputDecorator(
                decoration: const InputDecoration(labelText: 'TANGGAL BERANGKAT'),
                child: Text(
                  _startDate.isEmpty ? 'Pilih tanggal' : Fmt.dateDay(_startDate),
                  style: const TextStyle(fontWeight: FontWeight.w600),
                ),
              ),
            ),
            const SizedBox(height: 14),
            InkWell(
              onTap: () => _pickDate(isStart: false),
              borderRadius: BorderRadius.circular(12),
              child: InputDecorator(
                decoration: const InputDecoration(labelText: 'TANGGAL PULANG (OPSIONAL)'),
                child: Text(
                  _endDate == null || _endDate!.isEmpty ? 'Pilih tanggal' : Fmt.dateDay(_endDate!),
                  style: const TextStyle(fontWeight: FontWeight.w600),
                ),
              ),
            ),
            const SizedBox(height: 14),
            TextField(
              controller: _budgetCtrl,
              keyboardType: TextInputType.number,
              decoration: const InputDecoration(labelText: 'ANGGARAN (OPSIONAL)', prefixText: 'Rp '),
            ),
            const SizedBox(height: 20),
            FilledButton(
              onPressed: _saving ? null : _save,
              child: _saving
                  ? const SizedBox(width: 22, height: 22, child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white))
                  : Text(widget.trip == null ? 'Simpan Trip' : 'Simpan Perubahan'),
            ),
          ],
        ),
      ),
    );
  }
}
