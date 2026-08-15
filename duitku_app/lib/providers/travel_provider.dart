import 'dart:convert';

import 'package:flutter/foundation.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:uuid/uuid.dart';

import '../models/transaction.dart';
import '../models/travel_item.dart';
import '../models/travel_ticket.dart';
import '../models/travel_trip.dart';
import '../services/api_service.dart';

class TravelProvider extends ChangeNotifier {
  static const _kTrips = 'duitku_travel_trips';
  static const _kItems = 'duitku_travel_items';
  static const _kTickets = 'duitku_travel_tickets';
  static const _kTransactions = 'duitku_travel_transactions';

  List<TravelTrip> _trips = [];
  List<TravelItem> _items = [];
  List<TravelTicket> _tickets = [];
  List<Transaction> _transactions = [];
  bool _loaded = false;
  bool _isSyncing = false;

  List<TravelTrip> get trips => _trips;
  List<TravelItem> get items => _items;
  List<TravelTicket> get tickets => _tickets;
  List<Transaction> get transactions => _transactions;
  bool get loaded => _loaded;
  bool get isSyncing => _isSyncing;

  Future<void> ensureLoaded({bool force = false}) async {
    if (_loaded && !force) return;

    // Load from local storage first for offline / instant render
    if (!_loaded) {
      final prefs = await SharedPreferences.getInstance();
      _trips = _decodeList(prefs.getString(_kTrips), TravelTrip.fromJson);
      _items = _decodeList(prefs.getString(_kItems), TravelItem.fromJson);
      _tickets = _decodeList(prefs.getString(_kTickets), TravelTicket.fromJson);
      _transactions = _decodeList(prefs.getString(_kTransactions), Transaction.fromJson);
      _loaded = true;
      notifyListeners();
    }

    // Fetch from backend API
    await _fetchFromServer();
  }

  Future<void> refresh() async {
    await _fetchFromServer();
  }

  Future<void> _fetchFromServer() async {
    if (_isSyncing) return;
    _isSyncing = true;
    try {
      final res = await ApiService.instance.travelingGet();
      if (res['success'] == true) {
        if (res['trips'] is List) {
          _trips = (res['trips'] as List<dynamic>)
              .map((e) => TravelTrip.fromJson(e as Map<String, dynamic>))
              .toList();
        }
        if (res['items'] is List) {
          _items = (res['items'] as List<dynamic>)
              .map((e) => TravelItem.fromJson(e as Map<String, dynamic>))
              .toList();
        }
        if (res['tickets'] is List) {
          _tickets = (res['tickets'] as List<dynamic>)
              .map((e) => TravelTicket.fromJson(e as Map<String, dynamic>))
              .toList();
        }
        if (res['transactions'] is List) {
          _transactions = (res['transactions'] as List<dynamic>)
              .map((e) => Transaction.fromJson(e as Map<String, dynamic>))
              .toList();
        }
        _loaded = true;
        await _saveAllLocal();
        notifyListeners();
      }
    } catch (e) {
      debugPrint('TravelProvider sync error: $e');
    } finally {
      _isSyncing = false;
      notifyListeners();
    }
  }

  List<T> _decodeList<T>(String? raw, T Function(Map<String, dynamic>) fromJson) {
    if (raw == null || raw.isEmpty) return [];
    try {
      final list = jsonDecode(raw) as List<dynamic>;
      return list.map((e) => fromJson(e as Map<String, dynamic>)).toList();
    } catch (_) {
      return [];
    }
  }

  Future<void> _saveAllLocal() async {
    await Future.wait([_saveTrips(), _saveItems(), _saveTickets(), _saveTransactions()]);
  }

  Future<void> _saveTrips() async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString(_kTrips, jsonEncode(_trips.map((e) => e.toJson()).toList()));
  }

  Future<void> _saveItems() async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString(_kItems, jsonEncode(_items.map((e) => e.toJson()).toList()));
  }

  Future<void> _saveTickets() async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString(_kTickets, jsonEncode(_tickets.map((e) => e.toJson()).toList()));
  }

  Future<void> _saveTransactions() async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString(_kTransactions, jsonEncode(_transactions.map((e) {
      return {
        'id': e.id,
        'wallet_id': e.walletId,
        'category_id': e.categoryId,
        'type': e.type,
        'amount': e.amount,
        'note': e.note,
        'date': e.date,
        'image': e.image,
        'category_name': e.categoryName,
        'category_icon': e.categoryIcon,
        'category_color': e.categoryColor,
        'wallet_name': e.walletName,
      };
    }).toList()));
  }

  // ── Trips ────────────────────────────────────────────────────
  Future<void> addTrip(TravelTrip trip) async {
    _trips.add(trip);
    await _saveTrips();
    notifyListeners();

    try {
      final res = await ApiService.instance.travelingSync({
        'action': 'save_trip',
        'id': trip.id,
        'destination': trip.destination,
        'description': trip.description ?? '',
        'start_date': trip.startDate,
        'end_date': trip.endDate ?? '',
        'budget': trip.budget,
      });
      if (res['trip'] != null) {
        final serverTrip = TravelTrip.fromJson(res['trip'] as Map<String, dynamic>);
        final idx = _trips.indexWhere((t) => t.id == trip.id);
        if (idx >= 0) {
          _trips[idx] = serverTrip;
          await _saveTrips();
          notifyListeners();
        }
      }
    } catch (e) {
      debugPrint('Error syncing addTrip: $e');
    }
  }

  Future<void> updateTrip(TravelTrip updated) async {
    final idx = _trips.indexWhere((t) => t.id == updated.id);
    if (idx < 0) return;
    _trips[idx] = updated;
    await _saveTrips();
    notifyListeners();

    try {
      await ApiService.instance.travelingSync({
        'action': 'save_trip',
        'id': updated.id,
        'destination': updated.destination,
        'description': updated.description ?? '',
        'start_date': updated.startDate,
        'end_date': updated.endDate ?? '',
        'budget': updated.budget,
      });
    } catch (e) {
      debugPrint('Error syncing updateTrip: $e');
    }
  }

  Future<void> deleteTrip(String id) async {
    _trips.removeWhere((t) => t.id == id);
    _items.removeWhere((i) => i.tripId == id);
    _tickets.removeWhere((t) => t.tripId == id);
    _transactions.removeWhere((t) => _noteBelongsToTrip(t.note, id));
    await _saveAllLocal();
    notifyListeners();

    try {
      await ApiService.instance.travelingSync({
        'action': 'delete_trip',
        'id': id,
      });
    } catch (e) {
      debugPrint('Error syncing deleteTrip: $e');
    }
  }

  TravelTrip? tripById(String id) {
    try {
      return _trips.firstWhere((t) => t.id == id);
    } catch (_) {
      return null;
    }
  }

  // ── Checklist Items ──────────────────────────────────────────
  List<TravelItem> itemsForTrip(String tripId) =>
      _items.where((i) => i.tripId == tripId).toList();

  Future<void> addItem(TravelItem item) async {
    _items.add(item);
    await _saveItems();
    notifyListeners();

    try {
      final res = await ApiService.instance.travelingSync({
        'action': 'save_item',
        'id': item.id,
        'trip_id': item.tripId,
        'name': item.name,
        'is_packed': item.isPacked ? 1 : 0,
      });
      if (res['item'] != null) {
        final serverItem = TravelItem.fromJson(res['item'] as Map<String, dynamic>);
        final idx = _items.indexWhere((i) => i.id == item.id);
        if (idx >= 0) {
          _items[idx] = serverItem;
          await _saveItems();
          notifyListeners();
        }
      }
    } catch (e) {
      debugPrint('Error syncing addItem: $e');
    }
  }

  Future<void> updateItem(TravelItem updated) async {
    final idx = _items.indexWhere((i) => i.id == updated.id);
    if (idx < 0) return;
    _items[idx] = updated;
    await _saveItems();
    notifyListeners();

    try {
      await ApiService.instance.travelingSync({
        'action': 'save_item',
        'id': updated.id,
        'trip_id': updated.tripId,
        'name': updated.name,
        'is_packed': updated.isPacked ? 1 : 0,
      });
    } catch (e) {
      debugPrint('Error syncing updateItem: $e');
    }
  }

  Future<void> deleteItem(String id) async {
    _items.removeWhere((i) => i.id == id);
    await _saveItems();
    notifyListeners();

    try {
      await ApiService.instance.travelingSync({
        'action': 'delete_item',
        'id': id,
      });
    } catch (e) {
      debugPrint('Error syncing deleteItem: $e');
    }
  }

  Future<void> toggleItem(String id) async {
    final idx = _items.indexWhere((i) => i.id == id);
    if (idx < 0) return;
    final item = _items[idx];
    _items[idx] = item.copyWith(isPacked: !item.isPacked);
    await _saveItems();
    notifyListeners();

    try {
      await ApiService.instance.travelingSync({
        'action': 'toggle_item',
        'id': id,
      });
    } catch (e) {
      debugPrint('Error syncing toggleItem: $e');
    }
  }

  // ── Tickets ──────────────────────────────────────────────────
  List<TravelTicket> ticketsForTrip(String tripId) =>
      _tickets.where((t) => t.tripId == tripId).toList();

  Future<void> addTicket(TravelTicket ticket) async {
    _tickets.add(ticket);
    await _saveTickets();
    notifyListeners();

    try {
      final res = await ApiService.instance.travelingSync({
        'action': 'save_ticket',
        'id': ticket.id,
        'trip_id': ticket.tripId,
        'type': ticket.type,
        'code': ticket.code ?? '',
        'qr_data': ticket.qrData ?? (ticket.code ?? ''),
        'passenger_name': ticket.passengerName ?? '',
        'departure': ticket.departure ?? '',
        'arrival': ticket.arrival ?? '',
        'departure_time': ticket.departureTime ?? '',
        'seat': ticket.seat ?? '',
        'notes': ticket.notes ?? '',
      });
      if (res['ticket'] != null) {
        final serverTicket = TravelTicket.fromJson(res['ticket'] as Map<String, dynamic>);
        final idx = _tickets.indexWhere((t) => t.id == ticket.id);
        if (idx >= 0) {
          _tickets[idx] = serverTicket;
          await _saveTickets();
          notifyListeners();
        }
      }
    } catch (e) {
      debugPrint('Error syncing addTicket: $e');
    }
  }

  Future<void> updateTicket(TravelTicket updated) async {
    final idx = _tickets.indexWhere((t) => t.id == updated.id);
    if (idx < 0) return;
    _tickets[idx] = updated;
    await _saveTickets();
    notifyListeners();

    try {
      await ApiService.instance.travelingSync({
        'action': 'save_ticket',
        'id': updated.id,
        'trip_id': updated.tripId,
        'type': updated.type,
        'code': updated.code ?? '',
        'qr_data': updated.qrData ?? (updated.code ?? ''),
        'passenger_name': updated.passengerName ?? '',
        'departure': updated.departure ?? '',
        'arrival': updated.arrival ?? '',
        'departure_time': updated.departureTime ?? '',
        'seat': updated.seat ?? '',
        'notes': updated.notes ?? '',
      });
    } catch (e) {
      debugPrint('Error syncing updateTicket: $e');
    }
  }

  Future<void> deleteTicket(String id) async {
    _tickets.removeWhere((t) => t.id == id);
    await _saveTickets();
    notifyListeners();

    try {
      await ApiService.instance.travelingSync({
        'action': 'delete_ticket',
        'id': id,
      });
    } catch (e) {
      debugPrint('Error syncing deleteTicket: $e');
    }
  }

  // ── Transactions / Cost ──────────────────────────────────────
  String _tripNotePrefix(String tripId) => '[Trip:$tripId]';

  bool _noteBelongsToTrip(String? note, String tripId) =>
      note != null && note.startsWith(_tripNotePrefix(tripId));

  List<Transaction> transactionsForTrip(String tripId) =>
      _transactions.where((t) => _noteBelongsToTrip(t.note, tripId)).toList();

  double totalCostForTrip(String tripId) {
    return transactionsForTrip(tripId)
        .where((t) => t.type == 'expense')
        .fold(0.0, (sum, t) => sum + t.amount);
  }

  Future<Transaction?> addTransaction({
    required String tripId,
    required String type,
    required double amount,
    int? categoryId,
    int? walletId,
    String? note,
    String? date,
  }) async {
    final taggedNote = '${_tripNotePrefix(tripId)} ${note ?? ''}'.trim();
    try {
      final res = await ApiService.instance.storeTransaction(
        type: type,
        amount: amount,
        categoryId: categoryId,
        walletId: walletId,
        note: taggedNote,
        date: date,
      );
      final txJson = res['transaction'] as Map<String, dynamic>?;
      if (txJson == null) return null;
      final tx = Transaction.fromJson(txJson);
      _transactions.insert(0, tx);
      await _saveTransactions();
      notifyListeners();
      return tx;
    } on ApiException catch (e) {
      throw Exception(e.message);
    }
  }

  Future<void> deleteTransaction(int id) async {
    try {
      await ApiService.instance.deleteTransaction(id);
      _transactions.removeWhere((t) => t.id == id);
      await _saveTransactions();
      notifyListeners();
    } on ApiException catch (e) {
      throw Exception(e.message);
    }
  }
}

const _uuid = Uuid();
String generateId() => _uuid.v4();
