class TravelTrip {
  final String id;
  final String destination;
  final String? description;
  final String startDate;
  final String? endDate;
  final double budget;
  final DateTime createdAt;

  TravelTrip({
    required this.id,
    required this.destination,
    this.description,
    required this.startDate,
    this.endDate,
    this.budget = 0,
    DateTime? createdAt,
  }) : createdAt = createdAt ?? DateTime.now();

  factory TravelTrip.fromJson(Map<String, dynamic> json) {
    return TravelTrip(
      id: json['id']?.toString() ?? '',
      destination: json['destination']?.toString() ?? '',
      description: json['description']?.toString(),
      startDate: json['start_date']?.toString() ?? '',
      endDate: json['end_date']?.toString(),
      budget: double.tryParse('${json['budget']}') ?? 0,
      createdAt: DateTime.tryParse(json['created_at']?.toString() ?? '') ?? DateTime.now(),
    );
  }

  Map<String, dynamic> toJson() => {
        'id': id,
        'destination': destination,
        'description': description,
        'start_date': startDate,
        'end_date': endDate,
        'budget': budget,
        'created_at': createdAt.toIso8601String(),
      };

  TravelTrip copyWith({
    String? destination,
    String? description,
    String? startDate,
    String? endDate,
    double? budget,
  }) {
    return TravelTrip(
      id: id,
      destination: destination ?? this.destination,
      description: description ?? this.description,
      startDate: startDate ?? this.startDate,
      endDate: endDate ?? this.endDate,
      budget: budget ?? this.budget,
      createdAt: createdAt,
    );
  }
}
