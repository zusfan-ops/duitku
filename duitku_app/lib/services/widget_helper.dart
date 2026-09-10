import 'dart:developer';

import 'package:home_widget/home_widget.dart';

import '../models/dashboard.dart';
import '../utils/format.dart';
import 'api_service.dart';

/// Helper untuk sinkronisasi data ke Android home screen widget.
///
/// Mendukung 3 ukuran widget:
/// - [DuitkuWidgetSmall]  — compact 4x1
/// - [DuitkuWidgetProvider] — standard 4x2
/// - [DuitkuWidgetLarge]  — dashboard 4x4
///
/// Semua widget membaca data dari SharedPreferences yang sama
/// via [HomeWidget.saveWidgetData].
class WidgetHelper {
  static const _groupId = 'com.duitku.duitku_app.widget';

  // Provider names (untuk Android broadcast)
  static const _smallName = 'DuitkuWidgetSmall';
  static const _standardName = 'DuitkuWidgetProvider';
  static const _largeName = 'DuitkuWidgetLarge';
  static const _pkg = 'com.duitku.duitku_app';

  /// Key data yang dibaca oleh semua native widget.
  static const _kBalance = 'widget_balance';
  static const _kIncome = 'widget_income';
  static const _kExpense = 'widget_expense';
  static const _kMonth = 'widget_month';
  static const _kSymbol = 'widget_symbol';

  // Keys khusus widget large (top kategori)
  static const _kCatName1 = 'widget_cat_name_1';
  static const _kCatName2 = 'widget_cat_name_2';
  static const _kCatName3 = 'widget_cat_name_3';
  static const _kCatAmount1 = 'widget_cat_amount_1';
  static const _kCatAmount2 = 'widget_cat_amount_2';
  static const _kCatAmount3 = 'widget_cat_amount_3';
  static const _kCatColor1 = 'widget_cat_color_1';
  static const _kCatColor2 = 'widget_cat_color_2';
  static const _kCatColor3 = 'widget_cat_color_3';

  /// Ambil snapshot dashboard dan push ke semua native widget.
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

      // Simpan top kategori untuk widget large
      await _saveTopCategories(data);

      // Update semua ukuran widget
      await _updateAllWidgets();
    } catch (e, st) {
      log('Widget update failed: $e', stackTrace: st);
    }
  }

  /// Simpan top 3 kategori pengeluaran ke SharedPreferences.
  static Future<void> _saveTopCategories(DashboardData data) async {
    final topCats = data.topCategories;
    if (topCats.isEmpty) {
      // Clear category data
      for (final key in [
        _kCatName1, _kCatName2, _kCatName3,
        _kCatAmount1, _kCatAmount2, _kCatAmount3,
        _kCatColor1, _kCatColor2, _kCatColor3,
      ]) {
        await HomeWidget.saveWidgetData(key, '');
      }
      return;
    }

    final colors = [
      0xFF16A34A, // green-600
      0xFF2563EB, // blue-600
      0xFFF59E0B, // amber-500
    ];

    for (int i = 0; i < 3; i++) {
      if (i < topCats.length) {
        final cat = topCats[i];
        final name = cat['name']?.toString() ?? '';
        final amount = cat['amount'] ?? cat['total'] ?? 0;
        final symbol = data.symbol;
        final amountStr = Fmt.money(
          double.tryParse('$amount') ?? 0,
          symbol: symbol,
        );
        final color = cat['color'] != null
            ? int.tryParse('${cat['color']}'.replaceFirst('#', '0xFF')) ??
                colors[i]
            : colors[i];

        await HomeWidget.saveWidgetData('widget_cat_name_${i + 1}', name);
        await HomeWidget.saveWidgetData('widget_cat_amount_${i + 1}', amountStr);
        await HomeWidget.saveWidgetData('widget_cat_color_${i + 1}', color);
      } else {
        await HomeWidget.saveWidgetData('widget_cat_name_${i + 1}', '');
        await HomeWidget.saveWidgetData('widget_cat_amount_${i + 1}', '');
        await HomeWidget.saveWidgetData('widget_cat_color_${i + 1}', 0);
      }
    }
  }

  /// Trigger update ke semua jenis widget.
  static Future<void> _updateAllWidgets() async {
    await HomeWidget.updateWidget(
      name: _smallName,
      androidName: _smallName,
      qualifiedAndroidName: '$_pkg.$_smallName',
    );
    await HomeWidget.updateWidget(
      name: _standardName,
      androidName: _standardName,
      qualifiedAndroidName: '$_pkg.$_standardName',
    );
    await HomeWidget.updateWidget(
      name: _largeName,
      androidName: _largeName,
      qualifiedAndroidName: '$_pkg.$_largeName',
    );
  }

  /// Request pin widget otomatis ke Layar Utama (Android 8.0+).
  static Future<bool> requestPinWidget() async {
    try {
      final supported = await HomeWidget.isRequestPinWidgetSupported();
      if (supported == true) {
        await updateDashboardWidget();
        await HomeWidget.requestPinWidget(
          name: _standardName,
          androidName: _standardName,
          qualifiedAndroidName: '$_pkg.$_standardName',
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
