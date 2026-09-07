class IuranConfig {
  final int id;
  final int neighborhoodId;
  final String periodType;
  final double amount;
  final String description;
  final bool isActive;

  IuranConfig({
    this.id = 0,
    this.neighborhoodId = 0,
    this.periodType = 'monthly',
    this.amount = 0,
    this.description = 'Iuran Warga Bulanan',
    this.isActive = true,
  });

  factory IuranConfig.fromJson(Map<String, dynamic> json) {
    return IuranConfig(
      id: int.tryParse(json['id']?.toString() ?? '0') ?? 0,
      neighborhoodId: int.tryParse(json['neighborhood_id']?.toString() ?? '0') ?? 0,
      periodType: json['period_type']?.toString() ?? 'monthly',
      amount: double.tryParse(json['amount']?.toString() ?? '0') ?? 0.0,
      description: json['description']?.toString() ?? 'Iuran Warga Bulanan',
      isActive: json['is_active'] == 1 || json['is_active'] == '1' || json['is_active'] == true,
    );
  }
}

class IuranSummary {
  final int totalWarga;
  final double totalExpected;
  final double totalPaid;
  final int paidCount;
  final int partialCount;
  final int unpaidCount;
  final double totalArrears;

  IuranSummary({
    this.totalWarga = 0,
    this.totalExpected = 0.0,
    this.totalPaid = 0.0,
    this.paidCount = 0,
    this.partialCount = 0,
    this.unpaidCount = 0,
    this.totalArrears = 0.0,
  });

  factory IuranSummary.fromJson(Map<String, dynamic>? json) {
    if (json == null) return IuranSummary();
    return IuranSummary(
      totalWarga: int.tryParse(json['total_warga']?.toString() ?? '0') ?? 0,
      totalExpected: double.tryParse(json['total_expected']?.toString() ?? '0') ?? 0.0,
      totalPaid: double.tryParse(json['total_paid']?.toString() ?? '0') ?? 0.0,
      paidCount: int.tryParse(json['paid_count']?.toString() ?? '0') ?? 0,
      partialCount: int.tryParse(json['partial_count']?.toString() ?? '0') ?? 0,
      unpaidCount: int.tryParse(json['unpaid_count']?.toString() ?? '0') ?? 0,
      totalArrears: double.tryParse(json['total_arrears']?.toString() ?? '0') ?? 0.0,
    );
  }
}

class IuranPayment {
  final int id;
  final String residentName;
  final String? houseNumber;
  final String? avatar;
  final double amountExpected;
  final double amountPaid;
  final String status;
  final String? paidVia;
  final String? notes;

  IuranPayment({
    this.id = 0,
    this.residentName = 'Warga',
    this.houseNumber,
    this.avatar,
    this.amountExpected = 0.0,
    this.amountPaid = 0.0,
    this.status = 'unpaid',
    this.paidVia,
    this.notes,
  });

  bool get isPaid => status == 'paid';
  bool get isPartial => status == 'partial';

  factory IuranPayment.fromJson(Map<String, dynamic> json) {
    return IuranPayment(
      id: int.tryParse(json['id']?.toString() ?? '0') ?? 0,
      residentName: json['resident_name']?.toString() ?? 'Warga',
      houseNumber: json['house_number']?.toString(),
      avatar: json['avatar']?.toString(),
      amountExpected: double.tryParse(json['amount_expected']?.toString() ?? '0') ?? 0.0,
      amountPaid: double.tryParse(json['amount_paid']?.toString() ?? '0') ?? 0.0,
      status: json['status']?.toString() ?? 'unpaid',
      paidVia: json['paid_via']?.toString(),
      notes: json['notes']?.toString(),
    );
  }
}

class IuranHistoryItem {
  final int id;
  final String periodMonth;
  final double amountExpected;
  final double amountPaid;
  final String status;

  IuranHistoryItem({
    this.id = 0,
    this.periodMonth = '',
    this.amountExpected = 0.0,
    this.amountPaid = 0.0,
    this.status = 'unpaid',
  });

  factory IuranHistoryItem.fromJson(Map<String, dynamic> json) {
    return IuranHistoryItem(
      id: int.tryParse(json['id']?.toString() ?? '0') ?? 0,
      periodMonth: json['period_month']?.toString() ?? '',
      amountExpected: double.tryParse(json['amount_expected']?.toString() ?? '0') ?? 0.0,
      amountPaid: double.tryParse(json['amount_paid']?.toString() ?? '0') ?? 0.0,
      status: json['status']?.toString() ?? 'unpaid',
    );
  }
}

class IuranResident {
  final int id;
  final String name;
  final String? houseNumber;

  IuranResident({this.id = 0, this.name = '', this.houseNumber});

  factory IuranResident.fromJson(Map<String, dynamic> json) {
    return IuranResident(
      id: int.tryParse(json['id']?.toString() ?? '0') ?? 0,
      name: json['name']?.toString() ?? 'Warga',
      houseNumber: json['house_number']?.toString(),
    );
  }
}

class IuranData {
  final bool joined;
  final bool canManage;
  final IuranConfig? config;
  final String periodMonth;
  final IuranSummary? summary;
  final List<IuranPayment> payments;
  final List<IuranResident> residents;
  final List<IuranHistoryItem> myHistory;

  IuranData({
    this.joined = false,
    this.canManage = false,
    this.config,
    this.periodMonth = '',
    this.summary,
    this.payments = const [],
    this.residents = const [],
    this.myHistory = const [],
  });

  factory IuranData.fromJson(Map<String, dynamic> json) {
    return IuranData(
      joined: json['joined'] == true || json['joined'] == 1 || json['joined'] == '1',
      canManage: json['can_manage'] == true || json['can_manage'] == 1 || json['can_manage'] == '1',
      config: json['config'] != null ? IuranConfig.fromJson(json['config'] as Map<String, dynamic>) : null,
      periodMonth: json['period_month']?.toString() ?? '',
      summary: IuranSummary.fromJson(json['summary'] as Map<String, dynamic>?),
      payments: (json['payments'] as List? ?? [])
          .map((e) => IuranPayment.fromJson(e as Map<String, dynamic>))
          .toList(),
      residents: (json['residents'] as List? ?? [])
          .map((e) => IuranResident.fromJson(e as Map<String, dynamic>))
          .toList(),
      myHistory: (json['my_history'] as List? ?? [])
          .map((e) => IuranHistoryItem.fromJson(e as Map<String, dynamic>))
          .toList(),
    );
  }
}