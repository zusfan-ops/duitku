import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import '../../services/api_service.dart';
import '../../theme.dart';

class BackupRestoreScreen extends StatefulWidget {
  const BackupRestoreScreen({super.key});

  @override
  State<BackupRestoreScreen> createState() => _BackupRestoreScreenState();
}

class _BackupRestoreScreenState extends State<BackupRestoreScreen> {
  bool _loading = false;
  String? _exportedJson;
  final _restoreCtrl = TextEditingController();

  @override
  void dispose() {
    _restoreCtrl.dispose();
    super.dispose();
  }

  Future<void> _export() async {
    setState(() => _loading = true);
    try {
      final res = await ApiService.instance.exportBackup();
      final backup = res['backup'] as Map<String, dynamic>? ?? {};
      final jsonPretty = const JsonEncoder.withIndent('  ').convert(backup);

      setState(() {
        _exportedJson = jsonPretty;
        _loading = false;
      });

      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Cadangan data berhasil dibuat!')),
        );
      }
    } catch (e) {
      setState(() => _loading = false);
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Gagal membuat backup: $e')),
        );
      }
    }
  }

  Future<void> _restore() async {
    final text = _restoreCtrl.text.trim();
    if (text.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Tempelkan teks data JSON backup terlebih dahulu.')),
      );
      return;
    }

    Map<String, dynamic> data;
    try {
      data = jsonDecode(text) as Map<String, dynamic>;
    } catch (e) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Format JSON tidak valid.')),
      );
      return;
    }

    final confirm = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        title: const Text('Pulihkan Data?'),
        content: const Text('Data yang ada akan disinkronkan dengan data backup ini.'),
        actions: [
          TextButton(onPressed: () => Navigator.pop(ctx, false), child: const Text('Batal')),
          FilledButton(onPressed: () => Navigator.pop(ctx, true), child: const Text('Pulihkan')),
        ],
      ),
    );

    if (confirm != true) return;

    setState(() => _loading = true);
    try {
      await ApiService.instance.restoreBackup(data);
      setState(() => _loading = false);
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Data berhasil dipulihkan dari cadangan!')),
        );
        Navigator.pop(context, true);
      }
    } catch (e) {
      setState(() => _loading = false);
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Gagal restore: $e')),
        );
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.bg,
      appBar: AppBar(
        title: const Text('💾 Cadangan & Pemulihan'),
      ),
      body: _loading
          ? const Center(child: CircularProgressIndicator())
          : ListView(
              padding: const EdgeInsets.all(16),
              children: [
                // Export Card
                Container(
                  padding: const EdgeInsets.all(16),
                  decoration: BoxDecoration(
                    color: AppColors.card,
                    borderRadius: BorderRadius.circular(18),
                    border: Border.all(color: AppColors.border),
                    boxShadow: AppColors.cardShadow,
                  ),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      const Row(
                        children: [
                          Text('📤', style: TextStyle(fontSize: 20)),
                          SizedBox(width: 8),
                          Text('Ekspor Cadangan Data (JSON)', style: TextStyle(fontSize: 14.5, fontWeight: FontWeight.w800)),
                        ],
                      ),
                      const SizedBox(height: 6),
                      const Text(
                        'Cadangkan seluruh transaksi, rekening dompet, kategori, produk POS, kendaraan, hutang, dan tabungan ke dalam format JSON.',
                        style: TextStyle(fontSize: 12, color: AppColors.textMuted, height: 1.35),
                      ),
                      const SizedBox(height: 14),
                      FilledButton.icon(
                        onPressed: _export,
                        icon: const Icon(Icons.download_rounded, size: 18),
                        label: const Text('Buat Cadangan Sekarang', style: TextStyle(fontWeight: FontWeight.w800)),
                        style: FilledButton.styleFrom(
                          minimumSize: const Size.fromHeight(46),
                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                        ),
                      ),
                      if (_exportedJson != null) ...[
                        const SizedBox(height: 14),
                        Container(
                          padding: const EdgeInsets.all(12),
                          decoration: BoxDecoration(
                            color: AppColors.bg,
                            borderRadius: BorderRadius.circular(12),
                            border: Border.all(color: AppColors.border),
                          ),
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.stretch,
                            children: [
                              Row(
                                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                children: [
                                  const Text('Data Cadangan Tersedia', style: TextStyle(fontSize: 12, fontWeight: FontWeight.w700)),
                                  TextButton.icon(
                                    onPressed: () {
                                      Clipboard.setData(ClipboardData(text: _exportedJson!));
                                      ScaffoldMessenger.of(context).showSnackBar(
                                        const SnackBar(content: Text('Teks cadangan disalin ke clipboard!')),
                                      );
                                    },
                                    icon: const Icon(Icons.copy_rounded, size: 14),
                                    label: const Text('Salin', style: TextStyle(fontSize: 12)),
                                  ),
                                ],
                              ),
                              const SizedBox(height: 4),
                              Text(
                                '${_exportedJson!.length} karakter · Format JSON',
                                style: const TextStyle(fontSize: 11, color: AppColors.textMuted),
                              ),
                            ],
                          ),
                        ),
                      ],
                    ],
                  ),
                ),
                const SizedBox(height: 16),

                // Restore Card
                Container(
                  padding: const EdgeInsets.all(16),
                  decoration: BoxDecoration(
                    color: AppColors.card,
                    borderRadius: BorderRadius.circular(18),
                    border: Border.all(color: AppColors.border),
                    boxShadow: AppColors.cardShadow,
                  ),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      const Row(
                        children: [
                          Text('📥', style: TextStyle(fontSize: 20)),
                          SizedBox(width: 8),
                          Text('Pulihkan Data (Restore)', style: TextStyle(fontSize: 14.5, fontWeight: FontWeight.w800)),
                        ],
                      ),
                      const SizedBox(height: 6),
                      const Text(
                        'Tempelkan teks data JSON yang pernah dicadangkan untuk memulihkan seluruh data aplikasi Anda.',
                        style: TextStyle(fontSize: 12, color: AppColors.textMuted, height: 1.35),
                      ),
                      const SizedBox(height: 14),
                      TextField(
                        controller: _restoreCtrl,
                        maxLines: 5,
                        style: const TextStyle(fontSize: 12, fontFamily: 'monospace'),
                        decoration: const InputDecoration(
                          hintText: '{\n  "categories": [...],\n  "transactions": [...]\n}',
                          labelText: 'TEKS DATA BACKUP JSON',
                          alignLabelWithHint: true,
                        ),
                      ),
                      const SizedBox(height: 14),
                      ElevatedButton.icon(
                        onPressed: _restore,
                        icon: const Icon(Icons.restore_rounded, size: 18),
                        label: const Text('Pulihkan Data', style: TextStyle(fontWeight: FontWeight.w800)),
                        style: ElevatedButton.styleFrom(
                          backgroundColor: const Color(0xFFD97706),
                          foregroundColor: Colors.white,
                          minimumSize: const Size.fromHeight(46),
                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                        ),
                      ),
                    ],
                  ),
                ),
              ],
            ),
    );
  }
}
