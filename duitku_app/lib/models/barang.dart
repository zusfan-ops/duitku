class MaintenanceTask {
  final String id;
  final String title;
  final String frequency;
  final String dueDate;
  bool isDone;

  MaintenanceTask({
    required this.id,
    required this.title,
    this.frequency = 'Setiap 6 Bulan',
    this.dueDate = '',
    this.isDone = false,
  });

  factory MaintenanceTask.fromJson(Map<String, dynamic> json) => MaintenanceTask(
        id: json['id']?.toString() ?? '',
        title: json['title']?.toString() ?? 'Perawatan',
        frequency: json['frequency']?.toString() ?? 'Setiap 6 Bulan',
        dueDate: json['due_date']?.toString() ?? json['dueDate']?.toString() ?? '',
        isDone: json['is_done'] == true || json['isDone'] == true || json['is_done'] == 1,
      );

  Map<String, dynamic> toJson() => {
        'id': id,
        'title': title,
        'frequency': frequency,
        'due_date': dueDate,
        'is_done': isDone,
      };
}

class WarrantyItem {
  final String id;
  final String provider;
  final String expiryDate;
  final String status;
  final String notes;

  WarrantyItem({
    required this.id,
    required this.provider,
    required this.expiryDate,
    this.status = 'Aktif',
    this.notes = '',
  });

  factory WarrantyItem.fromJson(Map<String, dynamic> json) => WarrantyItem(
        id: json['id']?.toString() ?? '',
        provider: json['provider']?.toString() ?? 'Resmi',
        expiryDate: json['expiry_date']?.toString() ?? json['expiryDate']?.toString() ?? '',
        status: json['status']?.toString() ?? 'Aktif',
        notes: json['notes']?.toString() ?? '',
      );

  Map<String, dynamic> toJson() => {
        'id': id,
        'provider': provider,
        'expiry_date': expiryDate,
        'status': status,
        'notes': notes,
      };
}

/// Model untuk pencatatan barang / aset rumah tangga (My Home Inventory).
class Barang {
  String id;
  String name;
  String location; // Room name
  String room;
  String category;
  String brand;
  String purchaseDate;
  double purchasePrice;
  String? itemPhoto;
  String? locationPhoto;
  List<MaintenanceTask> maintenance;
  List<WarrantyItem> warranties;
  DateTime createdAt;
  DateTime updatedAt;

  Barang({
    required this.id,
    required this.name,
    required this.location,
    String? room,
    this.category = 'Perlengkapan',
    this.brand = '',
    this.purchaseDate = '',
    this.purchasePrice = 0,
    this.itemPhoto,
    this.locationPhoto,
    this.maintenance = const [],
    this.warranties = const [],
    required this.createdAt,
    required this.updatedAt,
  }) : room = (room != null && room.isNotEmpty) ? room : location;

  factory Barang.fromJson(Map<String, dynamic> json) {
    final loc = json['location']?.toString() ?? json['room']?.toString() ?? 'Lainnya';
    final rm = json['room']?.toString() ?? loc;

    final maintRaw = json['maintenance'] as List<dynamic>? ?? [];
    final maintList = maintRaw
        .map((e) => MaintenanceTask.fromJson(e as Map<String, dynamic>))
        .toList();

    final warrRaw = json['warranties'] as List<dynamic>? ?? [];
    final warrList = warrRaw
        .map((e) => WarrantyItem.fromJson(e as Map<String, dynamic>))
        .toList();

    return Barang(
      id: json['id']?.toString() ?? '',
      name: json['name']?.toString() ?? '',
      location: loc,
      room: rm,
      category: json['category']?.toString() ?? 'Perlengkapan',
      brand: json['brand']?.toString() ?? '',
      purchaseDate: json['purchase_date']?.toString() ?? json['purchaseDate']?.toString() ?? '',
      purchasePrice: double.tryParse('${json['purchase_price'] ?? json['purchasePrice']}') ?? 0,
      itemPhoto: json['itemPhoto']?.toString() ?? json['item_photo']?.toString(),
      locationPhoto: json['locationPhoto']?.toString() ?? json['location_photo']?.toString(),
      maintenance: maintList,
      warranties: warrList,
      createdAt: _parseDate(json['createdAt'] ?? json['created_at']),
      updatedAt: _parseDate(json['updatedAt'] ?? json['updated_at']),
    );
  }

  Map<String, dynamic> toJson() => {
        'id': id,
        'name': name,
        'location': location,
        'room': room,
        'category': category,
        'brand': brand,
        'purchase_date': purchaseDate,
        'purchase_price': purchasePrice,
        if (itemPhoto != null) 'itemPhoto': itemPhoto,
        if (itemPhoto != null) 'item_photo': itemPhoto,
        if (locationPhoto != null) 'locationPhoto': locationPhoto,
        if (locationPhoto != null) 'location_photo': locationPhoto,
        'maintenance': maintenance.map((m) => m.toJson()).toList(),
        'warranties': warranties.map((w) => w.toJson()).toList(),
        'createdAt': createdAt.toIso8601String(),
        'updatedAt': updatedAt.toIso8601String(),
      };

  Barang copyWith({
    String? id,
    String? name,
    String? location,
    String? room,
    String? category,
    String? brand,
    String? purchaseDate,
    double? purchasePrice,
    String? itemPhoto,
    String? locationPhoto,
    List<MaintenanceTask>? maintenance,
    List<WarrantyItem>? warranties,
    DateTime? createdAt,
    DateTime? updatedAt,
  }) =>
      Barang(
        id: id ?? this.id,
        name: name ?? this.name,
        location: location ?? this.location,
        room: room ?? this.room,
        category: category ?? this.category,
        brand: brand ?? this.brand,
        purchaseDate: purchaseDate ?? this.purchaseDate,
        purchasePrice: purchasePrice ?? this.purchasePrice,
        itemPhoto: itemPhoto ?? this.itemPhoto,
        locationPhoto: locationPhoto ?? this.locationPhoto,
        maintenance: maintenance ?? this.maintenance,
        warranties: warranties ?? this.warranties,
        createdAt: createdAt ?? this.createdAt,
        updatedAt: updatedAt ?? this.updatedAt,
      );

  static DateTime _parseDate(dynamic value) {
    if (value == null) return DateTime.now();
    if (value is DateTime) return value;
    return DateTime.tryParse(value.toString()) ?? DateTime.now();
  }
}
