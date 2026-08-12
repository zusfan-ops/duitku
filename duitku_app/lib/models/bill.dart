class Bill {
  final String id;
  final String name;
  final double amount;
  final int dueDay;
  final String notes;
  final int? daysLeft;

  Bill({
    required this.id,
    required this.name,
    this.amount = 0,
    this.dueDay = 1,
    this.notes = '',
    this.daysLeft,
  });

  factory Bill.fromJson(Map<String, dynamic> json) {
    return Bill(
      id: json['id']?.toString() ?? '',
      name: json['name']?.toString() ?? '',
      amount: double.tryParse('${json['amount']}') ?? 0,
      dueDay: json['dueDay'] is int ? json['dueDay'] as int : int.tryParse('${json['dueDay']}') ?? 1,
      notes: json['notes']?.toString() ?? '',
      daysLeft: json['daysLeft'] == null
          ? null
          : json['daysLeft'] is int
              ? json['daysLeft'] as int
              : int.tryParse('${json['daysLeft']}'),
    );
  }

  Map<String, dynamic> toJson() => {
        'id': id,
        'name': name,
        'amount': amount,
        'dueDay': dueDay,
        'notes': notes,
      };
}
