class AppNotification {
  final int id;
  final String title;
  final String message;
  final String type;
  final String? actionUrl;
  final bool isPinned;
  bool isRead;
  final String createdAt;

  AppNotification({
    required this.id,
    required this.title,
    required this.message,
    required this.type,
    this.actionUrl,
    this.isPinned = false,
    this.isRead = false,
    required this.createdAt,
  });

  factory AppNotification.fromJson(Map<String, dynamic> json) {
    return AppNotification(
      id: json['id'] is int ? json['id'] as int : int.tryParse('${json['id']}') ?? 0,
      title: json['title']?.toString() ?? '',
      message: json['message']?.toString() ?? '',
      type: json['type']?.toString() ?? 'info',
      actionUrl: json['action_url']?.toString(),
      isPinned: json['is_pinned'] == true || json['is_pinned'] == 1 || json['is_pinned'] == '1',
      isRead: json['is_read'] == true || json['is_read'] == 1 || json['is_read'] == '1',
      createdAt: json['created_at']?.toString() ?? '',
    );
  }

  Map<String, dynamic> toJson() => {
        'id': id,
        'title': title,
        'message': message,
        'type': type,
        'action_url': actionUrl,
        'is_pinned': isPinned,
        'is_read': isRead,
        'created_at': createdAt,
      };
}
