# ProGuard rules for DuitKu release build

# Flutter embedding & plugins
-keep class io.flutter.app.** { *; }
-keep class io.flutter.plugin.** { *; }
-keep class io.flutter.util.** { *; }
-keep class io.flutter.view.** { *; }
-keep class io.flutter.** { *; }
-keep class io.flutter.plugins.** { *; }
-keep class com.duitku.duitku_app.** { *; }

# Keep attributes needed by Flutter & reflection
-keepattributes *Annotation*
-keepattributes Signature
-keepattributes Exceptions
-keepattributes InnerClasses
-keepattributes EnclosingMethod

# Keep native methods
-keepclasseswithmembernames class * {
    native <methods>;
}

# Keep setters and getters
-keepclassmembers class * {
    void set*(***);
    *** get*();
}

# AndroidX lifecycle (known R8 bug workaround)
-keep class androidx.lifecycle.DefaultLifecycleObserver
-keep class androidx.lifecycle.** { *; }

# Google Play Services / ML Kit (used by mobile_scanner)
-keep class com.google.android.gms.** { *; }
-keep class com.google.mlkit.** { *; }
-keep class com.google.android.libraries.barhopper.** { *; }
-keep class com.google.photos.* { *; }
-dontwarn com.google.android.gms.**
-dontwarn com.google.mlkit.**

# ZXing / barcode scanning
-keep class com.google.zxing.** { *; }
-dontwarn com.google.zxing.**

# OKHttp / http package
-dontwarn okhttp3.**
-dontwarn okio.**

# home_widget plugin
-keep class es.antonborri.home_widget.** { *; }
-dontwarn org.xmlpull.v1.**
-dontwarn org.kxml2.io.**
-dontwarn android.content.res.**
-dontwarn org.slf4j.impl.StaticLoggerBinder
-keep class org.xmlpull.** { *; }
-keepclassmembers class org.xmlpull.** { *; }

# Google Play Core (deferred components / dynamic delivery) — not used by this app
-dontwarn com.google.android.play.core.splitcompat.**
-dontwarn com.google.android.play.core.splitinstall.**
-dontwarn com.google.android.play.core.tasks.**

# SharedPreferences / serialization
-keepclassmembers class * implements android.os.Parcelable {
    public static final android.os.Parcelable$Creator CREATOR;
}

# Keep JSON models used by reflection
-keepclassmembers class * {
    @com.google.gson.annotations.SerializedName <fields>;
}

# Enum values (used by ML Kit / mobile_scanner)
-keepclassmembers class * extends java.lang.Enum {
    <fields>;
    public static **[] values();
    public static ** valueOf(java.lang.String);
}

# Remove logging in release
-assumenosideeffects class android.util.Log {
    public static boolean isLoggable(java.lang.String, int);
    public static int v(...);
    public static int i(...);
    public static int w(...);
    public static int d(...);
    public static int e(...);
}
