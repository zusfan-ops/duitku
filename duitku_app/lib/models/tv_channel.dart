class TvChannel {
  final int id;
  final String name;
  final String category;
  final String streamUrl;
  final String? logoUrl;
  final String description;
  final int sortOrder;

  TvChannel({
    required this.id,
    required this.name,
    required this.category,
    required this.streamUrl,
    this.logoUrl,
    this.description = '',
    this.sortOrder = 0,
  });

  factory TvChannel.fromJson(Map<String, dynamic> json) {
    return TvChannel(
      id: json['id'] is int ? json['id'] as int : int.tryParse('${json['id']}') ?? 0,
      name: json['name']?.toString() ?? '',
      category: json['category']?.toString() ?? 'Nasional',
      streamUrl: json['stream_url']?.toString() ?? '',
      logoUrl: json['logo_url']?.toString(),
      description: json['description']?.toString() ?? '',
      sortOrder: json['sort_order'] is int ? json['sort_order'] as int : int.tryParse('${json['sort_order']}') ?? 0,
    );
  }

  Map<String, dynamic> toJson() => {
        'id': id,
        'name': name,
        'category': category,
        'stream_url': streamUrl,
        'logo_url': logoUrl,
        'description': description,
        'sort_order': sortOrder,
      };
}
