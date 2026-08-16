import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:provider/provider.dart';
import 'package:duitku_app/screens/features_screen.dart';
import 'package:duitku_app/screens/pos/pos_cashier_screen.dart';
import 'package:duitku_app/screens/pos/pos_products_screen.dart';
import 'package:duitku_app/screens/pos/pos_reports_screen.dart';
import 'package:duitku_app/providers/auth_provider.dart';
import 'package:duitku_app/providers/app_data_provider.dart';

void main() {
  testWidgets('Test FeaturesScreen renders all sections without overflow', (tester) async {
    tester.view.physicalSize = const Size(1080, 2400);
    tester.view.devicePixelRatio = 2.0;

    await tester.pumpWidget(
      MultiProvider(
        providers: [
          ChangeNotifierProvider(create: (_) => AuthProvider()),
          ChangeNotifierProvider(create: (_) => AppDataProvider()),
        ],
        child: const MaterialApp(
          home: FeaturesScreen(),
        ),
      ),
    );
    await tester.pumpAndSettle();

    expect(find.text('Layanan & Fitur'), findsOneWidget);
    expect(find.text('MANAJEMEN KEUANGAN'), findsOneWidget);
    expect(find.text('GAYA HIDUP & BELANJA'), findsOneWidget);
    expect(find.text('BISNIS & USAHA (KASIR UMKM)'), findsOneWidget);
    expect(find.text('Kasir Mini (POS)'), findsOneWidget);
    expect(find.text('Katalog & Stok'), findsOneWidget);
    expect(find.text('Laporan Laba Rugi'), findsOneWidget);

    addTearDown(tester.view.resetPhysicalSize);
  });

  testWidgets('Test PosCashierScreen and PosProductsScreen pump without crash', (tester) async {
    await tester.pumpWidget(
      const MaterialApp(
        home: PosCashierScreen(),
      ),
    );
    await tester.pump();
    expect(find.text('☕ Kasir Mini POS'), findsOneWidget);

    await tester.pumpWidget(
      const MaterialApp(
        home: PosProductsScreen(),
      ),
    );
    await tester.pump();
    expect(find.text('📦 Katalog & Stok Produk'), findsOneWidget);

    await tester.pumpWidget(
      const MaterialApp(
        home: PosReportsScreen(),
      ),
    );
    await tester.pump();
    expect(find.text('📊 Laporan Laba Rugi POS'), findsOneWidget);
  });
}
