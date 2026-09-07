class ArisanProgress {
  final int currentRound;
  final int totalMembers;
  final double percent;

  ArisanProgress({
    this.currentRound = 0,
    this.totalMembers = 0,
    this.percent = 0,
  });

  factory ArisanProgress.fromJson(Map<String, dynamic>? json) {
    if (json == null) return ArisanProgress();
    return ArisanProgress(
      currentRound: int.tryParse(json['current_round']?.toString() ?? '0') ?? 0,
      totalMembers: int.tryParse(json['total_members']?.toString() ?? '0') ?? 0,
      percent: double.tryParse(json['percent']?.toString() ?? '0') ?? 0.0,
    );
  }
}

class ArisanMember {
  final int id;
  final int? userId;
  final String memberName;
  final String? phone;
  final int rotationOrder;
  final bool hasReceived;
  final String? receivedAt;
  final String? userDisplayName;
  final String? avatar;

  ArisanMember({
    this.id = 0,
    this.userId,
    this.memberName = '',
    this.phone,
    this.rotationOrder = 1,
    this.hasReceived = false,
    this.receivedAt,
    this.userDisplayName,
    this.avatar,
  });

  factory ArisanMember.fromJson(Map<String, dynamic> json) {
    return ArisanMember(
      id: int.tryParse(json['id']?.toString() ?? '0') ?? 0,
      userId: json['user_id'] != null ? int.tryParse(json['user_id'].toString()) : null,
      memberName: json['member_name']?.toString() ?? json['user_display_name']?.toString() ?? 'Anggota',
      phone: json['phone']?.toString(),
      rotationOrder: int.tryParse(json['rotation_order']?.toString() ?? '1') ?? 1,
      hasReceived: json['has_received'] == 1 || json['has_received'] == '1' || json['has_received'] == true,
      receivedAt: json['received_at']?.toString(),
      userDisplayName: json['user_display_name']?.toString(),
      avatar: json['avatar']?.toString(),
    );
  }
}

class ArisanPayment {
  final int id;
  final int memberId;
  final int roundNumber;
  final double amount;
  final String status;
  final String? paidAt;
  final String? memberName;
  final int? rotationOrder;

  ArisanPayment({
    this.id = 0,
    this.memberId = 0,
    this.roundNumber = 1,
    this.amount = 0.0,
    this.status = 'unpaid',
    this.paidAt,
    this.memberName,
    this.rotationOrder,
  });

  bool get isPaid => status == 'paid';

  factory ArisanPayment.fromJson(Map<String, dynamic> json) {
    return ArisanPayment(
      id: int.tryParse(json['id']?.toString() ?? '0') ?? 0,
      memberId: int.tryParse(json['member_id']?.toString() ?? '0') ?? 0,
      roundNumber: int.tryParse(json['round_number']?.toString() ?? '1') ?? 1,
      amount: double.tryParse(json['amount']?.toString() ?? '0') ?? 0.0,
      status: json['status']?.toString() ?? 'unpaid',
      paidAt: json['paid_at']?.toString(),
      memberName: json['member_name']?.toString(),
      rotationOrder: json['rotation_order'] != null ? int.tryParse(json['rotation_order'].toString()) : null,
    );
  }
}

class ArisanGroup {
  final int id;
  final int? neighborhoodId;
  final String? neighborhoodName;
  final int userId;
  final String name;
  final double amount;
  final String frequency;
  final int totalMembers;
  final int currentRound;
  final String status;
  final String startDate;
  final String description;
  final ArisanProgress progress;
  final bool canAccess;
  final bool isOwner;
  final List<ArisanMember> members;
  final List<ArisanPayment> payments;

  ArisanGroup({
    this.id = 0,
    this.neighborhoodId,
    this.neighborhoodName,
    this.userId = 0,
    this.name = '',
    this.amount = 0.0,
    this.frequency = 'monthly',
    this.totalMembers = 0,
    this.currentRound = 0,
    this.status = 'active',
    this.startDate = '',
    this.description = '',
    ArisanProgress? progress,
    this.canAccess = false,
    this.isOwner = false,
    this.members = const [],
    this.payments = const [],
  }) : progress = progress ??
            ArisanProgress(
              currentRound: currentRound,
              totalMembers: totalMembers,
              percent: totalMembers > 0
                  ? ((currentRound / totalMembers) * 100).clamp(0, 100).toDouble()
                  : 0,
            );

  factory ArisanGroup.fromJson(Map<String, dynamic> json) {
    final currentRound = int.tryParse(json['current_round']?.toString() ?? '0') ?? 0;
    final totalMembers = int.tryParse(json['total_members']?.toString() ?? '0') ?? 0;
    final rawProgress = json['progress'] as Map<String, dynamic>?;
    final progress = rawProgress == null
        ? ArisanProgress(
            currentRound: currentRound,
            totalMembers: totalMembers,
            percent: totalMembers > 0
                ? ((currentRound / totalMembers) * 100).clamp(0, 100).toDouble()
                : 0,
          )
        : ArisanProgress.fromJson(rawProgress);
    return ArisanGroup(
      id: int.tryParse(json['id']?.toString() ?? '0') ?? 0,
      neighborhoodId: json['neighborhood_id'] != null ? int.tryParse(json['neighborhood_id'].toString()) : null,
      neighborhoodName: json['neighborhood_name']?.toString(),
      userId: int.tryParse(json['user_id']?.toString() ?? '0') ?? 0,
      name: json['name']?.toString() ?? '',
      amount: double.tryParse(json['amount']?.toString() ?? '0') ?? 0.0,
      frequency: json['frequency']?.toString() ?? 'monthly',
      totalMembers: totalMembers,
      currentRound: currentRound,
      status: json['status']?.toString() ?? 'active',
      startDate: json['start_date']?.toString() ?? '',
      description: json['description']?.toString() ?? '',
      progress: progress,
      canAccess: json['can_access'] == true || json['can_access'] == 1 || json['can_access'] == '1',
      isOwner: json['is_owner'] == true || json['is_owner'] == 1 || json['is_owner'] == '1',
      members: (json['members'] as List? ?? [])
          .map((e) => ArisanMember.fromJson(e as Map<String, dynamic>))
          .toList(),
      payments: (json['payments'] as List? ?? [])
          .map((e) => ArisanPayment.fromJson(e as Map<String, dynamic>))
          .toList(),
    );
  }
}