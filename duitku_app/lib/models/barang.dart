/// Model untuk pencatatan barang yang disimpan.
///
/// Digunakan agar user tidak lupa lokasi dan penampakan barang
/// saat menyimpannya.
class Barang {
  String id;
  String name;
  String location;
  String? itemPhoto;
  String? locationPhoto;
  DateTime createdAt;
  DateTime updatedAt;

  Barang({
    required this.id,
    required this.name,
    required this.location,
    this.itemPhoto,
    this.locationPhoto,
    required this.createdAt,
    required this.updatedAt,
  });

  factory Barang.fromJson(Map<String, dynamic> json) => Barang(
        id: json['id']?.toString() ?? '',
        name: json['name']?.toString() ?? '',
        location: json['location']?.toString() ?? '',
        itemPhoto: json['itemPhoto']?.toString(),
        locationPhoto: json['locationPhoto']?.toString(),
        createdAt: _parseDate(json['createdAt']),
        updatedAt: _parseDate(json['updatedAt']),
      );

  Map<String, dynamic> toJson() => {
        'id': id,
        'name': name,
        'location': location,
        if (itemPhoto != null) 'itemPhoto': itemPhoto,
        if (locationPhoto != null) 'locationPhoto': locationPhoto,
        'createdAt': createdAt.toIso8601String(),
        'updatedAt': updatedAt.toIso8601String(),
      };

  Barang copyWith({
    String? id,
    String? name,
    String? location,
    String? itemPhoto,
    String? locationPhoto,
    DateTime? createdAt,
    DateTime? updatedAt,
  }) =>
      Barang(
        id: id ?? this.id,
        name: name ?? this.name,
        location: location ?? this.location,
        itemPhoto: itemPhoto ?? this.itemPhoto,
        locationPhoto: locationPhoto ?? this.locationPhoto,
        createdAt: createdAt ?? this.createdAt,
        updatedAt: updatedAt ?? this.updatedAt,
      );

  static DateTime _parseDate(dynamic value) {
    if (value == null) return DateTime.now();
    if (value is DateTime) return value;
    return DateTime.tryParse(value.toString()) ?? DateTime.now();
  }
}
