class User {
  final int id;
  final String name;
  final String email;
  final String initials;
  final String color;
  final String? avatarImage;

  User({
    required this.id,
    required this.name,
    required this.email,
    required this.initials,
    required this.color,
    this.avatarImage,
  });

  factory User.fromJson(Map<String, dynamic> json) {
    return User(
      id: json['id'] is int ? json['id'] as int : int.tryParse('${json['id']}') ?? 0,
      name: json['name']?.toString() ?? '',
      email: json['email']?.toString() ?? '',
      initials: json['initials']?.toString() ?? 'U',
      color: json['color']?.toString() ?? '#2D5A27',
      avatarImage: json['avatarImage']?.toString(),
    );
  }

  Map<String, dynamic> toJson() => {
        'id': id,
        'name': name,
        'email': email,
        'initials': initials,
        'color': color,
        'avatarImage': avatarImage,
      };
}
