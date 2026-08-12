import 'dart:convert';

import 'package:shared_preferences/shared_preferences.dart';

import '../models/barang.dart';

/// Penyimpanan lokal data barang (offline-first).
///
/// Data disimpan sebagai JSON list di SharedPreferences. Setiap barang
/// menyimpan nama, lokasi, foto barang, dan foto lokasi dalam bentuk
/// base64 atau path file lokal.
class BarangStore {
  static const _key = 'barang_storage_list';

  static Future<List<Barang>> loadAll() async {
    final prefs = await SharedPreferences.getInstance();
    final raw = prefs.getString(_key);
    if (raw == null || raw.isEmpty) return [];
    try {
      final decoded = jsonDecode(raw);
      if (decoded is! List) return [];
      return decoded
          .map((e) => Barang.fromJson(e as Map<String, dynamic>))
          .toList();
    } catch (_) {
      return [];
    }
  }

  static Future<void> saveAll(List<Barang> list) async {
    final prefs = await SharedPreferences.getInstance();
    final payload = list.map((e) => e.toJson()).toList();
    await prefs.setString(_key, jsonEncode(payload));
  }

  static Future<void> add(Barang barang) async {
    final list = await loadAll();
    list.add(barang);
    await saveAll(list);
  }

  static Future<void> update(Barang barang) async {
    final list = await loadAll();
    final idx = list.indexWhere((b) => b.id == barang.id);
    if (idx >= 0) {
      list[idx] = barang;
      await saveAll(list);
    }
  }

  static Future<void> delete(String id) async {
    final list = await loadAll();
    list.removeWhere((b) => b.id == id);
    await saveAll(list);
  }

  static Future<Barang?> getById(String id) async {
    final list = await loadAll();
    try {
      return list.firstWhere((b) => b.id == id);
    } catch (_) {
      return null;
    }
  }
}
