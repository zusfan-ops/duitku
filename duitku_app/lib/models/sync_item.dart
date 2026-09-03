class SyncItem {
  final String id;
  final String type; // transaction_store, transaction_update, transaction_delete, wallet_transfer, wallet_store, debt_store, debt_pay, debt_settle, bill_store, pos_order_store
  final Map<String, dynamic> payload;
  final DateTime createdAt;
  int retries;
  String status; // pending, syncing, failed
  String? error;

  SyncItem({
    required this.id,
    required this.type,
    required this.payload,
    required this.createdAt,
    this.retries = 0,
    this.status = 'pending',
    this.error,
  });

  Map<String, dynamic> toJson() => {
        'id': id,
        'type': type,
        'payload': payload,
        'created_at': createdAt.toIso8601String(),
        'retries': retries,
        'status': status,
        'error': error,
      };

  factory SyncItem.fromJson(Map<String, dynamic> json) {
    return SyncItem(
      id: json['id']?.toString() ?? '',
      type: json['type']?.toString() ?? '',
      payload: json['payload'] is Map<String, dynamic>
          ? json['payload'] as Map<String, dynamic>
          : Map<String, dynamic>.from(json['payload'] as Map? ?? {}),
      createdAt: json['created_at'] != null
          ? DateTime.tryParse(json['created_at'].toString()) ?? DateTime.now()
          : DateTime.now(),
      retries: json['retries'] is int ? json['retries'] as int : 0,
      status: json['status']?.toString() ?? 'pending',
      error: json['error']?.toString(),
    );
  }
}
