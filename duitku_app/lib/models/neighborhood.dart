class Neighborhood {
  final int id;
  final String name;
  final String province;
  final String city;
  final String district;
  final String subdistrict;
  final String rw;
  final String rt;
  final String uniqueCode;
  final int? adminUserId;
  final String? adminName;
  final String? adminPhone;
  final int totalVerifiedResidents;
  final int totalPendingResidents;
  final int totalCommunityTools;
  final int activeErrandsCount;

  Neighborhood({
    required this.id,
    required this.name,
    required this.province,
    required this.city,
    required this.district,
    required this.subdistrict,
    required this.rw,
    required this.rt,
    required this.uniqueCode,
    this.adminUserId,
    this.adminName,
    this.adminPhone,
    this.totalVerifiedResidents = 0,
    this.totalPendingResidents = 0,
    this.totalCommunityTools = 0,
    this.activeErrandsCount = 0,
  });

  factory Neighborhood.fromJson(Map<String, dynamic> json) {
    return Neighborhood(
      id: int.tryParse(json['id'].toString()) ?? 0,
      name: json['name']?.toString() ?? '',
      province: json['province']?.toString() ?? '',
      city: json['city']?.toString() ?? '',
      district: json['district']?.toString() ?? '',
      subdistrict: json['subdistrict']?.toString() ?? '',
      rw: json['rw']?.toString() ?? '',
      rt: json['rt']?.toString() ?? '',
      uniqueCode: json['unique_code']?.toString() ?? '',
      adminUserId: json['admin_user_id'] != null ? int.tryParse(json['admin_user_id'].toString()) : null,
      adminName: json['admin_name']?.toString(),
      adminPhone: json['admin_phone']?.toString(),
      totalVerifiedResidents: int.tryParse(json['total_verified_residents']?.toString() ?? '0') ?? 0,
      totalPendingResidents: int.tryParse(json['total_pending_residents']?.toString() ?? '0') ?? 0,
      totalCommunityTools: int.tryParse(json['total_community_tools']?.toString() ?? '0') ?? 0,
      activeErrandsCount: int.tryParse(json['active_errands_count']?.toString() ?? '0') ?? 0,
    );
  }
}

class Resident {
  final int id;
  final String name;
  final String? username;
  final String? phone;
  final String? email;
  final String role;
  final String residenceStatus;
  final String verificationStatus;
  final String? houseNumber;

  Resident({
    required this.id,
    required this.name,
    this.username,
    this.phone,
    this.email,
    required this.role,
    required this.residenceStatus,
    required this.verificationStatus,
    this.houseNumber,
  });

  bool get isRtAdmin => role.toLowerCase() == 'rt_admin' || role.toLowerCase() == 'admin' || role.toLowerCase() == 'administrator';
  bool get isTreasurer => role.toLowerCase() == 'rt_treasurer' || role.toLowerCase() == 'bendahara';

  factory Resident.fromJson(Map<String, dynamic> json) {
    return Resident(
      id: int.tryParse(json['id'].toString()) ?? 0,
      name: json['name']?.toString() ?? '',
      username: json['username']?.toString(),
      phone: json['phone']?.toString(),
      email: json['email']?.toString(),
      role: json['role']?.toString() ?? 'user',
      residenceStatus: json['residence_status']?.toString() ?? 'permanent',
      verificationStatus: json['rt_verification_status']?.toString() ?? 'pending',
      houseNumber: json['house_number']?.toString(),
    );
  }
}

class NeighborhoodKasItem {
  final int id;
  final int neighborhoodId;
  final int createdBy;
  final String type; // 'in' or 'out'
  final String category;
  final double amount;
  final String date;
  final String? description;
  final String? recordedByName;

  NeighborhoodKasItem({
    required this.id,
    required this.neighborhoodId,
    required this.createdBy,
    required this.type,
    required this.category,
    required this.amount,
    required this.date,
    this.description,
    this.recordedByName,
  });

  factory NeighborhoodKasItem.fromJson(Map<String, dynamic> json) {
    return NeighborhoodKasItem(
      id: int.tryParse(json['id'].toString()) ?? 0,
      neighborhoodId: int.tryParse(json['neighborhood_id'].toString()) ?? 0,
      createdBy: int.tryParse(json['created_by'].toString()) ?? 0,
      type: json['type']?.toString() ?? 'in',
      category: json['category']?.toString() ?? 'Iuran Warga',
      amount: double.tryParse(json['amount'].toString()) ?? 0.0,
      date: json['date']?.toString() ?? '',
      description: json['description']?.toString(),
      recordedByName: json['recorded_by_name']?.toString(),
    );
  }
}

class NeighborhoodKasSummary {
  final double totalIn;
  final double totalOut;
  final double balance;
  final int countIn;
  final int countOut;

  NeighborhoodKasSummary({
    this.totalIn = 0.0,
    this.totalOut = 0.0,
    this.balance = 0.0,
    this.countIn = 0,
    this.countOut = 0,
  });

  factory NeighborhoodKasSummary.fromJson(Map<String, dynamic>? json) {
    if (json == null) return NeighborhoodKasSummary();
    return NeighborhoodKasSummary(
      totalIn: double.tryParse(json['total_in']?.toString() ?? '0') ?? 0.0,
      totalOut: double.tryParse(json['total_out']?.toString() ?? '0') ?? 0.0,
      balance: double.tryParse(json['balance']?.toString() ?? '0') ?? 0.0,
      countIn: int.tryParse(json['count_in']?.toString() ?? '0') ?? 0,
      countOut: int.tryParse(json['count_out']?.toString() ?? '0') ?? 0,
    );
  }
}

class NeighborhoodActivity {
  final int id;
  final int neighborhoodId;
  final String title;
  final String category;
  final String eventDate;
  final String eventTime;
  final String location;
  final String? description;
  final String? creatorName;

  NeighborhoodActivity({
    required this.id,
    required this.neighborhoodId,
    required this.title,
    required this.category,
    required this.eventDate,
    required this.eventTime,
    required this.location,
    this.description,
    this.creatorName,
  });

  factory NeighborhoodActivity.fromJson(Map<String, dynamic> json) {
    return NeighborhoodActivity(
      id: int.tryParse(json['id'].toString()) ?? 0,
      neighborhoodId: int.tryParse(json['neighborhood_id'].toString()) ?? 0,
      title: json['title']?.toString() ?? '',
      category: json['category']?.toString() ?? 'Kerja Bakti',
      eventDate: json['event_date']?.toString() ?? '',
      eventTime: json['event_time']?.toString() ?? '08:00',
      location: json['location']?.toString() ?? 'Lingkungan RT',
      description: json['description']?.toString(),
      creatorName: json['creator_name']?.toString(),
    );
  }
}

class CommunityTool {
  final int id;
  final int neighborhoodId;
  final int? ownerUserId;
  final String? ownerName;
  final String name;
  final String category;
  final String? description;
  final String? photo;
  final String status;
  final double rentalFee;
  final double depositAmount;
  final int maxRentDays;
  final String conditionNote;

  CommunityTool({
    required this.id,
    required this.neighborhoodId,
    this.ownerUserId,
    this.ownerName,
    required this.name,
    required this.category,
    this.description,
    this.photo,
    required this.status,
    required this.rentalFee,
    required this.depositAmount,
    required this.maxRentDays,
    required this.conditionNote,
  });

  factory CommunityTool.fromJson(Map<String, dynamic> json) {
    return CommunityTool(
      id: int.tryParse(json['id'].toString()) ?? 0,
      neighborhoodId: int.tryParse(json['neighborhood_id'].toString()) ?? 0,
      ownerUserId: json['owner_user_id'] != null ? int.tryParse(json['owner_user_id'].toString()) : null,
      ownerName: json['owner_name']?.toString(),
      name: json['name']?.toString() ?? '',
      category: json['category']?.toString() ?? 'Pertukangan',
      description: json['description']?.toString(),
      photo: json['photo']?.toString(),
      status: json['status']?.toString() ?? 'available',
      rentalFee: double.tryParse(json['rental_fee']?.toString() ?? '0') ?? 0.0,
      depositAmount: double.tryParse(json['deposit_amount']?.toString() ?? '0') ?? 0.0,
      maxRentDays: int.tryParse(json['max_rent_days']?.toString() ?? '3') ?? 3,
      conditionNote: json['condition_note']?.toString() ?? 'Baik',
    );
  }
}

class ToolRental {
  final int id;
  final int neighborhoodId;
  final int toolId;
  final String toolName;
  final int borrowerUserId;
  final String borrowerName;
  final String status;
  final double rentalFee;
  final double rtFeeAmount;
  final double depositAmount;
  final String startDate;
  final String dueDate;
  final String? actualReturnDate;
  final String? handoverToken;
  final String? returnToken;

  ToolRental({
    required this.id,
    required this.neighborhoodId,
    required this.toolId,
    required this.toolName,
    required this.borrowerUserId,
    required this.borrowerName,
    required this.status,
    required this.rentalFee,
    this.rtFeeAmount = 0.0,
    required this.depositAmount,
    required this.startDate,
    required this.dueDate,
    this.actualReturnDate,
    this.handoverToken,
    this.returnToken,
  });

  factory ToolRental.fromJson(Map<String, dynamic> json) {
    return ToolRental(
      id: int.tryParse(json['id'].toString()) ?? 0,
      neighborhoodId: int.tryParse(json['neighborhood_id'].toString()) ?? 0,
      toolId: int.tryParse(json['tool_id'].toString()) ?? 0,
      toolName: json['tool_name']?.toString() ?? 'Alat',
      borrowerUserId: int.tryParse(json['borrower_user_id'].toString()) ?? 0,
      borrowerName: json['borrower_name']?.toString() ?? 'Warga',
      status: json['status']?.toString() ?? 'requested',
      rentalFee: double.tryParse(json['rental_fee']?.toString() ?? '0') ?? 0.0,
      rtFeeAmount: double.tryParse(json['rt_fee_amount']?.toString() ?? '0') ?? 0.0,
      depositAmount: double.tryParse(json['deposit_amount']?.toString() ?? '0') ?? 0.0,
      startDate: json['start_date']?.toString() ?? '',
      dueDate: json['due_date']?.toString() ?? '',
      actualReturnDate: json['actual_return_date']?.toString(),
      handoverToken: json['handover_token']?.toString(),
      returnToken: json['return_token']?.toString(),
    );
  }
}

class Errand {
  final int id;
  final int organizerUserId;
  final String organizerName;
  final String? organizerHouse;
  final String destinationStore;
  final String? description;
  final String cutoffTime;
  final String status;
  final int totalItems;

  Errand({
    required this.id,
    required this.organizerUserId,
    required this.organizerName,
    this.organizerHouse,
    required this.destinationStore,
    this.description,
    required this.cutoffTime,
    required this.status,
    this.totalItems = 0,
  });

  factory Errand.fromJson(Map<String, dynamic> json) {
    return Errand(
      id: int.tryParse(json['id'].toString()) ?? 0,
      organizerUserId: int.tryParse(json['organizer_user_id'].toString()) ?? 0,
      organizerName: json['organizer_name']?.toString() ?? 'Warga',
      organizerHouse: json['organizer_house']?.toString(),
      destinationStore: json['destination_store']?.toString() ?? '',
      description: json['description']?.toString(),
      cutoffTime: json['cutoff_time']?.toString() ?? '',
      status: json['status']?.toString() ?? 'open',
      totalItems: int.tryParse(json['total_items']?.toString() ?? '0') ?? 0,
    );
  }
}

class ErrandItem {
  final int id;
  final int errandId;
  final int requesterUserId;
  final String? requesterName;
  final String itemName;
  final double quantity;
  final String unit;
  final double estimatedPrice;
  final double actualPrice;
  final double serviceFee;
  final String status;
  final String handoverToken;
  final String? notes;

  ErrandItem({
    required this.id,
    required this.errandId,
    required this.requesterUserId,
    this.requesterName,
    required this.itemName,
    required this.quantity,
    required this.unit,
    required this.estimatedPrice,
    required this.actualPrice,
    required this.serviceFee,
    required this.status,
    required this.handoverToken,
    this.notes,
  });

  factory ErrandItem.fromJson(Map<String, dynamic> json) {
    return ErrandItem(
      id: int.tryParse(json['id'].toString()) ?? 0,
      errandId: int.tryParse(json['errand_id'].toString()) ?? 0,
      requesterUserId: int.tryParse(json['requester_user_id'].toString()) ?? 0,
      requesterName: json['requester_name']?.toString(),
      itemName: json['item_name']?.toString() ?? '',
      quantity: double.tryParse(json['quantity']?.toString() ?? '1') ?? 1.0,
      unit: json['unit']?.toString() ?? 'pcs',
      estimatedPrice: double.tryParse(json['estimated_price']?.toString() ?? '0') ?? 0.0,
      actualPrice: double.tryParse(json['actual_price']?.toString() ?? '0') ?? 0.0,
      serviceFee: double.tryParse(json['service_fee']?.toString() ?? '5000') ?? 5000.0,
      status: json['status']?.toString() ?? 'pending',
      handoverToken: json['handover_token']?.toString() ?? '',
      notes: json['notes']?.toString(),
    );
  }
}
