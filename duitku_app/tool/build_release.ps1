param (
    [string]$BumpType = "patch", # patch | minor | major | none
    [string]$CustomVersion = "",
    [switch]$IncludeAppBundle
)

$ErrorActionPreference = "Stop"

# Pindah ke folder duitku_app jika dijalankan dari root
$ScriptDir = Split-Path -Parent $MyInvocation.MyCommand.Path
$AppDir = Resolve-Path "$ScriptDir\.."
Set-Location $AppDir

Write-Host "==========================================" -ForegroundColor Cyan
Write-Host " [DuitKu Mobile] Auto Release & Split APK " -ForegroundColor Green
Write-Host "==========================================" -ForegroundColor Cyan

# 1. Bump Versi
if ($CustomVersion -ne "") {
    Write-Host "`n[1/4] Mengatur versi custom: $CustomVersion..." -ForegroundColor Yellow
    dart run tool/bump_version.dart --set $CustomVersion
} elseif ($BumpType -eq "none") {
    Write-Host "`n[1/4] Mempertahankan versi saat ini..." -ForegroundColor Yellow
    dart run tool/bump_version.dart --no-bump
} elseif ($BumpType -eq "minor") {
    Write-Host "`n[1/4] Meningkatkan versi minor..." -ForegroundColor Yellow
    dart run tool/bump_version.dart --minor
} elseif ($BumpType -eq "major") {
    Write-Host "`n[1/4] Meningkatkan versi major..." -ForegroundColor Yellow
    dart run tool/bump_version.dart --major
} else {
    Write-Host "`n[1/4] Meningkatkan versi patch otomatis..." -ForegroundColor Yellow
    dart run tool/bump_version.dart
}

# Ambil versi terbaru dari pubspec.yaml
$Pubspec = Get-Content "pubspec.yaml" -Raw
if ($Pubspec -match "version:\s*([0-9\.]+)\+([0-9]+)") {
    $VersionName = $matches[1]
    $BuildNumber = $matches[2]
} else {
    Write-Error "Gagal membaca versi dari pubspec.yaml"
    exit 1
}

Write-Host "`n[Target Versi] v$VersionName (Build $BuildNumber)" -ForegroundColor Green

# 2. Flutter Pub Get
Write-Host "`n[2/4] Menjalankan flutter pub get..." -ForegroundColor Yellow
flutter pub get

$ReleaseDir = "$AppDir\releases\v$VersionName"
if (-not (Test-Path $ReleaseDir)) {
    New-Item -ItemType Directory -Path $ReleaseDir -Force | Out-Null
}

$ApkSourceDir = "$AppDir\build\app\outputs\flutter-apk"

# 3. Build APK Split per ABI (Ukuran kecil, hemat kuota pengguna)
Write-Host "`n[3/4] Melakukan build APK SPLIT per ABI (ARM64, ARMv7, x86_64)..." -ForegroundColor Yellow
flutter build apk --release --split-per-abi

if (Test-Path "$ApkSourceDir\app-arm64-v8a-release.apk") {
    Copy-Item "$ApkSourceDir\app-arm64-v8a-release.apk" "$ReleaseDir\DuitKu-v$VersionName-arm64-v8a.apk" -Force
    Write-Host "  -> [SPLIT] ARM64-v8a APK: $ReleaseDir\DuitKu-v$VersionName-arm64-v8a.apk" -ForegroundColor Green
}
if (Test-Path "$ApkSourceDir\app-armeabi-v7a-release.apk") {
    Copy-Item "$ApkSourceDir\app-armeabi-v7a-release.apk" "$ReleaseDir\DuitKu-v$VersionName-armeabi-v7a.apk" -Force
    Write-Host "  -> [SPLIT] ARMv7-a APK:   $ReleaseDir\DuitKu-v$VersionName-armeabi-v7a.apk" -ForegroundColor Green
}
if (Test-Path "$ApkSourceDir\app-x86_64-release.apk") {
    Copy-Item "$ApkSourceDir\app-x86_64-release.apk" "$ReleaseDir\DuitKu-v$VersionName-x86_64.apk" -Force
    Write-Host "  -> [SPLIT] x86_64 APK:    $ReleaseDir\DuitKu-v$VersionName-x86_64.apk" -ForegroundColor Green
}

# 4. Build APK Universal (Mendukung semua arsitektur sekaligus)
Write-Host "`n[4/4] Melakukan build APK UNIVERSAL (All-in-one)..." -ForegroundColor Yellow
flutter build apk --release

if (Test-Path "$ApkSourceDir\app-release.apk") {
    Copy-Item "$ApkSourceDir\app-release.apk" "$ReleaseDir\DuitKu-v$VersionName-universal.apk" -Force
    Write-Host "  -> [UNIVERSAL] Full APK:  $ReleaseDir\DuitKu-v$VersionName-universal.apk" -ForegroundColor Green
}

# Optional: Build App Bundle jika ada flag -IncludeAppBundle
if ($IncludeAppBundle) {
    Write-Host "`n[BONUS] Melakukan build Android App Bundle (.aab)..." -ForegroundColor Yellow
    flutter build appbundle --release
    $AabSource = "$AppDir\build\app\outputs\bundle\release\app-release.aab"
    if (Test-Path $AabSource) {
        Copy-Item $AabSource "$ReleaseDir\DuitKu-v$VersionName.aab" -Force
        Write-Host "  -> [BUNDLE] App Bundle:   $ReleaseDir\DuitKu-v$VersionName.aab" -ForegroundColor Green
    }
}

Write-Host "`n=======================================================" -ForegroundColor Cyan
Write-Host " BUILD RELEASE & SPLIT v$VersionName SUKSES LENGKAP!   " -ForegroundColor Green
Write-Host "=======================================================" -ForegroundColor Cyan
Write-Host "File APK (Split & Universal) siap diupload ke GitHub:" -ForegroundColor Yellow
Write-Host "Folder: $ReleaseDir`n" -ForegroundColor White
Write-Host "Daftar File yang Dihasilkan:" -ForegroundColor Cyan
Get-ChildItem $ReleaseDir | ForEach-Object {
    $SizeMB = [math]::Round($_.Length / 1MB, 2)
    Write-Host "  * $($_.Name) ($SizeMB MB)" -ForegroundColor White
}
Write-Host "`nLangkah upload rilis ke GitHub:" -ForegroundColor Cyan
Write-Host "1. Buka https://github.com/zusfan-ops/duitku/releases/new"
Write-Host "2. Buat tag baru: v$VersionName"
Write-Host "3. Beri judul rilis: DuitKu v$VersionName"
Write-Host "4. Upload semua file APK dari folder $ReleaseDir"
Write-Host "5. Publikasikan Release!"
