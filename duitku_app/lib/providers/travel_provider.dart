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

  List<TravelTrip> get trips => _trips;
  List<TravelItem> get items => _items;
  List<TravelTicket> get tickets => _tickets;
  List<Transaction> get transactions => _transactions;
  bool get loaded => _loaded;

  Future<void> ensureLoaded() async {
    if (_loaded) return;
    final prefs = await SharedPreferences.getInstance();
    _trips = _decodeList(prefs.getString(_kTrips), TravelTrip.fromJson);
    _items = _decodeList(prefs.getString(_kItems), TravelItem.fromJson);
    _tickets = _decodeList(prefs.getString(_kTickets), TravelTicket.fromJson);
    _transactions = _decodeList(prefs.getString(_kTransactions), Transaction.fromJson);
    _loaded = true;
    notifyListeners();
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
      // Transaction tidak punya toJson, buat manual.
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
  }

  Future<void> updateTrip(TravelTrip updated) async {
    final idx = _trips.indexWhere((t) => t.id == updated.id);
    if (idx < 0) return;
    _trips[idx] = updated;
    await _saveTrips();
    notifyListeners();
  }

  Future<void> deleteTrip(String id) async {
    _trips.removeWhere((t) => t.id == id);
    _items.removeWhere((i) => i.tripId == id);
    _tickets.removeWhere((t) => t.tripId == id);
    _transactions.removeWhere((t) => _noteBelongsToTrip(t.note, id));
    await Future.wait([_saveTrips(), _saveItems(), _saveTickets(), _saveTransactions()]);
    notifyListeners();
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
  }

  Future<void> updateItem(TravelItem updated) async {
    final idx = _items.indexWhere((i) => i.id == updated.id);
    if (idx < 0) return;
    _items[idx] = updated;
    await _saveItems();
    notifyListeners();
  }

  Future<void> deleteItem(String id) async {
    _items.removeWhere((i) => i.id == id);
    await _saveItems();
    notifyListeners();
  }

  Future<void> toggleItem(String id) async {
    final idx = _items.indexWhere((i) => i.id == id);
    if (idx < 0) return;
    final item = _items[idx];
    _items[idx] = item.copyWith(isPacked: !item.isPacked);
    await _saveItems();
    notifyListeners();
  }

  // ── Tickets ──────────────────────────────────────────────────
  List<TravelTicket> ticketsForTrip(String tripId) =>
      _tickets.where((t) => t.tripId == tripId).toList();

  Future<void> addTicket(TravelTicket ticket) async {
    _tickets.add(ticket);
    await _saveTickets();
    notifyListeners();
  }

  Future<void> updateTicket(TravelTicket updated) async {
    final idx = _tickets.indexWhere((t) => t.id == updated.id);
    if (idx < 0) return;
    _tickets[idx] = updated;
    await _saveTickets();
    notifyListeners();
  }

  Future<void> deleteTicket(String id) async {
    _tickets.removeWhere((t) => t.id == id);
    await _saveTickets();
    notifyListeners();
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
      _transactions.add(tx);
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
