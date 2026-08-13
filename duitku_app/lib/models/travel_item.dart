class TravelItem {
  final String id;
  final String tripId;
  final String name;
  final bool isPacked;

  TravelItem({
    required this.id,
    required this.tripId,
    required this.name,
    this.isPacked = false,
  });

  factory TravelItem.fromJson(Map<String, dynamic> json) {
    return TravelItem(
      id: json['id']?.toString() ?? '',
      tripId: json['trip_id']?.toString() ?? '',
      name: json['name']?.toString() ?? '',
      isPacked: (json['is_packed']?.toString() ?? '0') == '1',
    );
  }

  Map<String, dynamic> toJson() => {
        'id': id,
        'trip_id': tripId,
        'name': name,
        'is_packed': isPacked ? '1' : '0',
      };

  TravelItem copyWith({String? name, bool? isPacked}) {
    return TravelItem(
      id: id,
      tripId: tripId,
      name: name ?? this.name,
      isPacked: isPacked ?? this.isPacked,
    );
  }
}
