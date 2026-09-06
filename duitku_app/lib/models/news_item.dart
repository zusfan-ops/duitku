class NewsItem {
  final String id;
  final String title;
  final String link;
  final String description;
  final String image;
  final bool hasImage;
  final String source;
  final String sourceKey;
  final String category;
  final String color;
  final String bgColor;
  final String icon;
  final String pubDate;
  final int timestamp;
  final String timeAgo;

  NewsItem({
    required this.id,
    required this.title,
    required this.link,
    required this.description,
    required this.image,
    required this.hasImage,
    required this.source,
    required this.sourceKey,
    required this.category,
    required this.color,
    required this.bgColor,
    required this.icon,
    required this.pubDate,
    required this.timestamp,
    required this.timeAgo,
  });

  factory NewsItem.fromJson(Map<String, dynamic> json) {
    return NewsItem(
      id: json['id']?.toString() ?? '',
      title: json['title']?.toString() ?? '',
      link: json['link']?.toString() ?? '',
      description: json['description']?.toString() ?? '',
      image: json['image']?.toString() ?? '',
      hasImage: json['has_image'] == true || (json['image']?.toString() ?? '').isNotEmpty,
      source: json['source']?.toString() ?? 'Media',
      sourceKey: json['source_key']?.toString() ?? '',
      category: json['category']?.toString() ?? 'Nasional',
      color: json['color']?.toString() ?? '#2563EB',
      bgColor: json['bg_color']?.toString() ?? 'rgba(37, 99, 235, 0.12)',
      icon: json['icon']?.toString() ?? '📰',
      pubDate: json['pub_date']?.toString() ?? '',
      timestamp: int.tryParse('${json['timestamp']}') ?? 0,
      timeAgo: json['time_ago']?.toString() ?? 'Baru saja',
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'title': title,
      'link': link,
      'description': description,
      'image': image,
      'has_image': hasImage,
      'source': source,
      'source_key': sourceKey,
      'category': category,
      'color': color,
      'bg_color': bgColor,
      'icon': icon,
      'pub_date': pubDate,
      'timestamp': timestamp,
      'time_ago': timeAgo,
    };
  }
}
