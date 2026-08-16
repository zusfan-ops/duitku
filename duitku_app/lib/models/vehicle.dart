class Vehicle {
  final int id;
  final String name;
  final String type;
  final String? licensePlate;
  final String? brand;
  final String? modelYear;
  final int odometer;
  final String? taxAnnualDate;
  final String? tax5yearDate;
  final String? photo;
  final double totalExpense;
  final int totalLogs;
  final String? lastOilDate;
  final int? nextOilKm;
  final String? lastServiceDate;
  final int? nextServiceKm;

  Vehicle({
    required this.id,
    required this.name,
    this.type = 'motor',
    this.licensePlate,
    this.brand,
    this.modelYear,
    this.odometer = 0,
    this.taxAnnualDate,
    this.tax5yearDate,
    this.photo,
    this.totalExpense = 0,
    this.totalLogs = 0,
    this.lastOilDate,
    this.nextOilKm,
    this.lastServiceDate,
    this.nextServiceKm,
  });

  factory Vehicle.fromJson(Map<String, dynamic> json) {
    return Vehicle(
      id: int.tryParse('${json['id']}') ?? 0,
      name: json['name']?.toString() ?? '',
      type: json['type']?.toString() ?? 'motor',
      licensePlate: json['license_plate']?.toString(),
      brand: json['brand']?.toString(),
      modelYear: json['model_year']?.toString(),
      odometer: int.tryParse('${json['odometer']}') ?? 0,
      taxAnnualDate: json['tax_annual_date']?.toString(),
      tax5yearDate: json['tax_5year_date']?.toString(),
      photo: json['photo']?.toString(),
      totalExpense: double.tryParse('${json['total_expense']}') ?? 0,
      totalLogs: int.tryParse('${json['total_logs']}') ?? 0,
      lastOilDate: json['last_oil_date']?.toString(),
      nextOilKm: int.tryParse('${json['next_oil_km']}'),
      lastServiceDate: json['last_service_date']?.toString(),
      nextServiceKm: int.tryParse('${json['next_service_km']}'),
    );
  }
}

class VehicleLog {
  final int id;
  final int vehicleId;
  final String type;
  final String title;
  final double cost;
  final int? km;
  final int? nextKm;
  final String? nextDate;
  final String date;
  final String? workshop;
  final String? notes;
  final String? vehicleName;
  final String? licensePlate;

  VehicleLog({
    required this.id,
    required this.vehicleId,
    required this.type,
    required this.title,
    this.cost = 0,
    this.km,
    this.nextKm,
    this.nextDate,
    required this.date,
    this.workshop,
    this.notes,
    this.vehicleName,
    this.licensePlate,
  });

  factory VehicleLog.fromJson(Map<String, dynamic> json) {
    return VehicleLog(
      id: int.tryParse('${json['id']}') ?? 0,
      vehicleId: int.tryParse('${json['vehicle_id']}') ?? 0,
      type: json['type']?.toString() ?? 'service_rutin',
      title: json['title']?.toString() ?? '',
      cost: double.tryParse('${json['cost']}') ?? 0,
      km: int.tryParse('${json['km']}'),
      nextKm: int.tryParse('${json['next_km']}'),
      nextDate: json['next_date']?.toString(),
      date: json['date']?.toString() ?? '',
      workshop: json['workshop']?.toString(),
      notes: json['notes']?.toString(),
      vehicleName: json['vehicle_name']?.toString(),
      licensePlate: json['license_plate']?.toString(),
    );
  }
}
