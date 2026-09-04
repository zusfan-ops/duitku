import 'dart:io';
import 'package:flutter/material.dart';
import 'package:image_picker/image_picker.dart';

import '../../services/api_service.dart';
import '../../theme.dart';

class MarketCreateScreen extends StatefulWidget {
  const MarketCreateScreen({super.key});

  @override
  State<MarketCreateScreen> createState() => _MarketCreateScreenState();
}

class _MarketCreateScreenState extends State<MarketCreateScreen> {
  final _formKey = GlobalKey<FormState>();

  String _type = 'sale'; // 'sale' or 'rent'
  String _rentPeriod = 'bulan';
  String _category = 'Motor & Skuter';
  String _condition = 'used_good';

  final TextEditingController _titleCtrl = TextEditingController();
  final TextEditingController _priceCtrl = TextEditingController();
  final TextEditingController _locationCtrl = TextEditingController();
  final TextEditingController _waCtrl = TextEditingController();
  final TextEditingController _thirdPartyCtrl = TextEditingController();
  final TextEditingController _descCtrl = TextEditingController();

  final List<File> _selectedFiles = [];
  bool _isSubmitting = false;

  final List<String> _categories = [
    'Motor & Skuter',
    'Mobil & Truk',
    'Rumah & Properti',
    'Elektronik & Gadget',
    'Komputer & Laptop',
    'Perabotan & Rumah Tangga',
    'Pakaian & Aksesoris',
    'Hobi, Musik & Olahraga',
    'Peralatan Usaha / Bisnis',
    'Lainnya',
  ];

  @override
  void dispose() {
    _titleCtrl.dispose();
    _priceCtrl.dispose();
    _locationCtrl.dispose();
    _waCtrl.dispose();
    _thirdPartyCtrl.dispose();
    _descCtrl.dispose();
    super.dispose();
  }

  Future<void> _pickImages() async {
    final picker = ImagePicker();
    try {
      final pickedList = await picker.pickMultiImage();
      if (pickedList.isNotEmpty) {
        setState(() {
          for (final f in pickedList) {
            _selectedFiles.add(File(f.path));
          }
        });
      }
    } catch (e) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Gagal memilih gambar: $e')),
      );
    }
  }

  Future<void> _pickCamera() async {
    final picker = ImagePicker();
    try {
      final picked = await picker.pickImage(source: ImageSource.camera);
      if (picked != null) {
        setState(() {
          _selectedFiles.add(File(picked.path));
        });
      }
    } catch (e) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Gagal mengambil foto: $e')),
      );
    }
  }

  Future<void> _submit() async {
    if (!_formKey.currentState!.validate()) return;

    setState(() => _isSubmitting = true);

    try {
      // Convert images to base64
      final imagesBase64 = <String>[];
      for (final f in _selectedFiles) {
        final b64 = await ApiService.instance.base64FromFile(f.path);
        if (b64 != null) {
          imagesBase64.add('data:image/jpeg;base64,$b64');
        }
      }

      final payload = {
        'title': _titleCtrl.text.trim(),
        'type': _type,
        'rent_period': _type == 'rent' ? _rentPeriod : null,
        'category': _category,
        'condition': _condition,
        'price': num.tryParse(_priceCtrl.text.replaceAll('.', '').replaceAll(',', '')) ?? 0,
        'location': _locationCtrl.text.trim(),
        'whatsapp': _waCtrl.text.trim(),
        'third_party_url': _thirdPartyCtrl.text.trim(),
        'description': _descCtrl.text.trim(),
        'images': imagesBase64,
      };

      final res = await ApiService.instance.createMarketplaceListing(payload);

      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text(res['message']?.toString() ?? 'Iklan berhasil ditayangkan!')),
        );
        Navigator.pop(context, true);
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Gagal menyimpan iklan: $e')),
        );
      }
    } finally {
      if (mounted) setState(() => _isSubmitting = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.bg,
      appBar: AppBar(
        title: const Text('Pasang Iklan'),
      ),
      body: Form(
        key: _formKey,
        child: ListView(
          padding: const EdgeInsets.fromLTRB(16, 12, 16, 100),
          children: [
            // 1. Type Selector
            _buildCard(
              title: '1. Tipe Transaksi',
              child: Column(
                children: [
                  Row(
                    children: [
                      Expanded(
                        child: GestureDetector(
                          onTap: () => setState(() => _type = 'sale'),
                          child: Container(
                            padding: const EdgeInsets.symmetric(vertical: 12),
                            decoration: BoxDecoration(
                              color: _type == 'sale' ? const Color(0xFF059669).withOpacity(0.12) : AppColors.bg,
                              borderRadius: BorderRadius.circular(12),
                              border: Border.all(
                                color: _type == 'sale' ? const Color(0xFF059669) : AppColors.border,
                                width: 2,
                              ),
                            ),
                            alignment: Alignment.center,
                            child: Text(
                              '🏷️ Dijual',
                              style: TextStyle(
                                fontWeight: FontWeight.w800,
                                color: _type == 'sale' ? const Color(0xFF059669) : AppColors.textMuted,
                              ),
                            ),
                          ),
                        ),
                      ),
                      const SizedBox(width: 10),
                      Expanded(
                        child: GestureDetector(
                          onTap: () => setState(() => _type = 'rent'),
                          child: Container(
                            padding: const EdgeInsets.symmetric(vertical: 12),
                            decoration: BoxDecoration(
                              color: _type == 'rent' ? const Color(0xFF6D28D9).withOpacity(0.12) : AppColors.bg,
                              borderRadius: BorderRadius.circular(12),
                              border: Border.all(
                                color: _type == 'rent' ? const Color(0xFF6D28D9) : AppColors.border,
                                width: 2,
                              ),
                            ),
                            alignment: Alignment.center,
                            child: Text(
                              '🔑 Disewakan',
                              style: TextStyle(
                                fontWeight: FontWeight.w800,
                                color: _type == 'rent' ? const Color(0xFF6D28D9) : AppColors.textMuted,
                              ),
                            ),
                          ),
                        ),
                      ),
                    ],
                  ),
                  if (_type == 'rent') ...[
                    const SizedBox(height: 12),
                    DropdownButtonFormField<String>(
                      value: _rentPeriod,
                      decoration: const InputDecoration(labelText: 'Periode Sewa'),
                      items: const [
                        DropdownMenuItem(value: 'hari', child: Text('Per Hari')),
                        DropdownMenuItem(value: 'bulan', child: Text('Per Bulan')),
                        DropdownMenuItem(value: 'tahun', child: Text('Per Tahun')),
                      ],
                      onChanged: (val) => setState(() => _rentPeriod = val ?? 'bulan'),
                    ),
                  ],
                ],
              ),
            ),

            const SizedBox(height: 14),

            // 2. Photos Picker
            _buildCard(
              title: '2. Foto Produk (Bisa Lebih dari 1)',
              child: Column(
                children: [
                  Row(
                    children: [
                      Expanded(
                        child: OutlinedButton.icon(
                          style: OutlinedButton.styleFrom(
                            padding: const EdgeInsets.symmetric(vertical: 12),
                            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                          ),
                          icon: const Icon(Icons.photo_library_rounded, size: 18),
                          label: const Text('Dari Galeri', style: TextStyle(fontWeight: FontWeight.w700)),
                          onPressed: _pickImages,
                        ),
                      ),
                      const SizedBox(width: 8),
                      Expanded(
                        child: OutlinedButton.icon(
                          style: OutlinedButton.styleFrom(
                            padding: const EdgeInsets.symmetric(vertical: 12),
                            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                          ),
                          icon: const Icon(Icons.camera_alt_rounded, size: 18),
                          label: const Text('Ambil Foto', style: TextStyle(fontWeight: FontWeight.w700)),
                          onPressed: _pickCamera,
                        ),
                      ),
                    ],
                  ),
                  if (_selectedFiles.isNotEmpty) ...[
                    const SizedBox(height: 14),
                    SizedBox(
                      height: 80,
                      child: ListView.separated(
                        scrollDirection: Axis.horizontal,
                        itemCount: _selectedFiles.length,
                        separatorBuilder: (_, __) => const SizedBox(width: 8),
                        itemBuilder: (context, index) {
                          return Stack(
                            children: [
                              ClipRRect(
                                borderRadius: BorderRadius.circular(10),
                                child: Image.file(
                                  _selectedFiles[index],
                                  width: 80,
                                  height: 80,
                                  fit: BoxFit.cover,
                                ),
                              ),
                              Positioned(
                                top: 2,
                                right: 2,
                                child: GestureDetector(
                                  onTap: () => setState(() => _selectedFiles.removeAt(index)),
                                  child: Container(
                                    padding: const EdgeInsets.all(3),
                                    decoration: const BoxDecoration(
                                      color: Colors.black87,
                                      shape: BoxShape.circle,
                                    ),
                                    child: const Icon(Icons.close, size: 12, color: Colors.white),
                                  ),
                                ),
                              ),
                              if (index == 0)
                                Positioned(
                                  bottom: 0,
                                  left: 0,
                                  right: 0,
                                  child: Container(
                                    color: Colors.green.withOpacity(0.85),
                                    padding: const EdgeInsets.symmetric(vertical: 2),
                                    alignment: Alignment.center,
                                    child: const Text('SAMPUL',
                                        style: TextStyle(color: Colors.white, fontSize: 8, fontWeight: FontWeight.w900)),
                                  ),
                                ),
                            ],
                          );
                        },
                      ),
                    ),
                  ],
                ],
              ),
            ),

            const SizedBox(height: 14),

            // 3. Product Info
            _buildCard(
              title: '3. Informasi Produk',
              child: Column(
                children: [
                  TextFormField(
                    controller: _titleCtrl,
                    decoration: const InputDecoration(
                      labelText: 'Judul Iklan *',
                      hintText: 'Contoh: Honda Vario 150 2021 Mulus Siap Pakai',
                    ),
                    validator: (v) => (v == null || v.trim().length < 4) ? 'Judul minimal 4 karakter' : null,
                  ),
                  const SizedBox(height: 12),
                  DropdownButtonFormField<String>(
                    value: _category,
                    decoration: const InputDecoration(labelText: 'Kategori *'),
                    items: _categories.map((c) => DropdownMenuItem(value: c, child: Text(c))).toList(),
                    onChanged: (val) => setState(() => _category = val ?? _categories[0]),
                  ),
                  const SizedBox(height: 12),
                  DropdownButtonFormField<String>(
                    value: _condition,
                    decoration: const InputDecoration(labelText: 'Kondisi *'),
                    items: const [
                      DropdownMenuItem(value: 'used_good', child: Text('Bekas (Mulus / Siap Pakai)')),
                      DropdownMenuItem(value: 'like_new', child: Text('Bekas (Seperti Baru)')),
                      DropdownMenuItem(value: 'used_fair', child: Text('Bekas (Layak Pakai / Minus)')),
                      DropdownMenuItem(value: 'new', child: Text('Baru (Belum Pernah Dipakai)')),
                    ],
                    onChanged: (val) => setState(() => _condition = val ?? 'used_good'),
                  ),
                  const SizedBox(height: 12),
                  TextFormField(
                    controller: _priceCtrl,
                    keyboardType: TextInputType.number,
                    decoration: const InputDecoration(
                      labelText: 'Harga (Rp) *',
                      hintText: 'Contoh: 15000000',
                    ),
                    validator: (v) => (v == null || v.trim().isEmpty) ? 'Harga wajib diisi' : null,
                  ),
                  const SizedBox(height: 12),
                  TextFormField(
                    controller: _locationCtrl,
                    decoration: const InputDecoration(
                      labelText: 'Lokasi COD / Wilayah *',
                      hintText: 'Contoh: Tebet, Jakarta Selatan',
                    ),
                    validator: (v) => (v == null || v.trim().isEmpty) ? 'Lokasi COD wajib diisi' : null,
                  ),
                  const SizedBox(height: 12),
                  TextFormField(
                    controller: _descCtrl,
                    maxLines: 4,
                    decoration: const InputDecoration(
                      labelText: 'Deskripsi Lengkap',
                      hintText: 'Jelaskan kondisi surat, kelengkapan, minus, atau riwayat pemakaian.',
                    ),
                  ),
                ],
              ),
            ),

            const SizedBox(height: 14),

            // 4. Contact & Online Safe Transaction
            _buildCard(
              title: '4. Kontak & Pembayaran Online Aman',
              child: Column(
                children: [
                  TextFormField(
                    controller: _waCtrl,
                    keyboardType: TextInputType.phone,
                    decoration: const InputDecoration(
                      labelText: 'Nomor WhatsApp *',
                      hintText: '08xxxxxxxxxx',
                    ),
                    validator: (v) => (v == null || v.trim().isEmpty) ? 'Nomor WhatsApp wajib diisi' : null,
                  ),
                  const SizedBox(height: 12),
                  TextFormField(
                    controller: _thirdPartyCtrl,
                    keyboardType: TextInputType.url,
                    decoration: const InputDecoration(
                      labelText: 'Link Shopee / Tokopedia (Opsional)',
                      hintText: 'https://shopee.co.id/...',
                      helperText: 'Untuk transaksi online aman bergaransi rekening bersama.',
                    ),
                  ),
                ],
              ),
            ),

            const SizedBox(height: 20),

            // Submit Button
            SizedBox(
              width: double.infinity,
              child: ElevatedButton(
                style: ElevatedButton.styleFrom(
                  backgroundColor: const Color(0xFF059669),
                  padding: const EdgeInsets.symmetric(vertical: 14),
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                ),
                onPressed: _isSubmitting ? null : _submit,
                child: _isSubmitting
                    ? const SizedBox(width: 22, height: 22, child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2.5))
                    : const Text(
                        '🚀 Tayangkan Iklan Sekarang',
                        style: TextStyle(fontWeight: FontWeight.w900, color: Colors.white, fontSize: 14),
                      ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildCard({required String title, required Widget child}) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: AppColors.card,
        borderRadius: BorderRadius.circular(18),
        border: Border.all(color: AppColors.border),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            title,
            style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w800, color: Color(0xFF4338CA)),
          ),
          const SizedBox(height: 12),
          child,
        ],
      ),
    );
  }
}
