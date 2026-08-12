class Debt {
  final int id;
  final String type; // hutang | piutang
  final String person;
  final double amount;
  final double paid;
  final String? description;
  final String? dueDate;
  final String status; // active | settled
  final bool isPast;

  Debt({
    required this.id,
    required this.type,
    required this.person,
    required this.amount,
    this.paid = 0,
    this.description,
    this.dueDate,
    this.status = 'active',
    this.isPast = false,
  });

  factory Debt.fromJson(Map<String, dynamic> json) {
    return Debt(
      id: json['id'] is int ? json['id'] as int : int.tryParse('${json['id']}') ?? 0,
      type: json['type']?.toString() ?? 'hutang',
      person: json['person']?.toString() ?? '',
      amount: double.tryParse('${json['amount']}') ?? 0,
      paid: double.tryParse('${json['paid']}') ?? 0,
      description: json['description']?.toString(),
      dueDate: json['due_date']?.toString(),
      status: json['status']?.toString() ?? 'active',
      isPast: (json['is_past']?.toString() ?? '0') == '1',
    );
  }

  double get remaining => amount - paid;
}

class DebtSummary {
  final double totalHutang;
  final double totalPiutang;
  final int activeCount;

  const DebtSummary({
    this.totalHutang = 0,
    this.totalPiutang = 0,
    this.activeCount = 0,
  });

  factory DebtSummary.fromJson(Map<String, dynamic> json) {
    return DebtSummary(
      totalHutang: double.tryParse('${json['total_hutang']}') ?? 0,
      totalPiutang: double.tryParse('${json['total_piutang']}') ?? 0,
      activeCount: json['active_count'] is int
          ? json['active_count'] as int
          : int.tryParse('${json['active_count']}') ?? 0,
    );
  }
}
