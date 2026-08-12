import 'package:flutter/material.dart';

/// Memetakan nama ikon kategori (dari DB) ke ikon Material.
IconData categoryIcon(String name) {
  switch (name) {
    case 'food':
      return Icons.restaurant;
    case 'transport':
      return Icons.directions_car;
    case 'utilities':
      return Icons.bolt;
    case 'shopping':
      return Icons.shopping_bag;
    case 'fun':
      return Icons.movie;
    case 'health':
      return Icons.favorite;
    case 'home':
      return Icons.home;
    case 'salary':
      return Icons.account_balance_wallet;
    case 'freelance':
      return Icons.work;
    case 'gift':
      return Icons.card_giftcard;
    case 'other':
      return Icons.category;
    default:
      return Icons.circle;
  }
}

Color parseColor(String hex) {
  var h = hex.replaceAll('#', '');
  if (h.length == 3) {
    h = h.split('').map((e) => '$e$e').join();
  }
  if (h.length != 6) return Colors.grey;
  return Color(int.parse('FF$h', radix: 16));
}
