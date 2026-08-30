import 'dart:developer';

import 'package:home_widget/home_widget.dart';

import '../models/dashboard.dart';
import '../utils/format.dart';
import 'api_service.dart';

/// Helper untuk sinkronisasi data ke Android home screen widget.
///
/// Widget membaca data terakhir yang disimpan via [HomeWidget.saveWidgetData]
/// dan menampilkannya melalui native [DuitkuWidgetProvider].
class WidgetHelper {
  static const _groupId = 'com.duitku.duitku_app.widget';
  static const _widgetName = 'DuitkuWidgetProvider';
  static const _qualifiedAndroidName = 'com.duitku.duitku_app.DuitkuWidgetProvider';

  /// Key data yang dibaca oleh native widget.
  static const _kBalance = 'widget_balance';
  static const _kIncome = 'widget_income';
  static const _kExpense = 'widget_expense';
  static const _kMonth = 'widget_month';
  static const _kSymbol = 'widget_symbol';

  /// Ambil snapshot dashboard dan push ke native widget.
  ///
  /// Dipanggil setiap kali dashboard berhasil di-load atau transaksi
  /// berhasil disimpan/dihapus.
  static Future<void> updateDashboardWidget([DashboardData? preloadedData]) async {
    try {
      final data = preloadedData ?? await ApiService.instance.dashboard();

      await HomeWidget.saveWidgetData(
        _kBalance,
        Fmt.money(data.balance, symbol: data.symbol),
      );
      await HomeWidget.saveWidgetData(
        _kIncome,
        Fmt.money(data.monthlyIncome, symbol: data.symbol),
      );
      await HomeWidget.saveWidgetData(
        _kExpense,
        Fmt.money(data.monthlyExpense, symbol: data.symbol),
      );
      await HomeWidget.saveWidgetData(_kMonth, data.month);
      await HomeWidget.saveWidgetData(_kSymbol, data.symbol);

      await HomeWidget.updateWidget(
        name: _widgetName,
        androidName: _widgetName,
        qualifiedAndroidName: _qualifiedAndroidName,
        iOSName: _widgetName,
      );
    } catch (e, st) {
      log('Widget update failed: $e', stackTrace: st);
    }
  }

  /// Request pin widget otomatis ke Layar Utama (Android 8.0+).
  static Future<bool> requestPinWidget() async {
    try {
      final supported = await HomeWidget.isRequestPinWidgetSupported();
      if (supported == true) {
        await updateDashboardWidget();
        await HomeWidget.requestPinWidget(
          name: _widgetName,
          androidName: _widgetName,
          qualifiedAndroidName: _qualifiedAndroidName,
        );
        return true;
      }
    } catch (e) {
      log('Request pin widget failed: $e');
    }
    return false;
  }

  /// Inisialisasi group ID untuk iOS (no-op di Android).
  static Future<void> init() async {
    await HomeWidget.setAppGroupId(_groupId);
  }
}

