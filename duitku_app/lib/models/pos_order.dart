class PosOrderItem {
  final int id;
  final int orderId;
  final int? productId;
  final String productName;
  final int qty;
  final double price;
  final double costPrice;
  final double subtotal;

  const PosOrderItem({
    this.id = 0,
    this.orderId = 0,
    this.productId,
    required this.productName,
    this.qty = 1,
    this.price = 0,
    this.costPrice = 0,
    this.subtotal = 0,
  });

  factory PosOrderItem.fromJson(Map<String, dynamic> json) {
    return PosOrderItem(
      id: (json['id'] as num?)?.toInt() ?? 0,
      orderId: (json['order_id'] as num?)?.toInt() ?? 0,
      productId: (json['product_id'] as num?)?.toInt(),
      productName: json['product_name']?.toString() ?? '',
      qty: (json['qty'] as num?)?.toInt() ?? 1,
      price: double.tryParse('${json['price']}') ?? 0,
      costPrice: double.tryParse('${json['cost_price']}') ?? 0,
      subtotal: double.tryParse('${json['subtotal']}') ?? 0,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'product_id': productId,
      'product_name': productName,
      'qty': qty,
      'price': price,
      'cost_price': costPrice,
      'subtotal': subtotal,
    };
  }
}

class PosOrder {
  final int id;
  final String orderNumber;
  final double totalAmount;
  final double totalCost;
  final double profit;
  final String paymentMethod;
  final int? walletId;
  final double cashReceived;
  final double changeAmount;
  final String? customerName;
  final String? customerPhone;
  final int? debtId;
  final int? transactionId;
  final String? notes;
  final String date;
  final String? createdAt;
  final List<PosOrderItem> items;

  const PosOrder({
    this.id = 0,
    required this.orderNumber,
    this.totalAmount = 0,
    this.totalCost = 0,
    this.profit = 0,
    this.paymentMethod = 'cash',
    this.walletId,
    this.cashReceived = 0,
    this.changeAmount = 0,
    this.customerName,
    this.customerPhone,
    this.debtId,
    this.transactionId,
    this.notes,
    required this.date,
    this.createdAt,
    this.items = const [],
  });

  factory PosOrder.fromJson(Map<String, dynamic> json) {
    final rawItems = json['items'] as List<dynamic>? ?? [];
    return PosOrder(
      id: (json['id'] as num?)?.toInt() ?? 0,
      orderNumber: json['order_number']?.toString() ?? '',
      totalAmount: double.tryParse('${json['total_amount']}') ?? 0,
      totalCost: double.tryParse('${json['total_cost']}') ?? 0,
      profit: double.tryParse('${json['profit']}') ?? 0,
      paymentMethod: json['payment_method']?.toString() ?? 'cash',
      walletId: (json['wallet_id'] as num?)?.toInt(),
      cashReceived: double.tryParse('${json['cash_received']}') ?? 0,
      changeAmount: double.tryParse('${json['change_amount']}') ?? 0,
      customerName: json['customer_name']?.toString(),
      customerPhone: json['customer_phone']?.toString(),
      debtId: (json['debt_id'] as num?)?.toInt(),
      transactionId: (json['transaction_id'] as num?)?.toInt(),
      notes: json['notes']?.toString(),
      date: json['date']?.toString() ?? '',
      createdAt: json['created_at']?.toString(),
      items: rawItems.map((e) => PosOrderItem.fromJson(e as Map<String, dynamic>)).toList(),
    );
  }
}
