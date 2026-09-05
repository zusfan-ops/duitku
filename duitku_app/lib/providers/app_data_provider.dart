import 'package:flutter/foundation.dart' show ChangeNotifier;

import '../models/category.dart';
import '../models/wallet.dart';
import '../services/api_service.dart';
import '../services/offline_cache_service.dart';

class AppDataProvider extends ChangeNotifier {
  List<Category> _categories = [];
  List<Wallet> _wallets = [];
  String _symbol = 'Rp';
  bool _loaded = false;

  int _marketChatUnread = 0;

  List<Category> get categories => _categories;
  List<Wallet> get wallets => _wallets;
  String get symbol => _symbol;
  bool get loaded => _loaded;
  int get marketChatUnread => _marketChatUnread;

  void setMarketChatUnread(int count) {
    if (_marketChatUnread != count) {
      _marketChatUnread = count;
      notifyListeners();
    }
  }

  Future<void> refreshMarketChatUnread() async {
    try {
      final res = await ApiService.instance.getMarketplaceChatConversations();
      final convs = (res['conversations'] as List<dynamic>?) ?? [];
      int total = 0;
      for (final c in convs) {
        if (c is Map) {
          total += int.tryParse('${c['unread_count']}') ?? 0;
        }
      }
      setMarketChatUnread(total);
    } catch (_) {}
  }

  Future<void> ensureLoaded({bool force = false}) async {
    if (_loaded && !force) return;

    // Load from local cache first for instant offline readiness
    if (!_loaded) {
      final cachedCats = await OfflineCacheService.instance.getCategories();
      final (cachedWallets, cachedSym) = await OfflineCacheService.instance.getWallets();
      if (cachedCats.isNotEmpty || cachedWallets.isNotEmpty) {
        _categories = cachedCats;
        _wallets = cachedWallets;
        _symbol = cachedSym;
        _loaded = true;
        notifyListeners();
      }
    }

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

      // Persist to local cache
      await OfflineCacheService.instance.saveCategories(_categories);
      await OfflineCacheService.instance.saveWallets(_wallets, symbol: _symbol);
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
      await OfflineCacheService.instance.saveWallets(_wallets, symbol: _symbol);
    } catch (_) {
      final (cachedWallets, cachedSym) = await OfflineCacheService.instance.getWallets();
      if (cachedWallets.isNotEmpty) {
        _wallets = cachedWallets;
        _symbol = cachedSym;
        notifyListeners();
      }
    }
  }

  void updateWalletsLocally(List<Wallet> newWallets) {
    _wallets = newWallets;
    notifyListeners();
    OfflineCacheService.instance.saveWallets(_wallets, symbol: _symbol);
  }
}

