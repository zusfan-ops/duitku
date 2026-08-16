class PosProduct {
  final int id;
  final String name;
  final String category;
  final String? sku;
  final double costPrice;
  final double sellingPrice;
  final int stock;
  final int minStockAlert;
  final String unit;
  final String icon;
  final String? image;
  final bool isActive;

  const PosProduct({
    this.id = 0,
    required this.name,
    this.category = 'Umum',
    this.sku,
    this.costPrice = 0,
    this.sellingPrice = 0,
    this.stock = 0,
    this.minStockAlert = 5,
    this.unit = 'pcs',
    this.icon = 'coffee',
    this.image,
    this.isActive = true,
  });

  bool get isLowStock => stock <= minStockAlert;
  double get margin => sellingPrice - costPrice;
  double get marginPct => costPrice > 0 ? (margin / costPrice) * 100 : 100;

  factory PosProduct.fromJson(Map<String, dynamic> json) {
    return PosProduct(
      id: (json['id'] as num?)?.toInt() ?? 0,
      name: json['name']?.toString() ?? '',
      category: json['category']?.toString() ?? 'Umum',
      sku: json['sku']?.toString(),
      costPrice: double.tryParse('${json['cost_price']}') ?? 0,
      sellingPrice: double.tryParse('${json['selling_price']}') ?? 0,
      stock: (json['stock'] as num?)?.toInt() ?? 0,
      minStockAlert: (json['min_stock_alert'] as num?)?.toInt() ?? 5,
      unit: json['unit']?.toString() ?? 'pcs',
      icon: json['icon']?.toString() ?? 'coffee',
      image: json['image']?.toString(),
      isActive: (json['is_active'] == 1 || json['is_active'] == true || json['is_active'] == '1'),
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'name': name,
      'category': category,
      'sku': sku,
      'cost_price': costPrice,
      'selling_price': sellingPrice,
      'stock': stock,
      'min_stock_alert': minStockAlert,
      'unit': unit,
      'icon': icon,
      'image': image,
      'is_active': isActive ? 1 : 0,
    };
  }
}
