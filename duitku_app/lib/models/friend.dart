class Friend {
  final int friendId;
  final String name;
  final String username;
  final String? avatar;
  final String? phone;
  final String? friendsSince;

  Friend({
    required this.friendId,
    required this.name,
    required this.username,
    this.avatar,
    this.phone,
    this.friendsSince,
  });

  factory Friend.fromJson(Map<String, dynamic> json) {
    return Friend(
      friendId: int.tryParse('${json['friend_id']}') ?? 0,
      name: json['name'] ?? '',
      username: json['username'] ?? '',
      avatar: json['avatar'],
      phone: json['phone'],
      friendsSince: json['friends_since'],
    );
  }
}

class FriendRequest {
  final int requestId;
  final int requesterId;
  final String requesterName;
  final String requesterUsername;
  final String? requesterAvatar;
  final String? createdAt;

  FriendRequest({
    required this.requestId,
    required this.requesterId,
    required this.requesterName,
    required this.requesterUsername,
    this.requesterAvatar,
    this.createdAt,
  });

  factory FriendRequest.fromJson(Map<String, dynamic> json) {
    return FriendRequest(
      requestId: int.tryParse('${json['request_id']}') ?? 0,
      requesterId: int.tryParse('${json['requester_id']}') ?? 0,
      requesterName: json['requester_name'] ?? '',
      requesterUsername: json['requester_username'] ?? '',
      requesterAvatar: json['requester_avatar'],
      createdAt: json['created_at'],
    );
  }
}

class UserSearchResult {
  final int id;
  final String name;
  final String username;
  final String? avatar;
  final String friendStatus; // 'none', 'pending_sent', 'pending_received', 'friends'
  final int? requestId;

  UserSearchResult({
    required this.id,
    required this.name,
    required this.username,
    this.avatar,
    required this.friendStatus,
    this.requestId,
  });

  factory UserSearchResult.fromJson(Map<String, dynamic> json) {
    return UserSearchResult(
      id: int.tryParse('${json['id']}') ?? 0,
      name: json['name'] ?? '',
      username: json['username'] ?? '',
      avatar: json['avatar'],
      friendStatus: json['friend_status'] ?? 'none',
      requestId: json['request_id'] != null ? int.tryParse('${json['request_id']}') : null,
    );
  }
}
