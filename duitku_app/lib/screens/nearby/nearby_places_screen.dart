import 'package:flutter/material.dart';
import 'package:url_launcher/url_launcher.dart';

import '../../theme.dart';

class NearbyPlacesScreen extends StatefulWidget {
  const NearbyPlacesScreen({super.key});

  @override
  State<NearbyPlacesScreen> createState() => _NearbyPlacesScreenState();
}

class _NearbyPlacesScreenState extends State<NearbyPlacesScreen> {
  String _selectedCategory = 'toko_kelontong';
  int _selectedRadius = 500; // meters

  final List<Map<String, dynamic>> _categories = [
    {
      'id': 'toko_kelontong',
      'label': 'Toko Kelontong',
      'icon': Icons.storefront_rounded,
      'query': 'toko kelontong terdekat warung madura',
      'color': const Color(0xFF10B981),
      'places': [
        {'name': 'Warung Madura 24 Jam', 'dist': '250 m', 'eta': '1 mnt', 'desc': 'Sembako, rokok, pulsa & token 24 jam'},
        {'name': 'Toko Kelontong Berkah', 'dist': '420 m', 'eta': '2 mnt', 'desc': 'Beras, minyak goreng, kebutuhan dapur'},
        {'name': 'Minimarket Barokah Jaya', 'dist': '650 m', 'eta': '3 mnt', 'desc': 'Snack, minuman dingin & sembako lengkap'},
        {'name': 'Warung Sembako Bu Siti', 'dist': '850 m', 'eta': '4 mnt', 'desc': 'Sayur mayur segar & kelontong'},
      ]
    },
    {
      'id': 'spbu',
      'label': 'Pom Bensin (SPBU)',
      'icon': Icons.local_gas_station_rounded,
      'query': 'spbu pom bensin terdekat pertamina',
      'color': const Color(0xFF0284C7),
      'places': [
        {'name': 'SPBU Pertamina Pasti Pas 24 Jam', 'dist': '450 m', 'eta': '2 mnt', 'desc': 'Pertalite, Pertamax, Solar, Toilet & Musholla'},
        {'name': 'Pertashop Resmi Pertamina', 'dist': '700 m', 'eta': '3 mnt', 'desc': 'Pertamax 92 & Dexlite resmi'},
        {'name': 'SPBU Shell Express', 'dist': '1.2 km', 'eta': '5 mnt', 'desc': 'Super, V-Power, Deli2go & Nitrogen'},
      ]
    },
    {
      'id': 'tambal_ban',
      'label': 'Tambal Ban',
      'icon': Icons.build_circle_rounded,
      'query': 'tambal ban terdekat bengkel motor',
      'color': const Color(0xFFEA580C),
      'places': [
        {'name': 'Tambal Ban Tubeless 24 Jam Pak Joko', 'dist': '180 m', 'eta': '1 mnt', 'desc': 'Tubeless, ban dalam, isi angin nitrogen'},
        {'name': 'Bengkel Motor & Tambal Ban Berkah', 'dist': '520 m', 'eta': '2 mnt', 'desc': 'Ganti ban, oli, tambal pres dingin'},
        {'name': 'Tambal Ban Tip Top Cepat', 'dist': '890 m', 'eta': '4 mnt', 'desc': 'Buka 24 jam motor & mobil'},
      ]
    },
    {
      'id': 'atm',
      'label': 'ATM & Bank',
      'icon': Icons.account_balance_rounded,
      'color': const Color(0xFF7C3AED),
      'query': 'atm terdekat bank bca mandiri bri bni',
      'places': [
        {'name': 'Galeri ATM BCA 24 Jam', 'dist': '320 m', 'eta': '2 mnt', 'desc': 'Tarik tunai & setor tunai (CRM)'},
        {'name': 'ATM Mandiri & Link', 'dist': '550 m', 'eta': '3 mnt', 'desc': 'Tarik tunai pecahan Rp 50.000 & 100.000'},
        {'name': 'ATM BRI & Agen BRILink', 'dist': '780 m', 'eta': '4 mnt', 'desc': 'Transfer, tarik tunai & pembayaran'},
      ]
    },
    {
      'id': 'kuliner',
      'label': 'Warkop / Kuliner',
      'icon': Icons.coffee_rounded,
      'color': const Color(0xFFD97706),
      'query': 'warkop warmindo kuliner terdekat',
      'places': [
        {'name': 'Warkop Warmindo 24 Jam Barokah', 'dist': '290 m', 'eta': '2 mnt', 'desc': 'Kopi, indomie rebus/goreng, gorengan & WiFi'},
        {'name': 'Warung Nasi Padang Sederhana', 'dist': '610 m', 'eta': '3 mnt', 'desc': 'Rendang, ayam bakar, sambal ijo'},
      ]
    },
  ];

  Future<void> _openGoogleMaps(String queryName) async {
    final uri = Uri.parse('https://www.google.com/maps/search/?api=1&query=${Uri.encodeComponent(queryName)}');
    try {
      if (await canLaunchUrl(uri)) {
        await launchUrl(uri, mode: LaunchMode.externalApplication);
      }
    } catch (_) {}
  }

  @override
  Widget build(BuildContext context) {
    final currentCatData = _categories.firstWhere(
      (c) => c['id'] == _selectedCategory,
      orElse: () => _categories.first,
    );
    final List places = currentCatData['places'] as List;

    return Scaffold(
      backgroundColor: AppColors.bg,
      appBar: AppBar(
        title: const Text('Layanan & Toko Terdekat'),
        centerTitle: false,
        actions: [
          IconButton(
            icon: const Icon(Icons.my_location_rounded),
            tooltip: 'Buka di Google Maps',
            onPressed: () => _openGoogleMaps(currentCatData['query'] as String),
          ),
        ],
      ),
      body: ListView(
        padding: const EdgeInsets.fromLTRB(16, 12, 16, 100),
        children: [
          // Banner Info
          Container(
            padding: const EdgeInsets.all(14),
            decoration: BoxDecoration(
              gradient: const LinearGradient(
                colors: [Color(0xFF0F172A), Color(0xFF1E293B)],
                begin: Alignment.topLeft,
                end: Alignment.bottomRight,
              ),
              borderRadius: BorderRadius.circular(16),
            ),
            child: Row(
              children: [
                Container(
                  width: 44,
                  height: 44,
                  decoration: BoxDecoration(
                    color: AppColors.primary.withValues(alpha: 0.2),
                    borderRadius: BorderRadius.circular(12),
                  ),
                  child: const Icon(Icons.explore_rounded, color: AppColors.primary, size: 24),
                ),
                const SizedBox(width: 12),
                const Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        'Temukan Layanan Cepat',
                        style: TextStyle(color: Colors.white, fontWeight: FontWeight.w800, fontSize: 13.5),
                      ),
                      SizedBox(height: 2),
                      Text(
                        'Pilih toko kelontong, SPBU, tambal ban atau ATM untuk petunjuk arah langsung.',
                        style: TextStyle(color: Color(0xFF94A3B8), fontSize: 11.5),
                      ),
                    ],
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(height: 16),

          // Category Selector Pills
          SizedBox(
            height: 40,
            child: ListView.separated(
              scrollDirection: Axis.horizontal,
              itemCount: _categories.length,
              separatorBuilder: (_, _) => const SizedBox(width: 8),
              itemBuilder: (_, idx) {
                final cat = _categories[idx];
                final isSelected = cat['id'] == _selectedCategory;
                return ChoiceChip(
                  avatar: Icon(cat['icon'] as IconData, size: 16, color: isSelected ? Colors.white : AppColors.textSecondary),
                  label: Text(cat['label'] as String),
                  selected: isSelected,
                  selectedColor: AppColors.primary,
                  backgroundColor: AppColors.card,
                  labelStyle: TextStyle(
                    color: isSelected ? Colors.white : AppColors.textSecondary,
                    fontWeight: FontWeight.w700,
                    fontSize: 12,
                  ),
                  onSelected: (selected) {
                    if (selected) {
                      setState(() => _selectedCategory = cat['id'] as String);
                    }
                  },
                );
              },
            ),
          ),
          const SizedBox(height: 14),

          // Radius Filter Row
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              const Text(
                'Radius Jangkauan:',
                style: TextStyle(fontSize: 12, fontWeight: FontWeight.w700, color: AppColors.textSecondary),
              ),
              Row(
                children: [500, 1000, 2000, 5000].map((rad) {
                  final isSel = _selectedRadius == rad;
                  final label = rad >= 1000 ? '${rad ~/ 1000} km' : '$rad m';
                  return Padding(
                    padding: const EdgeInsets.only(left: 6),
                    child: InkWell(
                      onTap: () => setState(() => _selectedRadius = rad),
                      borderRadius: BorderRadius.circular(8),
                      child: Container(
                        padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                        decoration: BoxDecoration(
                          color: isSel ? const Color(0xFF0284C7) : AppColors.card,
                          borderRadius: BorderRadius.circular(8),
                          border: Border.all(
                            color: isSel ? const Color(0xFF0284C7) : AppColors.border,
                          ),
                        ),
                        child: Text(
                          label,
                          style: TextStyle(
                            fontSize: 11,
                            fontWeight: FontWeight.w700,
                            color: isSel ? Colors.white : AppColors.textSecondary,
                          ),
                        ),
                      ),
                    ),
                  );
                }).toList(),
              ),
            ],
          ),
          const SizedBox(height: 16),

          // Open in Maps Primary Action
          InkWell(
            onTap: () => _openGoogleMaps(currentCatData['query'] as String),
            borderRadius: BorderRadius.circular(16),
            child: Container(
              padding: const EdgeInsets.all(14),
              decoration: BoxDecoration(
                color: (currentCatData['color'] as Color).withValues(alpha: 0.08),
                borderRadius: BorderRadius.circular(16),
                border: Border.all(color: (currentCatData['color'] as Color).withValues(alpha: 0.3)),
              ),
              child: Row(
                children: [
                  Icon(Icons.map_rounded, color: currentCatData['color'] as Color, size: 28),
                  const SizedBox(width: 12),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          'Buka Semua ${currentCatData['label']} di Google Maps',
                          style: TextStyle(
                            fontWeight: FontWeight.w800,
                            fontSize: 13,
                            color: currentCatData['color'] as Color,
                          ),
                        ),
                        const SizedBox(height: 2),
                        const Text(
                          'Pindai live navigasi & kondisi lalu lintas real-time',
                          style: TextStyle(fontSize: 11, color: AppColors.textSecondary),
                        ),
                      ],
                    ),
                  ),
                  const Icon(Icons.open_in_new_rounded, size: 18, color: AppColors.textSecondary),
                ],
              ),
            ),
          ),
          const SizedBox(height: 16),

          // List of Places
          Text(
            'LOKASI DI SEKITAR ANDA (${places.length})',
            style: const TextStyle(fontSize: 11, fontWeight: FontWeight.w800, color: AppColors.textSecondary, letterSpacing: 0.6),
          ),
          const SizedBox(height: 10),

          ...places.map((p) {
            final name = p['name'] as String;
            final dist = p['dist'] as String;
            final eta = p['eta'] as String;
            final desc = p['desc'] as String;

            return Card(
              margin: const EdgeInsets.only(bottom: 10),
              shape: RoundedRectangleBorder(
                borderRadius: BorderRadius.circular(16),
                side: const BorderSide(color: AppColors.border),
              ),
              color: AppColors.card,
              elevation: 0,
              child: Padding(
                padding: const EdgeInsets.all(14),
                child: Row(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Container(
                      width: 40,
                      height: 40,
                      decoration: BoxDecoration(
                        color: (currentCatData['color'] as Color).withValues(alpha: 0.12),
                        borderRadius: BorderRadius.circular(12),
                      ),
                      child: Icon(currentCatData['icon'] as IconData, color: currentCatData['color'] as Color, size: 20),
                    ),
                    const SizedBox(width: 12),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            name,
                            style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 13.5),
                          ),
                          const SizedBox(height: 4),
                          Text(
                            desc,
                            style: const TextStyle(fontSize: 11.5, color: AppColors.textSecondary),
                          ),
                          const SizedBox(height: 8),
                          Row(
                            children: [
                              Container(
                                padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                                decoration: BoxDecoration(
                                  color: AppColors.primary.withValues(alpha: 0.12),
                                  borderRadius: BorderRadius.circular(6),
                                ),
                                child: Text(
                                  '📍 $dist',
                                  style: const TextStyle(
                                    color: AppColors.primary,
                                    fontSize: 10.5,
                                    fontWeight: FontWeight.w800,
                                  ),
                                ),
                              ),
                              const SizedBox(width: 6),
                              Text(
                                '• Est. $eta',
                                style: const TextStyle(fontSize: 11, color: AppColors.textSecondary),
                              ),
                            ],
                          ),
                        ],
                      ),
                    ),
                    const SizedBox(width: 8),
                    ElevatedButton(
                      style: ElevatedButton.styleFrom(
                        backgroundColor: AppColors.primary,
                        foregroundColor: Colors.white,
                        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 8),
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                        elevation: 0,
                        visualDensity: VisualDensity.compact,
                      ),
                      onPressed: () => _openGoogleMaps(name),
                      child: const Text('Rute', style: TextStyle(fontWeight: FontWeight.w800, fontSize: 12)),
                    ),
                  ],
                ),
              ),
            );
          }),
        ],
      ),
    );
  }
}
