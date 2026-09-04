import 'dart:async';
import 'dart:convert';
import 'dart:developer';

import 'package:connectivity_plus/connectivity_plus.dart';
import 'package:flutter/foundation.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:uuid/uuid.dart';

import '../models/sync_item.dart';
import 'api_service.dart';
import 'local_notification_service.dart';
import 'offline_cache_service.dart';

class SyncService extends ChangeNotifier {
  SyncService._();
  static final SyncService instance = SyncService._();

  static const _kQueue = 'duitku_offline_queue';
  static const _kLastSync = 'duitku_last_sync_timestamp';

  final _uuid = const Uuid();
  List<SyncItem> _queue = [];
  bool _isOnline = true;
  bool _isSyncing = false;
  DateTime? _lastSyncTime;
  StreamSubscription? _connectivitySub;

  List<SyncItem> get queue => List.unmodifiable(_queue);
  int get pendingCount => _queue.length;
  bool get isOnline => _isOnline;
  bool get isSyncing => _isSyncing;
  DateTime? get lastSyncTime => _lastSyncTime;

  /// Callback when sync completes successfully with new data
  VoidCallback? onSyncCompleted;

  Future<void> init() async {
    await _loadQueue();

    final prefs = await SharedPreferences.getInstance();
    final lastSyncStr = prefs.getString(_kLastSync);
    if (lastSyncStr != null) {
      _lastSyncTime = DateTime.tryParse(lastSyncStr);
    }

    // Monitor connectivity changes
    _connectivitySub = Connectivity().onConnectivityChanged.listen((results) {
      final hasNet = results.any((r) => r != ConnectivityResult.none);
      _handleConnectivityChange(hasNet);
    });

    // Check initial connectivity status
    try {
      final initial = await Connectivity().checkConnectivity();
      _isOnline = initial.any((r) => r != ConnectivityResult.none);
    } catch (_) {
      _isOnline = true;
    }

    notifyListeners();

    if (_isOnline && _queue.isNotEmpty) {
      // Background sync on boot if there are pending items
      unawaited(syncAll());
    }
  }

  @override
  void dispose() {
    _connectivitySub?.cancel();
    super.dispose();
  }

  void _handleConnectivityChange(bool online) {
    final prev = _isOnline;
    _isOnline = online;
    notifyListeners();

    if (!prev && online) {
      log('[SyncService] Device is back online! Triggering auto-sync...');
      syncAll();
    }
  }

  Future<void> _loadQueue() async {
    final prefs = await SharedPreferences.getInstance();
    final raw = prefs.getString(_kQueue);
    if (raw == null || raw.isEmpty) {
      _queue = [];
      return;
    }
    try {
      final list = jsonDecode(raw) as List<dynamic>;
      _queue = list.map((e) => SyncItem.fromJson(e as Map<String, dynamic>)).toList();
    } catch (e) {
      log('[SyncService] Error parsing queue: $e');
      _queue = [];
    }
  }

  Future<void> _saveQueue() async {
    final prefs = await SharedPreferences.getInstance();
    final raw = jsonEncode(_queue.map((e) => e.toJson()).toList());
    await prefs.setString(_kQueue, raw);
  }

  /// Add an action to the offline queue and automatically attempt sync if online
  Future<String> enqueue(String type, Map<String, dynamic> payload) async {
    final item = SyncItem(
      id: _uuid.v4(),
      type: type,
      payload: payload,
      createdAt: DateTime.now(),
      status: 'pending',
    );

    _queue.add(item);
    await _saveQueue();
    notifyListeners();

    log('[SyncService] Enqueued action $type (ID: ${item.id}). Pending: ${_queue.length}');

    if (_isOnline && !_isSyncing) {
      unawaited(syncAll());
    }

    return item.id;
  }

  /// Trigger manual or automatic synchronization of all queued actions
  Future<bool> syncAll() async {
    if (_isSyncing) return false;
    if (_queue.isEmpty) {
      // Even if queue is empty, if we're online we can refresh cache
      if (_isOnline && ApiService.instance.token != null) {
        await _refreshServerData();
      }
      return true;
    }

    _isSyncing = true;
    notifyListeners();

    log('[SyncService] Starting sync of ${_queue.length} items...');
    bool allSuccess = true;
    final itemsToProcess = List<SyncItem>.from(_queue);

    for (final item in itemsToProcess) {
      if (!_isOnline) {
        allSuccess = false;
        break;
      }

      item.status = 'syncing';
      notifyListeners();

      try {
        await _executeItem(item);
        _queue.removeWhere((i) => i.id == item.id);
        await _saveQueue();
        log('[SyncService] Successfully synced action ${item.type} (${item.id})');
      } on ApiException catch (e) {
        if (e.status == 401) {
          // Authentication issue - stop sync
          item.status = 'failed';
          item.error = e.message;
          allSuccess = false;
          break;
        } else if (e.status != null && e.status! >= 400 && e.status! < 500) {
          // Client/Validation error (e.g. invalid parameter) - remove to avoid infinite blocking
          item.retries++;
          if (item.retries >= 3) {
            log('[SyncService] Item ${item.id} exceeded retries due to client error: ${e.message}');
            _queue.removeWhere((i) => i.id == item.id);
            await _saveQueue();
          } else {
            item.status = 'failed';
            item.error = e.message;
          }
        } else {
          // Server error 5xx
          item.retries++;
          item.status = 'failed';
          item.error = e.message;
        }
      } catch (e) {
        // Network drop or timeout
        log('[SyncService] Network error while syncing ${item.id}: $e');
        item.status = 'pending';
        item.error = e.toString();
        _isOnline = false;
        allSuccess = false;
        break;
      }
    }

    _isSyncing = false;
    _lastSyncTime = DateTime.now();
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString(_kLastSync, _lastSyncTime!.toIso8601String());
    await _saveQueue();
    notifyListeners();

    if (_isOnline && ApiService.instance.token != null) {
      await _refreshServerData();
    }

    onSyncCompleted?.call();
    return allSuccess;
  }

  Future<void> _refreshServerData() async {
    try {
      final dashboard = await ApiService.instance.dashboard();
      // Update cache
      final cats = await ApiService.instance.categories();
      final walletsRes = await ApiService.instance.wallets();
      final wallets = (walletsRes['wallets'] as List<dynamic>? ?? [])
          .map((e) => e as Map<String, dynamic>)
          .toList();

      final dashJson = {
        'balance': dashboard.balance,
        'monthly': {
          'income': dashboard.monthlyIncome,
          'expense': dashboard.monthlyExpense,
        },
        'recent': dashboard.recent,
        'categories': cats.map((c) => {
          'id': c.id,
          'name': c.name,
          'type': c.type,
          'icon': c.icon,
          'color': c.color,
        }).toList(),
        'currency': dashboard.currency,
        'symbol': dashboard.symbol,
        'month': dashboard.month,
        'monthKey': dashboard.monthKey,
        'budget': dashboard.budget,
        'budgetPct': dashboard.budgetPct,
        'savingsName': dashboard.savingsName,
        'savingsTarget': dashboard.savingsTarget,
        'savingsSaved': dashboard.savingsSaved,
        'savingsPct': dashboard.savingsPct,
        'monthNote': dashboard.monthNote,
        'debtSummary': {
          'total_piutang': dashboard.debtSummary.totalPiutang,
          'total_hutang': dashboard.debtSummary.totalHutang,
          'active_count': dashboard.debtSummary.activeCount,
        },
        'topCategories': dashboard.topCategories,
        'wallets': wallets,
        'dailyBalance': dashboard.dailyBalance,
        'upcomingBills': dashboard.upcomingBills.map((b) => b.toJson()).toList(),
        'upcomingDebts': dashboard.upcomingDebts,
        'upcomingTaxes': dashboard.upcomingTaxes,
        'upcomingRecurring': dashboard.upcomingRecurring,
        'notifications': dashboard.notifications,
        'business': dashboard.business,
      };

      await OfflineCacheService.instance.saveDashboard(dashJson);
      await OfflineCacheService.instance.saveCategories(cats);
      await OfflineCacheService.instance.saveWallets(dashboard.wallets, symbol: dashboard.symbol);
      LocalNotificationService.instance.checkAndNotifyNewBroadcasts(dashboard.broadcastNotifications);
    } catch (e) {
      log('[SyncService] Refresh server data error: $e');
    }
  }

  Future<void> _executeItem(SyncItem item) async {
    final p = item.payload;
    switch (item.type) {
      case 'transaction_store':
        await ApiService.instance.storeTransaction(
          type: p['type']?.toString() ?? 'expense',
          amount: double.tryParse('${p['amount']}') ?? 0,
          categoryId: p['category_id'] == null ? null : int.tryParse('${p['category_id']}'),
          walletId: p['wallet_id'] == null ? null : int.tryParse('${p['wallet_id']}'),
          note: p['note']?.toString(),
          date: p['date']?.toString(),
          isRecurring: p['is_recurring'] == true || p['is_recurring'] == 1,
          imageBase64: p['image_base64']?.toString(),
        );
        break;

      case 'transaction_update':
        final id = int.tryParse('${p['id']}') ?? 0;
        await ApiService.instance.updateTransaction(
          id,
          type: p['type']?.toString() ?? 'expense',
          amount: double.tryParse('${p['amount']}') ?? 0,
          categoryId: p['category_id'] == null ? null : int.tryParse('${p['category_id']}'),
          walletId: p['wallet_id'] == null ? null : int.tryParse('${p['wallet_id']}'),
          note: p['note']?.toString(),
          date: p['date']?.toString(),
          imageBase64: p['image_base64']?.toString(),
        );
        break;

      case 'transaction_delete':
        final id = int.tryParse('${p['id']}') ?? 0;
        await ApiService.instance.deleteTransaction(id);
        break;

      case 'wallet_transfer':
        await ApiService.instance.transferWallet(
          fromId: int.tryParse('${p['from_id']}') ?? 0,
          toId: int.tryParse('${p['to_id']}') ?? 0,
          amount: double.tryParse('${p['amount']}') ?? 0,
          note: p['note']?.toString(),
          date: p['date']?.toString(),
        );
        break;

      case 'wallet_store':
        await ApiService.instance.storeWallet(
          id: p['id'] == null ? null : int.tryParse('${p['id']}'),
          name: p['name']?.toString() ?? 'Dompet',
          type: p['type']?.toString() ?? 'cash',
          icon: p['icon']?.toString() ?? '💵',
          color: p['color']?.toString() ?? '#0AA956',
          initialBalance: double.tryParse('${p['initial_balance']}') ?? 0,
        );
        break;

      case 'debt_store':
        await ApiService.instance.storeDebt(
          type: p['type']?.toString() ?? 'piutang',
          person: p['person']?.toString() ?? '',
          amount: double.tryParse('${p['amount']}') ?? 0,
          description: p['description']?.toString(),
          dueDate: p['due_date']?.toString(),
          isPast: p['is_past'] == 1 || p['is_past'] == true,
        );
        break;

      case 'debt_pay':
        final id = int.tryParse('${p['id']}') ?? 0;
        final amount = double.tryParse('${p['amount']}') ?? 0;
        await ApiService.instance.payDebt(id, amount);
        break;

      case 'debt_settle':
        final id = int.tryParse('${p['id']}') ?? 0;
        await ApiService.instance.settleDebt(id);
        break;

      case 'bill_store':
        await ApiService.instance.storeBill(
          id: p['id']?.toString(),
          name: p['name']?.toString() ?? '',
          amount: double.tryParse('${p['amount']}') ?? 0,
          dueDay: int.tryParse('${p['due_day']}') ?? 1,
          notes: p['notes']?.toString() ?? '',
        );
        break;

      case 'note_store':
        await ApiService.instance.saveNote(p['note']?.toString() ?? '');
        break;

      default:
        log('[SyncService] Unknown action type: ${item.type}');
    }
  }

  Future<void> remove(String id) async {
    _queue.removeWhere((i) => i.id == id);
    await _saveQueue();
    notifyListeners();
  }

  Future<void> clearQueue() async {
    _queue.clear();
    await _saveQueue();
    notifyListeners();
  }
}
