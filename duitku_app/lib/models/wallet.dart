class Wallet {
  final int id;
  final String name;
  final String type; // bank | e-wallet | cash | savings_home
  final String icon;
  final String color;
  final double initialBalance;
  final bool isDefault;
  final double balance;

  Wallet({
    required this.id,
    required this.name,
    required this.type,
    required this.icon,
    required this.color,
    this.initialBalance = 0,
    this.isDefault = false,
    this.balance = 0,
  });

  factory Wallet.fromJson(Map<String, dynamic> json) {
    return Wallet(
      id: json['id'] is int ? json['id'] as int : int.tryParse('${json['id']}') ?? 0,
      name: json['name']?.toString() ?? '',
      type: json['type']?.toString() ?? 'cash',
      icon: json['icon']?.toString() ?? '💵',
      color: json['color']?.toString() ?? '#0AA956',
      initialBalance: double.tryParse('${json['initial_balance']}') ?? 0,
      isDefault: (json['is_default']?.toString() ?? '0') == '1',
      balance: double.tryParse('${json['balance']}') ?? 0,
    );
  }

  String get typeLabel {
    switch (type) {
      case 'bank':
        return 'Bank';
      case 'e-wallet':
        return 'E-Wallet';
      case 'savings_home':
        return 'Tabungan';
      default:
        return 'Tunai';
    }
  }
}
