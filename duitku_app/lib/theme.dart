import 'package:flutter/material.dart';

class AppColors {
  static const Color primary = Color(0xFF059669); // Modern Emerald Green
  static const Color primaryDark = Color(0xFF064E3B);
  static const Color primaryLight = Color(0xFF10B981);
  static const Color primarySubtle = Color(0xFFECFDF5);

  static const Color royalBlue = Color(0xFF2563EB); // Modern Royal / Cobalt Blue
  static const Color royalBlueDark = Color(0xFF1E3A8A);
  static const Color royalBlueLight = Color(0xFF3B82F6);
  static const Color royalBlueSubtle = Color(0xFFEFF6FF);

  static const Color income = Color(0xFF16A34A);
  static const Color incomeSubtle = Color(0xFFDCFCE7);
  static const Color expense = Color(0xFFDC2626);
  static const Color expenseSubtle = Color(0xFFFEE2E2);

  static const Color bg = Color(0xFFF8FAFC);
  static const Color card = Colors.white;
  static const Color border = Color(0xFFE2E8F0);
  static const Color borderLight = Color(0xFFF1F5F9);

  static const Color textPrimary = Color(0xFF0F172A);
  static const Color textSecondary = Color(0xFF475569);
  static const Color textMuted = Color(0xFF94A3B8);

  /// Modern soft shadow for cards
  static const List<BoxShadow> cardShadow = [
    BoxShadow(color: Color(0x080F172A), blurRadius: 16, offset: Offset(0, 4)),
    BoxShadow(color: Color(0x040F172A), blurRadius: 4, offset: Offset(0, 1)),
  ];

  static const List<BoxShadow> fabShadow = [
    BoxShadow(color: Color(0x402563EB), blurRadius: 18, offset: Offset(0, 6)),
  ];

  static const List<BoxShadow> blueGlowShadow = [
    BoxShadow(color: Color(0x352563EB), blurRadius: 20, offset: Offset(0, 8)),
  ];
}

ThemeData buildLightTheme() {
  final base = ThemeData(
    useMaterial3: true,
    colorScheme: ColorScheme.fromSeed(
      seedColor: AppColors.primary,
      primary: AppColors.primary,
      surface: AppColors.card,
      surfaceTint: Colors.transparent,
    ),
    scaffoldBackgroundColor: AppColors.bg,
    fontFamily: 'sans-serif',
  );
  return base.copyWith(
    appBarTheme: const AppBarTheme(
      backgroundColor: AppColors.bg,
      foregroundColor: AppColors.textPrimary,
      elevation: 0,
      scrolledUnderElevation: 0,
      centerTitle: false,
      titleTextStyle: TextStyle(
        color: AppColors.textPrimary,
        fontSize: 18,
        fontWeight: FontWeight.w800,
        letterSpacing: -0.3,
      ),
    ),
    cardTheme: const CardThemeData(
      color: AppColors.card,
      elevation: 0,
      margin: EdgeInsets.zero,
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.all(Radius.circular(18)),
        side: BorderSide(color: AppColors.border, width: 1),
      ),
    ),
    inputDecorationTheme: InputDecorationTheme(
      filled: true,
      fillColor: AppColors.card,
      contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
      border: OutlineInputBorder(
        borderRadius: BorderRadius.circular(14),
        borderSide: const BorderSide(color: AppColors.border),
      ),
      enabledBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(14),
        borderSide: const BorderSide(color: AppColors.border),
      ),
      focusedBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(14),
        borderSide: const BorderSide(color: AppColors.primary, width: 1.8),
      ),
    ),
    filledButtonTheme: FilledButtonThemeData(
      style: FilledButton.styleFrom(
        backgroundColor: AppColors.primary,
        foregroundColor: Colors.white,
        minimumSize: const Size.fromHeight(48),
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
        textStyle: const TextStyle(fontSize: 15, fontWeight: FontWeight.w700, letterSpacing: -0.2),
      ),
    ),
    snackBarTheme: SnackBarThemeData(
      behavior: SnackBarBehavior.floating,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
    ),
  );
}
