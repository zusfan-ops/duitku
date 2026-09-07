class SubscriptionSummary {
  final double totalMonthly;
  final double totalYearly;
  final int activeCount;
  final int pausedCount;
  final int cancelledCount;
  final int wasteCount;
  final double wasteMonthly;
  final int totalCount;

  const SubscriptionSummary({
    this.totalMonthly = 0.0,
    this.totalYearly = 0.0,
    this.activeCount = 0,
    this.pausedCount = 0,
    this.cancelledCount = 0,
    this.wasteCount = 0,
    this.wasteMonthly = 0.0,
    this.totalCount = 0,
  });

  factory SubscriptionSummary.fromJson(Map<String, dynamic>? json) {
    if (json == null) return SubscriptionSummary();
    return SubscriptionSummary(
      totalMonthly: double.tryParse(json['total_monthly']?.toString() ?? '0') ?? 0.0,
      totalYearly: double.tryParse(json['total_yearly']?.toString() ?? '0') ?? 0.0,
      activeCount: int.tryParse(json['active_count']?.toString() ?? '0') ?? 0,
      pausedCount: int.tryParse(json['paused_count']?.toString() ?? '0') ?? 0,
      cancelledCount: int.tryParse(json['cancelled_count']?.toString() ?? '0') ?? 0,
      wasteCount: int.tryParse(json['waste_count']?.toString() ?? '0') ?? 0,
      wasteMonthly: double.tryParse(json['waste_monthly']?.toString() ?? '0') ?? 0.0,
      totalCount: int.tryParse(json['total_count']?.toString() ?? '0') ?? 0,
    );
  }
}

class Subscription {
  final int id;
  final String name;
  final String category;
  final double amount;
  final String currency;
  final String billingCycle;
  final String startDate;
  final String nextBillingDate;
  final String status;
  final String? paymentMethod;
  final String? providerUrl;
  final String? notes;
  final String? icon;
  final String? color;
  final String? categoryName;
  final String? categoryIcon;
  final String? walletName;
  final String? lastUsedAt;
  final int notifyBeforeDays;
  final bool isWaste;
  final String? wasteReason;

  Subscription({
    this.id = 0,
    this.name = '',
    this.category = 'Hiburan',
    this.amount = 0.0,
    this.currency = 'IDR',
    this.billingCycle = 'monthly',
    this.startDate = '',
    this.nextBillingDate = '',
    this.status = 'active',
    this.paymentMethod,
    this.providerUrl,
    this.notes,
    this.icon,
    this.color,
    this.categoryName,
    this.categoryIcon,
    this.walletName,
    this.lastUsedAt,
    this.notifyBeforeDays = 3,
    this.isWaste = false,
    this.wasteReason,
  });

  bool get isActive => status == 'active';

  factory Subscription.fromJson(Map<String, dynamic> json) {
    return Subscription(
      id: int.tryParse(json['id']?.toString() ?? '0') ?? 0,
      name: json['name']?.toString() ?? '',
      category: json['category']?.toString() ?? 'Hiburan',
      amount: double.tryParse(json['amount']?.toString() ?? '0') ?? 0.0,
      currency: json['currency']?.toString() ?? 'IDR',
      billingCycle: json['billing_cycle']?.toString() ?? 'monthly',
      startDate: json['start_date']?.toString() ?? '',
      nextBillingDate: json['next_billing_date']?.toString() ?? '',
      status: json['status']?.toString() ?? 'active',
      paymentMethod: json['payment_method']?.toString(),
      providerUrl: json['provider_url']?.toString(),
      notes: json['notes']?.toString(),
      icon: json['icon']?.toString(),
      color: json['color']?.toString(),
      categoryName: json['category_name']?.toString(),
      categoryIcon: json['category_icon']?.toString(),
      walletName: json['wallet_name']?.toString(),
      lastUsedAt: json['last_used_at']?.toString(),
      notifyBeforeDays: int.tryParse(json['notify_before_days']?.toString() ?? '3') ?? 3,
      isWaste: json['is_waste'] == 1 || json['is_waste'] == '1' || json['is_waste'] == true,
      wasteReason: json['waste_reason']?.toString(),
    );
  }
}

class SubscriptionData {
  final List<Subscription> subscriptions;
  final SubscriptionSummary summary;
  final List<Subscription> upcoming;

  SubscriptionData({
    this.subscriptions = const [],
    this.summary = const SubscriptionSummary(),
    this.upcoming = const [],
  });

  factory SubscriptionData.fromJson(Map<String, dynamic> json) {
    return SubscriptionData(
      subscriptions: (json['subscriptions'] as List? ?? [])
          .map((e) => Subscription.fromJson(e as Map<String, dynamic>))
          .toList(),
      summary: SubscriptionSummary.fromJson(json['summary'] as Map<String, dynamic>?),
      upcoming: (json['upcoming'] as List? ?? [])
          .map((e) => Subscription.fromJson(e as Map<String, dynamic>))
          .toList(),
    );
  }
}