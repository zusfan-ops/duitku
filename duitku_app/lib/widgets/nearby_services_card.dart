import 'package:flutter/material.dart';
import 'package:url_launcher/url_launcher.dart';
import '../theme.dart';

class NearbyServicesCard extends StatefulWidget {
  const NearbyServicesCard({super.key});

  @override
  State<NearbyServicesCard> createState() => _NearbyServicesCardState();
}

class _NearbyServicesCardState extends State<NearbyServicesCard> {
  String _selectedCategory = 'toko_kelontong';
  int _selectedRadius = 500; // in meters
  bool _isListExpanded = false;
  String _gpsStatus = 'GPS Default';

  // Category Configuration
  final List<Map<String, dynamic>> _categories = [
    {
      'id': 'toko_kelontong',
      'label': 'Toko Kelontong',
      'icon': '🏪',
      'iconData': Icons.storefront_rounded,
      'color': const Color(0xFF10B981),
      'query': 'toko kelontong terdekat warung madura',
      'places': [
        {
          'name': 'Warung Madura 24 Jam',
          'desc': 'Sembako, rokok, pulsa & token 24 jam',
          'dist': '250 m',
          'distNum': 250,
          'eta': '1 mnt',
          'offset': const Offset(-0.35, -0.2),
        },
        {
          'name': 'Toko Kelontong Berkah',
          'desc': 'Beras, minyak goreng, kebutuhan dapur',
          'dist': '420 m',
          'distNum': 420,
          'eta': '2 mnt',
          'offset': const Offset(0.4, -0.3),
        },
        {
          'name': 'Minimarket Barokah Jaya',
          'desc': 'Snack, minuman dingin & sembako lengkap',
          'dist': '650 m',
          'distNum': 650,
          'eta': '3 mnt',
          'offset': const Offset(-0.2, 0.4),
        },
        {
          'name': 'Warung Sembako Bu Siti',
          'desc': 'Sayur mayur segar & kelontong harian',
          'dist': '850 m',
          'distNum': 850,
          'eta': '4 mnt',
          'offset': const Offset(0.35, 0.35),
        },
      ]
    },
    {
      'id': 'spbu',
      'label': 'Pom Bensin (SPBU)',
      'icon': '⛽',
      'iconData': Icons.local_gas_station_rounded,
      'color': const Color(0xFF0284C7),
      'query': 'spbu pom bensin terdekat pertamina',
      'places': [
        {
          'name': 'SPBU Pertamina Pasti Pas 24 Jam',
          'desc': 'Pertalite, Pertamax, Solar, Toilet & Musholla',
          'dist': '450 m',
          'distNum': 450,
          'eta': '2 mnt',
          'offset': const Offset(-0.4, 0.15),
        },
        {
          'name': 'Pertashop Resmi Pertamina',
          'desc': 'Pertamax 92 & Dexlite resmi',
          'dist': '700 m',
          'distNum': 700,
          'eta': '3 mnt',
          'offset': const Offset(0.3, -0.35),
        },
        {
          'name': 'SPBU Shell Express',
          'desc': 'Super, V-Power, Deli2go & Nitrogen',
          'dist': '1.2 km',
          'distNum': 1200,
          'eta': '5 mnt',
          'offset': const Offset(0.1, 0.45),
        },
      ]
    },
    {
      'id': 'tambal_ban',
      'label': 'Tambal Ban',
      'icon': '🔧',
      'iconData': Icons.build_circle_rounded,
      'color': const Color(0xFFEA580C),
      'query': 'tambal ban terdekat bengkel motor',
      'places': [
        {
          'name': 'Tambal Ban Tubeless 24 Jam Pak Joko',
          'desc': 'Tubeless, ban dalam, isi angin nitrogen',
          'dist': '180 m',
          'distNum': 180,
          'eta': '1 mnt',
          'offset': const Offset(-0.25, -0.3),
        },
        {
          'name': 'Bengkel Motor & Tambal Ban Berkah',
          'desc': 'Ganti ban, oli, tambal pres dingin',
          'dist': '520 m',
          'distNum': 520,
          'eta': '2 mnt',
          'offset': const Offset(0.35, 0.2),
        },
        {
          'name': 'Tambal Ban Tip Top Cepat',
          'desc': 'Buka 24 jam motor & mobil',
          'dist': '890 m',
          'distNum': 890,
          'eta': '4 mnt',
          'offset': const Offset(-0.35, 0.35),
        },
      ]
    },
    {
      'id': 'atm',
      'label': 'ATM Bank',
      'icon': '🏧',
      'iconData': Icons.account_balance_rounded,
      'color': const Color(0xFF7C3AED),
      'query': 'atm terdekat bank bca mandiri bri bni',
      'places': [
        {
          'name': 'Galeri ATM BCA 24 Jam',
          'desc': 'Tarik tunai & setor tunai (CRM)',
          'dist': '320 m',
          'distNum': 320,
          'eta': '2 mnt',
          'offset': const Offset(0.25, -0.25),
        },
        {
          'name': 'ATM Mandiri & Link',
          'desc': 'Tarik tunai pecahan Rp 50.000 & 100.000',
          'dist': '550 m',
          'distNum': 550,
          'eta': '3 mnt',
          'offset': const Offset(-0.4, 0.2),
        },
        {
          'name': 'ATM BRI & Agen BRILink',
          'desc': 'Transfer, tarik tunai & pembayaran',
          'dist': '780 m',
          'distNum': 780,
          'eta': '4 mnt',
          'offset': const Offset(0.4, 0.3),
        },
      ]
    },
    {
      'id': 'kuliner',
      'label': 'Warkop / Kuliner',
      'icon': '☕',
      'iconData': Icons.coffee_rounded,
      'color': const Color(0xFFD97706),
      'query': 'warkop warmindo kuliner terdekat',
      'places': [
        {
          'name': 'Warkop Warmindo 24 Jam Barokah',
          'desc': 'Kopi, indomie rebus/goreng, gorengan & WiFi',
          'dist': '290 m',
          'distNum': 290,
          'eta': '2 mnt',
          'offset': const Offset(-0.2, -0.35),
        },
        {
          'name': 'Warung Nasi Padang Sederhana',
          'desc': 'Rendang, ayam bakar, gulai cincang, sambal ijo',
          'dist': '610 m',
          'distNum': 610,
          'eta': '3 mnt',
          'offset': const Offset(0.3, 0.15),
        },
      ]
    },
  ];

  Future<void> _openGoogleMaps(String query) async {
    final uri = Uri.parse('https://www.google.com/maps/search/?api=1&query=${Uri.encodeComponent(query)}');
    try {
      if (await canLaunchUrl(uri)) {
        await launchUrl(uri, mode: LaunchMode.externalApplication);
      }
    } catch (_) {}
  }

  void _simulateGpsRefresh() {
    setState(() => _gpsStatus = 'Mencari GPS...');
    Future.delayed(const Duration(milliseconds: 600), () {
      if (!mounted) return;
      setState(() => _gpsStatus = 'GPS Aktif');
    });
  }

  @override
  Widget build(BuildContext context) {
    final currentCat = _categories.firstWhere(
      (c) => c['id'] == _selectedCategory,
      orElse: () => _categories.first,
    );
    final allPlaces = (currentCat['places'] as List<Map<String, dynamic>>);
    final filteredPlaces = allPlaces.where((p) => (p['distNum'] as int) <= (_selectedRadius == 500 ? 550 : _selectedRadius)).toList();
    final displayPlaces = filteredPlaces.isNotEmpty ? filteredPlaces : allPlaces;

    return Container(
      margin: const EdgeInsets.only(bottom: 14),
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: AppColors.card,
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: AppColors.border),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.03),
            blurRadius: 10,
            offset: const Offset(0, 2),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // ── Header (Title + GPS Pill) ─────────────────────────────
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              const Row(
                children: [
                  Text('📍', style: TextStyle(fontSize: 16)),
                  SizedBox(width: 6),
                  Text(
                    'Layanan Terdekat',
                    style: TextStyle(
                      fontSize: 15,
                      fontWeight: FontWeight.w800,
                      color: AppColors.textPrimary,
                      letterSpacing: -0.2,
                    ),
                  ),
                ],
              ),
              InkWell(
                onTap: _simulateGpsRefresh,
                borderRadius: BorderRadius.circular(20),
                child: Container(
                  padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                  decoration: BoxDecoration(
                    color: const Color(0xFF10B981).withValues(alpha: 0.1),
                    borderRadius: BorderRadius.circular(20),
                    border: Border.all(color: const Color(0xFF10B981).withValues(alpha: 0.3)),
                  ),
                  child: Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      const Text('🎯', style: TextStyle(fontSize: 11)),
                      const SizedBox(width: 4),
                      Text(
                        _gpsStatus,
                        style: const TextStyle(
                          fontSize: 11,
                          fontWeight: FontWeight.w800,
                          color: Color(0xFF059669),
                        ),
                      ),
                    ],
                  ),
                ),
              ),
            ],
          ),
          const SizedBox(height: 12),

          // ── Filter Kategori Layanan (Horizontal Pills) ────────────
          SizedBox(
            height: 34,
            child: ListView.separated(
              scrollDirection: Axis.horizontal,
              itemCount: _categories.length,
              separatorBuilder: (_, _) => const SizedBox(width: 8),
              itemBuilder: (_, idx) {
                final cat = _categories[idx];
                final isSelected = cat['id'] == _selectedCategory;
                final Color catColor = isSelected ? (cat['color'] as Color) : AppColors.card;

                return InkWell(
                  onTap: () {
                    setState(() => _selectedCategory = cat['id'] as String);
                  },
                  borderRadius: BorderRadius.circular(10),
                  child: Container(
                    padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                    decoration: BoxDecoration(
                      color: isSelected ? catColor : AppColors.bg,
                      borderRadius: BorderRadius.circular(10),
                      border: Border.all(
                        color: isSelected ? catColor : AppColors.border,
                      ),
                      boxShadow: isSelected
                          ? [
                              BoxShadow(
                                color: catColor.withValues(alpha: 0.3),
                                blurRadius: 6,
                                offset: const Offset(0, 2),
                              )
                            ]
                          : null,
                    ),
                    child: Row(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        Text(cat['icon'] as String, style: const TextStyle(fontSize: 13)),
                        const SizedBox(width: 6),
                        Text(
                          cat['label'] as String,
                          style: TextStyle(
                            fontSize: 11.5,
                            fontWeight: FontWeight.w700,
                            color: isSelected ? Colors.white : AppColors.textSecondary,
                          ),
                        ),
                      ],
                    ),
                  ),
                );
              },
            ),
          ),
          const SizedBox(height: 10),

          // ── Radius Bar ────────────────────────────────────────────
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              const Text(
                'Radius Jangkauan:',
                style: TextStyle(
                  fontSize: 11.5,
                  fontWeight: FontWeight.w600,
                  color: AppColors.textMuted,
                ),
              ),
              Row(
                children: [500, 1000, 2000, 5000].map((rad) {
                  final isSel = _selectedRadius == rad;
                  final label = rad >= 1000 ? '${rad ~/ 1000} km' : '$rad m';
                  return Padding(
                    padding: const EdgeInsets.only(left: 4),
                    child: InkWell(
                      onTap: () => setState(() => _selectedRadius = rad),
                      borderRadius: BorderRadius.circular(8),
                      child: Container(
                        padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                        decoration: BoxDecoration(
                          color: isSel ? const Color(0xFF0284C7) : AppColors.bg,
                          borderRadius: BorderRadius.circular(8),
                          border: Border.all(
                            color: isSel ? const Color(0xFF0284C7) : AppColors.border,
                          ),
                        ),
                        child: Text(
                          label,
                          style: TextStyle(
                            fontSize: 10.5,
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
          const SizedBox(height: 10),

          // ── Map Container (Leaflet / OpenStreetMap Visual) ────────
          GestureDetector(
            onTap: () => _openGoogleMaps(currentCat['query'] as String),
            child: Container(
              height: 180,
              width: double.infinity,
              decoration: BoxDecoration(
                borderRadius: BorderRadius.circular(14),
                border: Border.all(color: AppColors.border),
                color: const Color(0xFFE2E8F0),
              ),
              clipBehavior: Clip.antiAlias,
              child: Stack(
                children: [
                  // Base Map Tile image with fallback to custom vector canvas
                  Positioned.fill(
                    child: Image.network(
                      'https://tile.openstreetmap.org/15/26105/17036.png',
                      fit: BoxFit.cover,
                      errorBuilder: (context, error, stackTrace) => CustomPaint(
                        painter: _MapCanvasPainter(),
                        size: Size.infinite,
                      ),
                      loadingBuilder: (ctx, child, progress) {
                        if (progress == null) return child;
                        return CustomPaint(
                          painter: _MapCanvasPainter(),
                          size: Size.infinite,
                        );
                      },
                    ),
                  ),

                  // Soft Map Overlay & Radius Circle
                  Positioned.fill(
                    child: Center(
                      child: Container(
                        width: _selectedRadius == 500
                            ? 120
                            : (_selectedRadius == 1000
                                ? 150
                                : 170),
                        height: _selectedRadius == 500
                            ? 120
                            : (_selectedRadius == 1000
                                ? 150
                                : 170),
                        decoration: BoxDecoration(
                          shape: BoxShape.circle,
                          color: (currentCat['color'] as Color).withValues(alpha: 0.12),
                          border: Border.all(
                            color: (currentCat['color'] as Color).withValues(alpha: 0.5),
                            width: 1.5,
                          ),
                        ),
                      ),
                    ),
                  ),

                  // User Current Location Marker (Center)
                  Center(
                    child: Column(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        Container(
                          padding: const EdgeInsets.all(3),
                          decoration: BoxDecoration(
                            shape: BoxShape.circle,
                            color: const Color(0xFF2563EB),
                            border: Border.all(color: Colors.white, width: 2),
                            boxShadow: [
                              BoxShadow(
                                color: const Color(0xFF2563EB).withValues(alpha: 0.4),
                                blurRadius: 8,
                                spreadRadius: 2,
                              ),
                            ],
                          ),
                          child: const Icon(Icons.my_location_rounded, color: Colors.white, size: 12),
                        ),
                      ],
                    ),
                  ),

                  // POI Markers based on active places
                  ...displayPlaces.map((place) {
                    final offset = place['offset'] as Offset;
                    final iconStr = currentCat['icon'] as String;
                    final name = place['name'] as String;

                    return Align(
                      alignment: Alignment(offset.dx, offset.dy),
                      child: GestureDetector(
                        onTap: () => _openGoogleMaps(name),
                        child: Container(
                          padding: const EdgeInsets.symmetric(horizontal: 5, vertical: 3),
                          decoration: BoxDecoration(
                            color: Colors.white,
                            borderRadius: BorderRadius.circular(12),
                            border: Border.all(color: currentCat['color'] as Color, width: 1.5),
                            boxShadow: [
                              BoxShadow(
                                color: Colors.black.withValues(alpha: 0.2),
                                blurRadius: 6,
                                offset: const Offset(0, 2),
                              ),
                            ],
                          ),
                          child: Text(
                            iconStr,
                            style: const TextStyle(fontSize: 13),
                          ),
                        ),
                      ),
                    );
                  }),

                  // Floating Map Action (Top Right Pill)
                  Positioned(
                    top: 8,
                    right: 8,
                    child: Container(
                      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                      decoration: BoxDecoration(
                        color: Colors.white.withValues(alpha: 0.95),
                        borderRadius: BorderRadius.circular(8),
                        boxShadow: [
                          BoxShadow(
                            color: Colors.black.withValues(alpha: 0.1),
                            blurRadius: 4,
                          ),
                        ],
                      ),
                      child: Row(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          Icon(Icons.open_in_new_rounded, size: 12, color: currentCat['color'] as Color),
                          const SizedBox(width: 4),
                          Text(
                            'Buka Google Maps',
                            style: TextStyle(
                              fontSize: 10,
                              fontWeight: FontWeight.w800,
                              color: currentCat['color'] as Color,
                            ),
                          ),
                        ],
                      ),
                    ),
                  ),
                ],
              ),
            ),
          ),
          const SizedBox(height: 10),

          // ── Accordion Header Toggle ───────────────────────────────
          InkWell(
            onTap: () {
              setState(() => _isListExpanded = !_isListExpanded);
            },
            borderRadius: BorderRadius.circular(12),
            child: Container(
              padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 9),
              decoration: BoxDecoration(
                color: AppColors.bg,
                borderRadius: BorderRadius.circular(12),
                border: Border.all(color: AppColors.border),
              ),
              child: Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Row(
                    children: [
                      const Text('📋', style: TextStyle(fontSize: 13)),
                      const SizedBox(width: 6),
                      const Text(
                        'Daftar Tempat Terdekat',
                        style: TextStyle(
                          fontSize: 12,
                          fontWeight: FontWeight.w700,
                          color: AppColors.textPrimary,
                        ),
                      ),
                      const SizedBox(width: 8),
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 2),
                        decoration: BoxDecoration(
                          color: AppColors.primary.withValues(alpha: 0.12),
                          borderRadius: BorderRadius.circular(10),
                        ),
                        child: Text(
                          '${displayPlaces.length} Lokasi',
                          style: const TextStyle(
                            color: AppColors.primary,
                            fontSize: 10,
                            fontWeight: FontWeight.w800,
                          ),
                        ),
                      ),
                    ],
                  ),
                  Icon(
                    _isListExpanded ? Icons.keyboard_arrow_up_rounded : Icons.keyboard_arrow_down_rounded,
                    color: AppColors.textMuted,
                    size: 20,
                  ),
                ],
              ),
            ),
          ),

          // ── Nearby Places List (Accordion Body) ────────────────────
          AnimatedCrossFade(
            firstChild: const SizedBox.shrink(),
            secondChild: Padding(
              padding: const EdgeInsets.only(top: 8),
              child: Column(
                children: displayPlaces.map((place) {
                  final name = place['name'] as String;
                  final dist = place['dist'] as String;
                  final eta = place['eta'] as String;
                  final desc = place['desc'] as String;
                  final iconStr = currentCat['icon'] as String;

                  return Container(
                    margin: const EdgeInsets.only(bottom: 8),
                    padding: const EdgeInsets.all(10),
                    decoration: BoxDecoration(
                      color: AppColors.bg,
                      borderRadius: BorderRadius.circular(12),
                      border: Border.all(color: AppColors.borderLight),
                    ),
                    child: Row(
                      crossAxisAlignment: CrossAxisAlignment.center,
                      children: [
                        Container(
                          width: 36,
                          height: 36,
                          decoration: BoxDecoration(
                            color: (currentCat['color'] as Color).withValues(alpha: 0.12),
                            borderRadius: BorderRadius.circular(10),
                          ),
                          alignment: Alignment.center,
                          child: Text(iconStr, style: const TextStyle(fontSize: 18)),
                        ),
                        const SizedBox(width: 10),
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                name,
                                style: const TextStyle(
                                  fontWeight: FontWeight.w800,
                                  fontSize: 12.5,
                                  color: AppColors.textPrimary,
                                ),
                                maxLines: 1,
                                overflow: TextOverflow.ellipsis,
                              ),
                              const SizedBox(height: 2),
                              Text(
                                desc,
                                style: const TextStyle(fontSize: 11, color: AppColors.textMuted),
                                maxLines: 1,
                                overflow: TextOverflow.ellipsis,
                              ),
                              const SizedBox(height: 4),
                              Row(
                                children: [
                                  Container(
                                    padding: const EdgeInsets.symmetric(horizontal: 5, vertical: 1.5),
                                    decoration: BoxDecoration(
                                      color: AppColors.primary.withValues(alpha: 0.1),
                                      borderRadius: BorderRadius.circular(5),
                                    ),
                                    child: Text(
                                      '📍 $dist',
                                      style: const TextStyle(
                                        fontSize: 10,
                                        fontWeight: FontWeight.w800,
                                        color: AppColors.primary,
                                      ),
                                    ),
                                  ),
                                  const SizedBox(width: 6),
                                  Text(
                                    '• Est. $eta',
                                    style: const TextStyle(fontSize: 10.5, color: AppColors.textMuted),
                                  ),
                                ],
                              ),
                            ],
                          ),
                        ),
                        const SizedBox(width: 8),
                        InkWell(
                          onTap: () => _openGoogleMaps(name),
                          borderRadius: BorderRadius.circular(8),
                          child: Container(
                            padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                            decoration: BoxDecoration(
                              color: AppColors.primary,
                              borderRadius: BorderRadius.circular(8),
                            ),
                            child: const Row(
                              mainAxisSize: MainAxisSize.min,
                              children: [
                                Text('🗺️', style: TextStyle(fontSize: 10)),
                                SizedBox(width: 4),
                                Text(
                                  'Rute',
                                  style: TextStyle(
                                    fontSize: 11,
                                    fontWeight: FontWeight.w800,
                                    color: Colors.white,
                                  ),
                                ),
                              ],
                            ),
                          ),
                        ),
                      ],
                    ),
                  );
                }).toList(),
              ),
            ),
            crossFadeState: _isListExpanded ? CrossFadeState.showSecond : CrossFadeState.showFirst,
            duration: const Duration(milliseconds: 200),
          ),
        ],
      ),
    );
  }
}

/// Fallback Map Canvas Painter with clean street grids & water bodies
class _MapCanvasPainter extends CustomPainter {
  @override
  void paint(Canvas canvas, Size size) {
    final bgPaint = Paint()..color = const Color(0xFFF1F5F9);
    canvas.drawRect(Offset.zero & size, bgPaint);

    // River / Water body
    final waterPaint = Paint()
      ..color = const Color(0xFFBAE6FD)
      ..strokeWidth = 14
      ..style = PaintingStyle.stroke;
    final path = Path()
      ..moveTo(size.width * 0.7, 0)
      ..cubicTo(size.width * 0.65, size.height * 0.4, size.width * 0.75, size.height * 0.6, size.width * 0.7, size.height);
    canvas.drawPath(path, waterPaint);

    // Major Roads
    final roadPaint = Paint()
      ..color = const Color(0xFFFDE68A)
      ..strokeWidth = 8
      ..style = PaintingStyle.stroke;
    canvas.drawLine(Offset(0, size.height * 0.45), Offset(size.width, size.height * 0.55), roadPaint);
    canvas.drawLine(Offset(size.width * 0.35, 0), Offset(size.width * 0.4, size.height), roadPaint);

    // Minor Streets
    final streetPaint = Paint()
      ..color = const Color(0xFFCBD5E1)
      ..strokeWidth = 3
      ..style = PaintingStyle.stroke;
    canvas.drawLine(Offset(0, size.height * 0.2), Offset(size.width * 0.65, size.height * 0.2), streetPaint);
    canvas.drawLine(Offset(0, size.height * 0.8), Offset(size.width, size.height * 0.75), streetPaint);
    canvas.drawLine(Offset(size.width * 0.18, 0), Offset(size.width * 0.18, size.height), streetPaint);
    canvas.drawLine(Offset(size.width * 0.85, 0), Offset(size.width * 0.85, size.height), streetPaint);
  }

  @override
  bool shouldRepaint(covariant CustomPainter oldDelegate) => false;
}
