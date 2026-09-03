import 'dart:convert';

import 'package:shared_preferences/shared_preferences.dart';

import '../models/category.dart';
import '../models/dashboard.dart';
import '../models/transaction.dart';
import '../models/wallet.dart';

class OfflineCacheService {
  OfflineCacheService._();
  static final OfflineCacheService instance = OfflineCacheService._();

  static const _kDashboard = 'duitku_cache_dashboard';
  static const _kCategories = 'duitku_cache_categories';
  static const _kWallets = 'duitku_cache_wallets';
  static const _kSymbol = 'duitku_cache_symbol';
  static const _kActivity = 'duitku_cache_activity';

  // ── Dashboard Cache ───────────────────────────────────────────
  Future<void> saveDashboard(Map<String, dynamic> rawJson) async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString(_kDashboard, jsonEncode(rawJson));
  }

  Future<DashboardData?> getDashboard() async {
    final prefs = await SharedPreferences.getInstance();
    final raw = prefs.getString(_kDashboard);
    if (raw == null || raw.isEmpty) return null;
    try {
      final json = jsonDecode(raw) as Map<String, dynamic>;
      return DashboardData.fromJson(json);
    } catch (_) {
      return null;
    }
  }

  Future<Map<String, dynamic>?> getDashboardRaw() async {
    final prefs = await SharedPreferences.getInstance();
    final raw = prefs.getString(_kDashboard);
    if (raw == null || raw.isEmpty) return null;
    try {
      return jsonDecode(raw) as Map<String, dynamic>;
    } catch (_) {
      return null;
    }
  }

  // ── Categories & Wallets ──────────────────────────────────────
  Future<void> saveCategories(List<Category> categories) async {
    final prefs = await SharedPreferences.getInstance();
    final list = categories
        .map((c) => {
              'id': c.id,
              'name': c.name,
              'type': c.type,
              'icon': c.icon,
              'color': c.color,
            })
        .toList();
    await prefs.setString(_kCategories, jsonEncode(list));
  }

  Future<List<Category>> getCategories() async {
    final prefs = await SharedPreferences.getInstance();
    final raw = prefs.getString(_kCategories);
    if (raw == null || raw.isEmpty) return [];
    try {
      final list = jsonDecode(raw) as List<dynamic>;
      return list.map((e) => Category.fromJson(e as Map<String, dynamic>)).toList();
    } catch (_) {
      return [];
    }
  }

  Future<void> saveWallets(List<Wallet> wallets, {String? symbol}) async {
    final prefs = await SharedPreferences.getInstance();
    final list = wallets
        .map((w) => {
              'id': w.id,
              'name': w.name,
              'type': w.type,
              'icon': w.icon,
              'color': w.color,
              'balance': w.balance,
              'is_default': w.isDefault ? 1 : 0,
            })
        .toList();
    await prefs.setString(_kWallets, jsonEncode(list));
    if (symbol != null) {
      await prefs.setString(_kSymbol, symbol);
    }
  }

  Future<(List<Wallet>, String)> getWallets() async {
    final prefs = await SharedPreferences.getInstance();
    final raw = prefs.getString(_kWallets);
    final symbol = prefs.getString(_kSymbol) ?? 'Rp';
    if (raw == null || raw.isEmpty) return (<Wallet>[], symbol);
    try {
      final list = jsonDecode(raw) as List<dynamic>;
      final wallets = list.map((e) => Wallet.fromJson(e as Map<String, dynamic>)).toList();
      return (wallets, symbol);
    } catch (_) {
      return (<Wallet>[], symbol);
    }
  }

  // ── Optimistic Updates (Offline Mode) ─────────────────────────
  Future<void> applyOptimisticTransaction(
    Transaction tx, {
    Category? category,
    Wallet? wallet,
  }) async {
    final raw = await getDashboardRaw();
    if (raw == null) return;

    try {
      // 1. Balance update
      double currentBalance = double.tryParse('${raw['balance']}') ?? 0;
      final monthly = Map<String, dynamic>.from(raw['monthly'] as Map? ?? {});
      double monthlyIncome = double.tryParse('${monthly['income']}') ?? 0;
      double monthlyExpense = double.tryParse('${monthly['expense']}') ?? 0;

      if (tx.type == 'income') {
        currentBalance += tx.amount;
        monthlyIncome += tx.amount;
      } else {
        currentBalance -= tx.amount;
        monthlyExpense += tx.amount;
      }

      monthly['income'] = monthlyIncome;
      monthly['expense'] = monthlyExpense;
      raw['balance'] = currentBalance;
      raw['monthly'] = monthly;

      // 2. Wallets balance update
      final rawWallets = (raw['wallets'] as List<dynamic>? ?? []).map((e) => Map<String, dynamic>.from(e as Map)).toList();
      if (tx.walletId != null) {
        for (final w in rawWallets) {
          if (w['id'] == tx.walletId) {
            double wb = double.tryParse('${w['balance']}') ?? 0;
            if (tx.type == 'income') {
              wb += tx.amount;
            } else {
              wb -= tx.amount;
            }
            w['balance'] = wb;
          }
        }
      }
      raw['wallets'] = rawWallets;

      // 3. Recent transactions list update
      final recent = (raw['recent'] as List<dynamic>? ?? []).map((e) => Map<String, dynamic>.from(e as Map)).toList();
      final txMap = tx.toJson();
      txMap['category_name'] = category?.name ?? tx.categoryName ?? 'Transaksi';
      txMap['category_icon'] = category?.icon ?? tx.categoryIcon ?? 'other';
      txMap['category_color'] = category?.color ?? tx.categoryColor ?? '#64748B';
      txMap['wallet_name'] = wallet?.name ?? tx.walletName ?? 'Dompet';
      txMap['is_pending_sync'] = true;

      recent.insert(0, txMap);
      raw['recent'] = recent;

      // Save updated cache
      await saveDashboard(raw);

      // Also update saved wallets list cache
      final (cachedWallets, symbol) = await getWallets();
      if (cachedWallets.isNotEmpty) {
        final updatedList = cachedWallets.map((w) {
          if (w.id == tx.walletId) {
            final newBal = tx.type == 'income' ? (w.balance + tx.amount) : (w.balance - tx.amount);
            return Wallet(
              id: w.id,
              name: w.name,
              type: w.type,
              icon: w.icon,
              color: w.color,
              balance: newBal,
              isDefault: w.isDefault,
            );
          }
          return w;
        }).toList();
        await saveWallets(updatedList, symbol: symbol);
      }
    } catch (_) {}
  }

  Future<void> applyOptimisticTransfer({
    required int fromId,
    required int toId,
    required double amount,
    String? note,
    String? date,
  }) async {
    final raw = await getDashboardRaw();
    if (raw != null) {
      try {
        final rawWallets = (raw['wallets'] as List<dynamic>? ?? []).map((e) => Map<String, dynamic>.from(e as Map)).toList();
        for (final w in rawWallets) {
          if (w['id'] == fromId) {
            double b = double.tryParse('${w['balance']}') ?? 0;
            w['balance'] = b - amount;
          } else if (w['id'] == toId) {
            double b = double.tryParse('${w['balance']}') ?? 0;
            w['balance'] = b + amount;
          }
        }
        raw['wallets'] = rawWallets;
        await saveDashboard(raw);
      } catch (_) {}
    }

    final (cachedWallets, symbol) = await getWallets();
    if (cachedWallets.isNotEmpty) {
      final updated = cachedWallets.map((w) {
        if (w.id == fromId) {
          return Wallet(
            id: w.id,
            name: w.name,
            type: w.type,
            icon: w.icon,
            color: w.color,
            balance: w.balance - amount,
            isDefault: w.isDefault,
          );
        } else if (w.id == toId) {
          return Wallet(
            id: w.id,
            name: w.name,
            type: w.type,
            icon: w.icon,
            color: w.color,
            balance: w.balance + amount,
            isDefault: w.isDefault,
          );
        }
        return w;
      }).toList();
      await saveWallets(updated, symbol: symbol);
    }
  }

  Future<void> clearCache() async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove(_kDashboard);
    await prefs.remove(_kCategories);
    await prefs.remove(_kWallets);
    await prefs.remove(_kSymbol);
    await prefs.remove(_kActivity);
  }
}
