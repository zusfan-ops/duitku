import 'dart:convert';
import 'dart:io';

import 'package:http/http.dart' as http;

import '../config/api_config.dart';
import '../models/bill.dart';
import '../models/category.dart';
import '../models/dashboard.dart';
import '../models/transaction.dart';
import '../models/user.dart';

class ApiException implements Exception {
  final String message;
  final int? status;
  ApiException(this.message, {this.status});
  @override
  String toString() => message;
}

class ApiService {
  ApiService._();
  static final ApiService instance = ApiService._();

  String? token;

  static const Duration _timeout = Duration(seconds: 45);

  Map<String, String> get _headers => {
        'Content-Type': 'application/json',
        if (token != null) 'Authorization': 'Bearer $token',
      };

  Uri _uri(String path, [Map<String, String>? query]) {
    final base = ApiConfig.endpoint(path);
    if (query != null && query.isNotEmpty) {
      final q = Uri(queryParameters: query);
      return Uri.parse('$base?${q.query}');
    }
    return Uri.parse(base);
  }

  Future<Map<String, dynamic>> get(String path, {Map<String, String>? query}) async {
    final res = await http.get(_uri(path, query), headers: _headers).timeout(_timeout);
    return _decode(res);
  }

  Future<Map<String, dynamic>> post(String path, [Map<String, dynamic>? body]) async {
    final res = await http
        .post(_uri(path), headers: _headers, body: jsonEncode(body ?? {}))
        .timeout(_timeout);
    return _decode(res);
  }

  Future<Map<String, dynamic>> _decode(http.Response res) async {
    Map<String, dynamic>? json;
    try {
      json = jsonDecode(utf8.decode(res.bodyBytes)) as Map<String, dynamic>;
    } catch (_) {
      json = null;
    }

    if (res.statusCode == 401) {
      if (json != null && json['message'] != null) {
        throw ApiException('${json['message']}', status: 401);
      }
      throw ApiException('Sesi berakhir. Silakan login kembali.', status: 401);
    }

    if (res.statusCode >= 400) {
      final msg = json?['message']?.toString() ?? 'Terjadi kesalahan (${res.statusCode}).';
      throw ApiException(msg, status: res.statusCode);
    }

    if (json != null && json['success'] == false) {
      throw ApiException(json['message']?.toString() ?? 'Terjadi kesalahan.', status: res.statusCode);
    }

    return json ?? {'success': true};
  }

  // ── Auth ─────────────────────────────────────────────────────
  Future<(User, String)> login(String email, String password, {String device = 'android'}) async {
    final json = await post('login', {'email': email, 'password': password, 'device': device});
    return (User.fromJson(json['user'] as Map<String, dynamic>), json['token'] as String);
  }

  Future<(User, String)> register(String name, String email, String phone, String password, String confirm) async {
    final json = await post('register', {
      'name': name,
      'email': email,
      'phone': phone,
      'password': password,
      'password_confirm': confirm,
      'device': 'android',
    });
    return (User.fromJson(json['user'] as Map<String, dynamic>), json['token'] as String);
  }

  Future<void> logout() async {
    try {
      await post('logout');
    } catch (_) {}
  }

  // ── Dashboard ────────────────────────────────────────────────
  Future<DashboardData> dashboard() async {
    final json = await get('dashboard');
    return DashboardData.fromJson(json);
  }

  Future<List<Category>> categories({String? type}) async {
    final json = await get('categories', query: type == null ? null : {'type': type});
    return (json['categories'] as List<dynamic>)
        .map((e) => Category.fromJson(e as Map<String, dynamic>))
        .toList();
  }

  // ── Activity / Transactions ──────────────────────────────────
  Future<Map<String, dynamic>> activity({
    String type = 'all',
    int page = 1,
    String search = '',
  }) async {
    return get('activity', query: {
      'type': type,
      'page': '$page',
      if (search.isNotEmpty) 'search': search,
    });
  }

  Future<Transaction> getTransaction(int id) async {
    final json = await get('transaction/$id');
    return Transaction.fromJson(json['transaction'] as Map<String, dynamic>);
  }

  Future<Map<String, dynamic>> storeTransaction({
    required String type,
    required double amount,
    int? categoryId,
    int? walletId,
    String? note,
    String? date,
    bool isRecurring = false,
    String? imageBase64,
  }) async {
    final body = {
      'type': type,
      'amount': amount,
      'category_id': ?categoryId,
      'wallet_id': ?walletId,
      if (note != null && note.isNotEmpty) 'note': note,
      'date': date ?? DateTime.now().toIso8601String().substring(0, 10),
      'is_recurring': isRecurring ? 1 : 0,
      'image_base64': ?imageBase64,
    };
    return post('transaction/store', body);
  }

  Future<Map<String, dynamic>> updateTransaction(
    int id, {
    required String type,
    required double amount,
    int? categoryId,
    int? walletId,
    String? note,
    String? date,
    String? imageBase64,
  }) async {
    final body = {
      'type': type,
      'amount': amount,
      'category_id': ?categoryId,
      'wallet_id': ?walletId,
      if (note != null && note.isNotEmpty) 'note': note,
      'date': date ?? DateTime.now().toIso8601String().substring(0, 10),
      'image_base64': ?imageBase64,
    };
    return post('transaction/update/$id', body);
  }

  Future<void> deleteTransaction(int id) async {
    await post('transaction/delete/$id');
  }

  Future<void> deleteRecurring(int id) async {
    await post('recurring/delete/$id');
  }

  // ── Stats ────────────────────────────────────────────────────
  Future<Map<String, dynamic>> stats(String month) async {
    return get('stats', query: {'month': month});
  }

  // ── Wallets ──────────────────────────────────────────────────
  Future<Map<String, dynamic>> wallets() async {
    return get('wallets');
  }

  Future<Map<String, dynamic>> storeWallet({
    int? id,
    required String name,
    required String type,
    String icon = '💵',
    String color = '#0AA956',
    double initialBalance = 0,
  }) async {
    return post('wallets/store', {
      'id': ?id,
      'name': name,
      'type': type,
      'icon': icon,
      'color': color,
      'initial_balance': initialBalance,
    });
  }

  Future<void> deleteWallet(int id) async {
    await post('wallets/delete/$id');
  }

  Future<void> setDefaultWallet(int id) async {
    await post('wallets/default/$id');
  }

  Future<void> transferWallet({
    required int fromId,
    required int toId,
    required double amount,
    String? note,
    String? date,
  }) async {
    await post('wallets/transfer', {
      'from_wallet_id': fromId,
      'to_wallet_id': toId,
      'amount': amount,
      if (note != null && note.isNotEmpty) 'note': note,
      'date': date ?? DateTime.now().toIso8601String().substring(0, 10),
    });
  }

  // ── Debts ────────────────────────────────────────────────────
  Future<Map<String, dynamic>> debts(String status) async {
    return get('hutang', query: {'status': status});
  }

  Future<Map<String, dynamic>> storeDebt({
    required String type,
    required String person,
    required double amount,
    String? description,
    String? dueDate,
    bool isPast = false,
  }) async {
    return post('hutang/store', {
      'type': type,
      'person': person,
      'amount': amount,
      if (description != null && description.isNotEmpty) 'description': description,
      if (dueDate != null && dueDate.isNotEmpty) 'due_date': dueDate,
      'is_past': isPast ? 1 : 0,
    });
  }

  Future<void> payDebt(int id, double amount) async {
    await post('hutang/pay/$id', {'pay_amount': amount});
  }

  Future<void> settleDebt(int id) async {
    await post('hutang/settle/$id');
  }

  Future<void> deleteDebt(int id) async {
    await post('hutang/delete/$id');
  }

  // ── Bills ────────────────────────────────────────────────────
  Future<List<Bill>> bills() async {
    final json = await get('bills');
    return (json['bills'] as List<dynamic>)
        .map((e) => Bill.fromJson(e as Map<String, dynamic>))
        .toList();
  }

  Future<Map<String, dynamic>> storeBill({
    String? id,
    required String name,
    required double amount,
    required int dueDay,
    String notes = '',
  }) async {
    return post('bills/store', {
      if (id != null && id.isNotEmpty) 'id': id,
      'name': name,
      'amount': amount,
      'due_day': dueDay,
      'notes': notes,
    });
  }

  Future<void> deleteBill(String id) async {
    await post('bills/delete/$id');
  }

  // ── Belanja sync ─────────────────────────────────────────────
  Future<Map<String, dynamic>> belanjaGet() async {
    return get('belanja');
  }

  Future<void> belanjaSync(Map<String, String> data) async {
    final body = <String, dynamic>{};
    data.forEach((k, v) {
      body[k] = v;
    });
    await post('belanja/sync', body);
  }

  // ── Settings ─────────────────────────────────────────────────
  Future<Map<String, dynamic>> settings() async {
    return get('settings');
  }

  Future<void> saveCurrency(String code) async {
    await post('settings/currency', {'currency': code});
  }

  Future<void> saveBudget(double amount, {String? month}) async {
    await post('settings/budget', {
      'amount': amount,
      'month': month ?? DateTime.now().toIso8601String().substring(0, 7),
    });
  }

  Future<Map<String, dynamic>> saveProfile({
    required String name,
    required String email,
    String? password,
  }) async {
    return post('settings/profile', {
      'name': name,
      'email': email,
      if (password != null && password.isNotEmpty) 'password': password,
    });
  }

  Future<void> saveAvatar(String imageBase64) async {
    await post('settings/avatar', {'image_base64': imageBase64});
  }

  Future<void> saveSavings({
    required String name,
    required double target,
    double saved = 0,
  }) async {
    await post('settings/savings', {
      'savings_name': name,
      'savings_target': target,
      'savings_saved': saved,
    });
  }

  Future<void> deleteSavings() async {
    await post('settings/savings/delete');
  }

  Future<void> saveNote(String note) async {
    await post('settings/note', {'note': note});
  }

  Future<Map<String, dynamic>> storeCategory({
    required String name,
    required String type,
    String icon = 'other',
    String color = '#6B7280',
  }) async {
    return post('categories/store', {
      'name': name,
      'type': type,
      'icon': icon,
      'color': color,
    });
  }

  Future<void> deleteCategory(int id) async {
    await post('categories/delete/$id');
  }

  // ── Upload helpers ───────────────────────────────────────────
  Future<String?> base64FromFile(String path) async {
    final file = File(path);
    if (!await file.exists()) return null;
    final bytes = await file.readAsBytes();
    return base64Encode(bytes);
  }
}
