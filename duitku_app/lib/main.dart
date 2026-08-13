import 'package:flutter/material.dart';
import 'package:intl/date_symbol_data_local.dart';
import 'package:provider/provider.dart';

import 'providers/app_data_provider.dart';
import 'providers/auth_provider.dart';
import 'providers/travel_provider.dart';
import 'screens/auth/login_screen.dart';
import 'screens/home_screen.dart';
import 'screens/splash_screen.dart';
import 'theme.dart';

void main() async {
  WidgetsFlutterBinding.ensureInitialized();
  await initializeDateFormatting('id_ID', null);
  runApp(const DuitkuApp());
}

class DuitkuApp extends StatelessWidget {
  const DuitkuApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MultiProvider(
      providers: [
        ChangeNotifierProvider(create: (_) => AuthProvider()..init()),
        ChangeNotifierProvider(create: (_) => AppDataProvider()),
        ChangeNotifierProvider(create: (_) => TravelProvider()..ensureLoaded()),
      ],
      child: Consumer<AuthProvider>(
        builder: (context, auth, _) {
          return MaterialApp(
            title: 'DuitKu',
            debugShowCheckedModeBanner: false,
            theme: buildLightTheme(),
            home: _resolveHome(auth),
          );
        },
      ),
    );
  }

  Widget _resolveHome(AuthProvider auth) {
    if (auth.initializing) return const SplashScreen();
    if (!auth.isLoggedIn) return const LoginScreen();
    return const HomeScreen();
  }
}
