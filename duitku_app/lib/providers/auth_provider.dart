import 'dart:developer';

import 'package:flutter/foundation.dart';

import '../models/user.dart';
import '../services/api_service.dart';
import '../services/session_manager.dart';

class AuthProvider extends ChangeNotifier {
  User? _user;
  bool _initializing = true;
  bool _busy = false;

  User? get user => _user;
  bool get isLoggedIn => _user != null;
  bool get initializing => _initializing;
  bool get busy => _busy;

  Future<void> init() async {
    // Keep the splash visible for a moment so its rotating tip is actually readable,
    // even though restoring the session locally is otherwise near-instant.
    try {
      final results = await Future.wait([
        SessionManager.restore(),
        Future.delayed(const Duration(milliseconds: 2500)),
      ]);
      _user = results[0] as User?;
    } catch (e, st) {
      log('Session restore failed: $e', stackTrace: st);
      _user = null;
    } finally {
      _initializing = false;
      notifyListeners();
    }
  }

  Future<String?> login(String email, String password) async {
    _busy = true;
    notifyListeners();
    try {
      final (user, token) = await ApiService.instance.login(email, password);
      await SessionManager.save(token, user);
      _user = user;
      return null;
    } on ApiException catch (e) {
      return e.message;
    } catch (e) {
      return 'Tidak dapat terhubung ke server. ($e)';
    } finally {
      _busy = false;
      notifyListeners();
    }
  }

  Future<String?> register(String name, String email, String password, String confirm) async {
    _busy = true;
    notifyListeners();
    try {
      final (user, token) = await ApiService.instance.register(name, email, password, confirm);
      await SessionManager.save(token, user);
      _user = user;
      return null;
    } on ApiException catch (e) {
      return e.message;
    } catch (e) {
      return 'Tidak dapat terhubung ke server. ($e)';
    } finally {
      _busy = false;
      notifyListeners();
    }
  }

  Future<void> logout() async {
    await ApiService.instance.logout();
    await SessionManager.clear();
    _user = null;
    notifyListeners();
  }

  Future<void> updateUser(User user) async {
    _user = user;
    await SessionManager.updateUser(user);
    notifyListeners();
  }
}
