import 'package:flutter/material.dart';

IconData travelTicketIcon(String type) {
  switch (type) {
    case 'flight':
      return Icons.flight_takeoff_rounded;
    case 'train':
      return Icons.train_rounded;
    case 'bus':
      return Icons.directions_bus_rounded;
    case 'ship':
      return Icons.directions_boat_rounded;
    default:
      return Icons.confirmation_num_rounded;
  }
}
