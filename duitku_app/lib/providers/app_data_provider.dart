import 'package:flutter/foundation.dart' show ChangeNotifier;

import '../models/category.dart';
import '../models/wallet.dart';
import '../services/api_service.dart';

class AppDataProvider extends ChangeNotifier {
  List<Category> _categories = [];
  List<Wallet> _wallets = [];
  String _symbol = 'Rp';
  bool _loaded = false;

  List<Category> get categories => _categories;
  List<Wallet> get wallets => _wallets;
  String get symbol => _symbol;
  bool get loaded => _loaded;

  Future<void> ensureLoaded({bool force = false}) async {
    if (_loaded && !force) return;
    try {
      final results = await Future.wait([
        ApiService.instance.categories(),
        ApiService.instance.wallets(),
      ]);
      _categories = results[0] as List<Category>;
      final walletJson = results[1] as Map<String, dynamic>;
      _wallets = (walletJson['wallets'] as List<dynamic>? ?? [])
          .map((e) => Wallet.fromJson(e as Map<String, dynamic>))
          .toList();
      _symbol = walletJson['symbol']?.toString() ?? _symbol;
      _loaded = true;
      notifyListeners();
    } catch (_) {}
  }

  Future<void> reloadWallets() async {
    try {
      final walletJson = await ApiService.instance.wallets();
      _wallets = (walletJson['wallets'] as List<dynamic>? ?? [])
          .map((e) => Wallet.fromJson(e as Map<String, dynamic>))
          .toList();
      _symbol = walletJson['symbol']?.toString() ?? _symbol;
      notifyListeners();
    } catch (_) {}
  }
}
