// File generated for DuitKu with Firebase configuration.
import 'package:firebase_core/firebase_core.dart' show FirebaseOptions;
import 'package:flutter/foundation.dart'
    show defaultTargetPlatform, kIsWeb, TargetPlatform;

class DefaultFirebaseOptions {
  static FirebaseOptions get currentPlatform {
    if (kIsWeb) {
      return web;
    }
    switch (defaultTargetPlatform) {
      case TargetPlatform.android:
        return android;
      default:
        return android;
    }
  }

  static const FirebaseOptions web = FirebaseOptions(
    apiKey: 'AIzaSyCiNTNG3KN69plil_LHgYx8WA-FCtRLQiI',
    appId: '1:406377563596:android:039b7f290925a122788969',
    messagingSenderId: '406377563596',
    projectId: 'duitku-19896',
    storageBucket: 'duitku-19896.firebasestorage.app',
  );

  static const FirebaseOptions android = FirebaseOptions(
    apiKey: 'AIzaSyCiNTNG3KN69plil_LHgYx8WA-FCtRLQiI',
    appId: '1:406377563596:android:039b7f290925a122788969',
    messagingSenderId: '406377563596',
    projectId: 'duitku-19896',
    storageBucket: 'duitku-19896.firebasestorage.app',
  );
}
