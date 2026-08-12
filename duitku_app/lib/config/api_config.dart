class ApiConfig {
  /// Base URL backend DuitKu (API). Ganti sesuai domain deploy.
  static const String baseUrl = 'https://duitku.ordr.my.id';

  static String endpoint(String path) {
    return '$baseUrl/api/$path';
  }
}
