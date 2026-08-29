// ignore_for_file: avoid_print
import 'dart:io';

void main(List<String> args) {
  final pubspecFile = File('pubspec.yaml');
  if (!pubspecFile.existsSync()) {
    print('[ERROR] pubspec.yaml tidak ditemukan di direktori saat ini.');
    exit(1);
  }

  final content = pubspecFile.readAsStringSync();
  final versionRegex = RegExp(r'^version:\s*(\d+)\.(\d+)\.(\d+)\+(\d+)', multiLine: true);
  final match = versionRegex.firstMatch(content);

  if (match == null) {
    print('[ERROR] Format versi di pubspec.yaml tidak valid (contoh yang benar: version: 1.2.3+4).');
    exit(1);
  }

  int major = int.parse(match.group(1)!);
  int minor = int.parse(match.group(2)!);
  int patch = int.parse(match.group(3)!);
  int build = int.parse(match.group(4)!);

  final currentVer = '$major.$minor.$patch+$build';
  print('[INFO] Versi saat ini: $currentVer (v$major.$minor.$patch)');

  String? targetVer;
  bool isBump = true;

  if (args.contains('--no-bump')) {
    isBump = false;
    targetVer = '$major.$minor.$patch+$build';
  } else if (args.contains('--major')) {
    major += 1;
    minor = 0;
    patch = 0;
    build += 1;
    targetVer = '$major.$minor.$patch+$build';
  } else if (args.contains('--minor')) {
    minor += 1;
    patch = 0;
    build += 1;
    targetVer = '$major.$minor.$patch+$build';
  } else if (args.contains('--set') && args.length > args.indexOf('--set') + 1) {
    final custom = args[args.indexOf('--set') + 1];
    if (custom.contains('+')) {
      targetVer = custom;
    } else {
      build += 1;
      targetVer = '$custom+$build';
    }
  } else {
    // Default: bump patch
    patch += 1;
    build += 1;
    targetVer = '$major.$minor.$patch+$build';
  }

  if (isBump) {
    final cleanVer = targetVer.split('+')[0];
    print('[INFO] Meningkatkan versi ke: $targetVer (v$cleanVer)...');

    // Update pubspec.yaml
    final updatedPubspec = content.replaceFirst(
      versionRegex,
      'version: $targetVer',
    );
    pubspecFile.writeAsStringSync(updatedPubspec);
    print('[OK] pubspec.yaml berhasil diperbarui -> version: $targetVer');

    // Update fallbackVersion di lib/services/update_checker_service.dart jika ada
    final serviceFile = File('lib/services/update_checker_service.dart');
    if (serviceFile.existsSync()) {
      var serviceContent = serviceFile.readAsStringSync();
      final serviceRegex = RegExp(r"static const String fallbackVersion = '.*?';");
      if (serviceRegex.hasMatch(serviceContent)) {
        serviceContent = serviceContent.replaceFirst(
          serviceRegex,
          "static const String fallbackVersion = '$cleanVer';",
        );
        serviceFile.writeAsStringSync(serviceContent);
        print('[OK] update_checker_service.dart berhasil disinkronkan -> fallbackVersion = $cleanVer');
      }
    }
  }

  print('[OK] Selesai. Versi siap untuk release: $targetVer');
}
