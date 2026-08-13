import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../../models/travel_item.dart';
import '../../providers/travel_provider.dart';
import '../../theme.dart';

class TravelChecklistSheet extends StatefulWidget {
  final String tripId;
  final TravelItem? item;

  const TravelChecklistSheet({super.key, required this.tripId, this.item});

  @override
  State<TravelChecklistSheet> createState() => _TravelChecklistSheetState();
}

class _TravelChecklistSheetState extends State<TravelChecklistSheet> {
  final _nameCtrl = TextEditingController();
  bool _saving = false;

  @override
  void initState() {
    super.initState();
    if (widget.item != null) {
      _nameCtrl.text = widget.item!.name;
    }
  }

  @override
  void dispose() {
    _nameCtrl.dispose();
    super.dispose();
  }

  Future<void> _save() async {
    final name = _nameCtrl.text.trim();
    if (name.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Masukkan nama barang')));
      return;
    }
    setState(() => _saving = true);
    final travel = context.read<TravelProvider>();
    try {
      if (widget.item == null) {
        await travel.addItem(TravelItem(
          id: generateId(),
          tripId: widget.tripId,
          name: name,
        ));
      } else {
        await travel.updateItem(widget.item!.copyWith(name: name));
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
              widget.item == null ? 'Tambah Barang' : 'Edit Barang',
              style: const TextStyle(fontSize: 18, fontWeight: FontWeight.w800, color: AppColors.textPrimary),
            ),
            const SizedBox(height: 16),
            TextField(
              controller: _nameCtrl,
              decoration: const InputDecoration(labelText: 'NAMA BARANG', hintText: 'Contoh: Kacamata hitam'),
              autofocus: true,
            ),
            const SizedBox(height: 20),
            FilledButton(
              onPressed: _saving ? null : _save,
              child: _saving
                  ? const SizedBox(width: 22, height: 22, child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white))
                  : Text(widget.item == null ? 'Tambah' : 'Simpan'),
            ),
          ],
        ),
      ),
    );
  }
}
