class Transaction {
  final int id;
  final int? walletId;
  final int? categoryId;
  final String type; // income | expense
  final double amount;
  final String? note;
  final String date;
  final String? image;
  final String? categoryName;
  final String? categoryIcon;
  final String? categoryColor;
  final String? walletName;
  final bool isRecurring;

  Transaction({
    required this.id,
    this.walletId,
    this.categoryId,
    required this.type,
    required this.amount,
    this.note,
    required this.date,
    this.image,
    this.categoryName,
    this.categoryIcon,
    this.categoryColor,
    this.walletName,
    this.isRecurring = false,
  });

  factory Transaction.fromJson(Map<String, dynamic> json) {
    final noteStr = json['note']?.toString() ?? '';
    final isRec = json['is_recurring'] == 1 ||
        json['is_recurring'] == true ||
        json['is_recurring'] == '1' ||
        json['recurring_id'] != null ||
        noteStr.toLowerCase().contains('(otomatis)') ||
        noteStr.toLowerCase().contains('pembayaran rutin') ||
        noteStr.toLowerCase().contains('(berulang)') ||
        noteStr.toLowerCase().contains('rutin');

    return Transaction(
      id: json['id'] is int ? json['id'] as int : int.tryParse('${json['id']}') ?? 0,
      walletId: json['wallet_id'] == null ? null : int.tryParse('${json['wallet_id']}'),
      categoryId: json['category_id'] == null ? null : int.tryParse('${json['category_id']}'),
      type: json['type']?.toString() ?? 'expense',
      amount: double.tryParse('${json['amount']}') ?? 0,
      note: json['note']?.toString(),
      date: json['date']?.toString() ?? '',
      image: json['image']?.toString(),
      categoryName: json['category_name']?.toString(),
      categoryIcon: json['category_icon']?.toString(),
      categoryColor: json['category_color']?.toString(),
      walletName: json['wallet_name']?.toString(),
      isRecurring: isRec,
    );
  }
}
