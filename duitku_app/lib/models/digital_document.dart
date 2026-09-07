class DigitalDocument {
  final int id;
  final String name;
  final String documentType;
  final String category;
  final String? documentNumber;
  final String? issuedDate;
  final String? expiryDate;
  final String? issuingAuthority;
  final String? ownerName;
  final String? notes;
  final String? photoPath;
  final String? photoBackPath;
  final String? storageLocation;
  final String? lastReminderAt;
  final int notifyBeforeDays;
  final String status;
  final bool isFavorite;
  final bool isExpired;
  final bool isExpiringSoon;

  DigitalDocument({
    this.id = 0,
    this.name = '',
    this.documentType = 'Lainnya',
    this.category = 'Identitas',
    this.documentNumber,
    this.issuedDate,
    this.expiryDate,
    this.issuingAuthority,
    this.ownerName,
    this.notes,
    this.photoPath,
    this.photoBackPath,
    this.storageLocation,
    this.lastReminderAt,
    this.notifyBeforeDays = 30,
    this.status = 'active',
    this.isFavorite = false,
    this.isExpired = false,
    this.isExpiringSoon = false,
  });

  factory DigitalDocument.fromJson(Map<String, dynamic> json) {
    return DigitalDocument(
      id: int.tryParse(json['id']?.toString() ?? '0') ?? 0,
      name: json['name']?.toString() ?? '',
      documentType: json['document_type']?.toString() ?? 'Lainnya',
      category: json['category']?.toString() ?? 'Identitas',
      documentNumber: json['document_number']?.toString(),
      issuedDate: json['issued_date']?.toString(),
      expiryDate: json['expiry_date']?.toString(),
      issuingAuthority: json['issuing_authority']?.toString(),
      ownerName: json['owner_name']?.toString(),
      notes: json['notes']?.toString(),
      photoPath: json['photo_path']?.toString(),
      photoBackPath: json['photo_back_path']?.toString(),
      storageLocation: json['storage_location']?.toString(),
      lastReminderAt: json['last_reminder_at']?.toString(),
      notifyBeforeDays: int.tryParse(json['notify_before_days']?.toString() ?? '30') ?? 30,
      status: json['status']?.toString() ?? 'active',
      isFavorite: json['is_favorite'] == 1 || json['is_favorite'] == '1' || json['is_favorite'] == true,
      isExpired: json['is_expired'] == true || json['is_expired'] == 1 || json['is_expired'] == '1',
      isExpiringSoon: json['is_expiring_soon'] == true || json['is_expiring_soon'] == 1 || json['is_expiring_soon'] == '1',
    );
  }
}

class DocumentSummary {
  final int totalDocs;
  final int expired;
  final int favorites;
  final Map<String, int> categories;
  final Map<String, int> statusBreakdown;

  const DocumentSummary({
    this.totalDocs = 0,
    this.expired = 0,
    this.favorites = 0,
    this.categories = const {},
    this.statusBreakdown = const {},
  });

  factory DocumentSummary.fromJson(Map<String, dynamic>? json) {
    if (json == null) return DocumentSummary();
    Map<String, int> toIntMap(dynamic raw) {
      if (raw is! Map) return const {};
      return raw.map((k, v) => MapEntry(k.toString(), int.tryParse(v?.toString() ?? '0') ?? 0));
    }

    return DocumentSummary(
      totalDocs: int.tryParse(json['total_docs']?.toString() ?? '0') ?? 0,
      expired: int.tryParse(json['expired']?.toString() ?? '0') ?? 0,
      favorites: int.tryParse(json['favorites']?.toString() ?? '0') ?? 0,
      categories: toIntMap(json['categories']),
      statusBreakdown: toIntMap(json['status_breakdown']),
    );
  }
}

class DocumentData {
  final List<DigitalDocument> documents;
  final DocumentSummary summary;
  final List<DigitalDocument> expiring;

  DocumentData({
    this.documents = const [],
    this.summary = const DocumentSummary(),
    this.expiring = const [],
  });

  factory DocumentData.fromJson(Map<String, dynamic> json) {
    return DocumentData(
      documents: (json['documents'] as List? ?? [])
          .map((e) => DigitalDocument.fromJson(e as Map<String, dynamic>))
          .toList(),
      summary: DocumentSummary.fromJson(json['summary'] as Map<String, dynamic>?),
      expiring: (json['expiring'] as List? ?? [])
          .map((e) => DigitalDocument.fromJson(e as Map<String, dynamic>))
          .toList(),
    );
  }
}