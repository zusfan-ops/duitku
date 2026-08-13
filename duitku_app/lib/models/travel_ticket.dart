class TravelTicket {
  final String id;
  final String tripId;
  final String type; // flight | train | bus | ship | other
  final String? code;
  final String? qrData;
  final String? passengerName;
  final String? departure;
  final String? arrival;
  final String? departureTime;
  final String? seat;
  final String? notes;

  TravelTicket({
    required this.id,
    required this.tripId,
    this.type = 'other',
    this.code,
    this.qrData,
    this.passengerName,
    this.departure,
    this.arrival,
    this.departureTime,
    this.seat,
    this.notes,
  });

  factory TravelTicket.fromJson(Map<String, dynamic> json) {
    return TravelTicket(
      id: json['id']?.toString() ?? '',
      tripId: json['trip_id']?.toString() ?? '',
      type: json['type']?.toString() ?? 'other',
      code: json['code']?.toString(),
      qrData: json['qr_data']?.toString(),
      passengerName: json['passenger_name']?.toString(),
      departure: json['departure']?.toString(),
      arrival: json['arrival']?.toString(),
      departureTime: json['departure_time']?.toString(),
      seat: json['seat']?.toString(),
      notes: json['notes']?.toString(),
    );
  }

  Map<String, dynamic> toJson() => {
        'id': id,
        'trip_id': tripId,
        'type': type,
        'code': code,
        'qr_data': qrData,
        'passenger_name': passengerName,
        'departure': departure,
        'arrival': arrival,
        'departure_time': departureTime,
        'seat': seat,
        'notes': notes,
      };

  String get typeLabel {
    switch (type) {
      case 'flight':
        return 'Pesawat';
      case 'train':
        return 'Kereta';
      case 'bus':
        return 'Bus';
      case 'ship':
        return 'Kapal';
      default:
        return 'Lainnya';
    }
  }
}
