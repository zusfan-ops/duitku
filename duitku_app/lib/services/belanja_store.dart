import 'dart:convert';

import 'package:shared_preferences/shared_preferences.dart';

import 'api_service.dart';

/// Penyimpanan lokal data Belanja (offline-first) + sinkronisasi ke server.
class BelanjaStore {
  static const _prefix = 'belanja_';
  static const _dirtyKey = 'belanja_dirty';

  static const keys = [
    'belanja_data',
    'belanja_notes',
    'belanja_storage',
    'belanja_favorites',
    'belanja_history',
    'belanja_pantry',
    'belanja_reminders',
    'belanja_lists',
    'belanja_current_list',
    'belanja_parking',
  ];

  // ── Local persistence ────────────────────────────────────────
  static Future<String?> localGet(String key) async {
    final prefs = await SharedPreferences.getInstance();
    return prefs.getString(_prefix + key);
  }

  static Future<void> localSet(String key, dynamic value) async {
    final prefs = await SharedPreferences.getInstance();
    final str = value is String ? value : jsonEncode(value);
    await prefs.setString(_prefix + key, str);
    await prefs.setBool(_dirtyKey, true);
  }

  static Future<List<Map<String, dynamic>>> localList(String key) async {
    final raw = await localGet(key);
    if (raw == null || raw.isEmpty) return [];
    try {
      final decoded = jsonDecode(raw);
      if (decoded is List) {
        return decoded.map((e) => (e as Map<String, dynamic>)).toList();
      }
    } catch (_) {}
    return [];
  }

  static Future<Map<String, dynamic>> localMap(String key) async {
    final raw = await localGet(key);
    if (raw == null || raw.isEmpty) return {};
    try {
      final decoded = jsonDecode(raw);
      if (decoded is Map) {
        return decoded.map((k, v) => MapEntry(k.toString(), v));
      }    } catch (_) {}
    return {};
  }

  static Future<void> markDirty(bool value) async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setBool(_dirtyKey, value);
  }

  static Future<bool> isDirty() async {
    final prefs = await SharedPreferences.getInstance();
    return prefs.getBool(_dirtyKey) ?? false;
  }

  // ── Sync ─────────────────────────────────────────────────────
  /// Pull semua data dari server lalu simpan lokal.
  static Future<void> pull() async {
    try {
      final res = await ApiService.instance.belanjaGet();
      final data = res['data'] as Map<String, dynamic>? ?? {};
      final prefs = await SharedPreferences.getInstance();
      for (final key in keys) {
        final value = data[key];
        if (value != null) {
          await prefs.setString(_prefix + key, value.toString());
        }
      }
      await prefs.setBool(_dirtyKey, false);
    } catch (_) {
      // offline: abaikan
    }
  }

  /// Push semua data lokal yang berubah ke server.
  static Future<bool> push() async {
    final prefs = await SharedPreferences.getInstance();
    final payload = <String, dynamic>{};
    for (final key in keys) {
      final value = prefs.getString(_prefix + key);
      if (value != null) {
        payload[key] = value;
      }
    }
    try {
      await ApiService.instance.belanjaSync(payload.map((k, v) => MapEntry(k, v.toString())));
      await prefs.setBool(_dirtyKey, false);
      return true;
    } catch (_) {
      return false;
    }
  }
}
