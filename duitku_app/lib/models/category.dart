class Category {
  final int id;
  final String name;
  final String type; // income | expense
  final String icon;
  final String color;
  final bool isDefault;

  Category({
    required this.id,
    required this.name,
    required this.type,
    required this.icon,
    required this.color,
    this.isDefault = false,
  });

  factory Category.fromJson(Map<String, dynamic> json) {
    return Category(
      id: json['id'] is int ? json['id'] as int : int.tryParse('${json['id']}') ?? 0,
      name: json['name']?.toString() ?? '',
      type: json['type']?.toString() ?? 'expense',
      icon: json['icon']?.toString() ?? 'other',
      color: json['color']?.toString() ?? '#6B7280',
      isDefault: (json['is_default']?.toString() ?? '0') == '1',
    );
  }
}
