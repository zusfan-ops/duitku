import 'package:flutter/material.dart';

class EmergencyContact {
  final String id;
  final String name;
  final String category;
  final String number;
  final String description;
  final String icon;
  final Color color;
  final bool isTollFree;
  final int priority;

  const EmergencyContact({
    required this.id,
    required this.name,
    required this.category,
    required this.number,
    required this.description,
    required this.icon,
    required this.color,
    this.isTollFree = false,
    this.priority = 99,
  });

  factory EmergencyContact.fromJson(Map<String, dynamic> json) {
    Color parseColor(dynamic hex) {
      if (hex == null) return const Color(0xFFEF4444);
      final s = hex.toString().replaceAll('#', '');
      final val = int.tryParse('FF$s', radix: 16) ?? 0xFFEF4444;
      return Color(val);
    }

    return EmergencyContact(
      id: json['id']?.toString() ?? '',
      name: json['name']?.toString() ?? '',
      category: json['category']?.toString() ?? 'Umum',
      number: json['number']?.toString() ?? '',
      description: json['description']?.toString() ?? '',
      icon: json['icon']?.toString() ?? '🚨',
      color: parseColor(json['color']),
      isTollFree: json['is_toll_free'] == true || json['is_toll_free'] == 1,
      priority: (json['priority'] as num?)?.toInt() ?? 99,
    );
  }

  /// Offline fallback list with verified national Indonesian emergency hotlines
  static const List<EmergencyContact> defaults = [
    EmergencyContact(
      id: 'emergency_112',
      name: 'Panggilan Darurat Terpadu (112)',
      category: 'Umum',
      number: '112',
      description: 'Layanan darurat terpadu (Polisi, Medis, Damkar, Bencana) bebas pulsa 24 jam.',
      icon: '🚨',
      color: Color(0xFFEF4444),
      isTollFree: true,
      priority: 1,
    ),
    EmergencyContact(
      id: 'police_110',
      name: 'Kepolisian RI (Polri)',
      category: 'Keamanan',
      number: '110',
      description: 'Bantuan keamanan, tindak kriminal, kecelakaan lalu lintas, dan posko polisi darurat.',
      icon: '🚓',
      color: Color(0xFF3B82F6),
      isTollFree: true,
      priority: 2,
    ),
    EmergencyContact(
      id: 'fire_113',
      name: 'Pemadam Kebakaran (Damkar)',
      category: 'Penyelamatan',
      number: '113',
      description: 'Penanganan kebakaran, evakuasi binatang berbisa, pelepasan cincin, dan penyelamatan darurat.',
      icon: '🚒',
      color: Color(0xFFF97316),
      isTollFree: true,
      priority: 3,
    ),
    EmergencyContact(
      id: 'ambulance_118',
      name: 'Ambulans Gawat Darurat (AGD)',
      category: 'Medis',
      number: '118',
      description: 'Layanan ambulans gawat darurat dan penanganan pertolongan pertama Kemenkes.',
      icon: '🚑',
      color: Color(0xFF10B981),
      isTollFree: true,
      priority: 4,
    ),
    EmergencyContact(
      id: 'kemenkes_119',
      name: 'Command Center Medis 119',
      category: 'Medis',
      number: '119',
      description: 'Sistem penanggulangan gawat darurat terpadu (SPGDT) Kementerian Kesehatan RI.',
      icon: '🏥',
      color: Color(0xFF059669),
      isTollFree: true,
      priority: 5,
    ),
    EmergencyContact(
      id: 'toll_jasamarga',
      name: 'Derek & Bantuan Tol Jasa Marga',
      category: 'Derek Tol',
      number: '14080',
      description: 'Bantuan derek resmi jalan tol Jasa Marga, patroli tol, dan informasi jalan tol 24 jam.',
      icon: '🚗',
      color: Color(0xFF8B5CF6),
      isTollFree: false,
      priority: 6,
    ),
    EmergencyContact(
      id: 'toll_astrainfra',
      name: 'Derek & Bantuan Tol Astra Infra',
      category: 'Derek Tol',
      number: '02189840000',
      description: 'Call center bantuan & derek jalan tol ruas Astra Infra (Cikopo-Palimanan, Tangerang-Merak, dll).',
      icon: '🛣️',
      color: Color(0xFF6366F1),
      isTollFree: false,
      priority: 7,
    ),
    EmergencyContact(
      id: 'pertamina_135',
      name: 'Pertamina Delivery BBM Darurat',
      category: 'Derek Tol',
      number: '135',
      description: 'Layanan antar BBM darurat saat mogok/kehabisan bensin di jalan tol dan non-tol.',
      icon: '⛽',
      color: Color(0xFFD97706),
      isTollFree: false,
      priority: 8,
    ),
    EmergencyContact(
      id: 'basarnas_115',
      name: 'SAR & Basarnas',
      category: 'Penyelamatan',
      number: '115',
      description: 'Operasi pencarian dan pertolongan korban bencana alam, musibah pelayaran, dan penerbangan.',
      icon: '⛑️',
      color: Color(0xFFEC4899),
      isTollFree: true,
      priority: 9,
    ),
    EmergencyContact(
      id: 'pln_123',
      name: 'PLN Gangguan Listrik & Korslet',
      category: 'Utilitas',
      number: '123',
      description: 'Laporan korsleting listrik, tiang roboh, trafo meledak, dan padam darurat 24 jam.',
      icon: '⚡',
      color: Color(0xFF0284C7),
      isTollFree: false,
      priority: 10,
    ),
    EmergencyContact(
      id: 'pmi_darurat',
      name: 'Palang Merah Indonesia (PMI)',
      category: 'Medis',
      number: '0217992325',
      description: 'Bantuan donor darah darurat, posko bencana alam, dan ambulans PMI.',
      icon: '🩸',
      color: Color(0xFFE11D48),
      isTollFree: false,
      priority: 11,
    ),
    EmergencyContact(
      id: 'bpjs_165',
      name: 'BPJS Kesehatan Care Center',
      category: 'Medis',
      number: '165',
      description: 'Informasi faskes rujukan darurat, pelayanan administrasi dan kepesertaan 24 jam.',
      icon: '🩺',
      color: Color(0xFF0D9488),
      isTollFree: false,
      priority: 12,
    ),
  ];
}
