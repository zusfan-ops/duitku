import 'dart:convert';

import 'package:shared_preferences/shared_preferences.dart';

import '../models/user.dart';
import 'api_service.dart';

class SessionManager {
  static const _kToken = 'duitku_token';
  static const _kUser = 'duitku_user';

  static Future<void> save(String token, User user) async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString(_kToken, token);
    await prefs.setString(_kUser, jsonEncode(user.toJson()));
    ApiService.instance.token = token;
  }

  static Future<User?> restore() async {
    final prefs = await SharedPreferences.getInstance();
    final token = prefs.getString(_kToken);
    final userJson = prefs.getString(_kUser);

    if (token == null || userJson == null) {
      return null;
    }

    ApiService.instance.token = token;
    return User.fromJson(jsonDecode(userJson) as Map<String, dynamic>);
  }

  static Future<void> clear() async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove(_kToken);
    await prefs.remove(_kUser);
    ApiService.instance.token = null;
  }

  static Future<void> updateUser(User user) async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString(_kUser, jsonEncode(user.toJson()));
  }
}
