param (
    [string]$BumpType = "patch", # patch | minor | major | none
    [string]$CustomVersion = ""
)

$ErrorActionPreference = "Stop"

# Pindah ke folder duitku_app jika dijalankan dari root
$ScriptDir = Split-Path -Parent $MyInvocation.MyCommand.Path
$AppDir = Resolve-Path "$ScriptDir\.."
Set-Location $AppDir

Write-Host "==========================================" -ForegroundColor Cyan
Write-Host " [DuitKu Mobile] Auto Release Builder     " -ForegroundColor Green
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

# 3. Build APK Release (Split per ABI & Universal)
Write-Host "`n[3/4] Melakukan build APK release..." -ForegroundColor Yellow
flutter build apk --release --split-per-abi
flutter build apk --release

# 4. Merapikan file APK ke folder releases
Write-Host "`n[4/4] Mengorganisir file rilis APK..." -ForegroundColor Yellow
$ReleaseDir = "$AppDir\releases\v$VersionName"
if (-not (Test-Path $ReleaseDir)) {
    New-Item -ItemType Directory -Path $ReleaseDir -Force | Out-Null
}

$ApkSourceDir = "$AppDir\build\app\outputs\flutter-apk"

# Copy Universal APK
if (Test-Path "$ApkSourceDir\app-release.apk") {
    Copy-Item "$ApkSourceDir\app-release.apk" "$ReleaseDir\DuitKu-v$VersionName-universal.apk" -Force
    Write-Host "  -> Universal APK: $ReleaseDir\DuitKu-v$VersionName-universal.apk" -ForegroundColor Green
}

# Copy arm64-v8a APK (Rekomendasi untuk sebagian besar HP modern)
if (Test-Path "$ApkSourceDir\app-arm64-v8a-release.apk") {
    Copy-Item "$ApkSourceDir\app-arm64-v8a-release.apk" "$ReleaseDir\DuitKu-v$VersionName-arm64-v8a.apk" -Force
    Write-Host "  -> ARM64 APK:     $ReleaseDir\DuitKu-v$VersionName-arm64-v8a.apk" -ForegroundColor Green
}

# Copy armeabi-v7a APK (HP 32-bit lama)
if (Test-Path "$ApkSourceDir\app-armeabi-v7a-release.apk") {
    Copy-Item "$ApkSourceDir\app-armeabi-v7a-release.apk" "$ReleaseDir\DuitKu-v$VersionName-armeabi-v7a.apk" -Force
    Write-Host "  -> ARMv7 APK:     $ReleaseDir\DuitKu-v$VersionName-armeabi-v7a.apk" -ForegroundColor Green
}

Write-Host "`n=======================================================" -ForegroundColor Cyan
Write-Host " BUILD RELEASE v$VersionName SELESAI DAN SUKSES!        " -ForegroundColor Green
Write-Host "=======================================================" -ForegroundColor Cyan
Write-Host "File APK siap diupload ke GitHub Releases:" -ForegroundColor Yellow
Write-Host "Folder: $ReleaseDir`n" -ForegroundColor White
Write-Host "Langkah upload rilis ke GitHub:" -ForegroundColor Cyan
Write-Host "1. Buka https://github.com/zusfan-ops/duitku/releases/new"
Write-Host "2. Buat tag baru: v$VersionName"
Write-Host "3. Beri judul rilis: DuitKu v$VersionName"
Write-Host "4. Upload file APK dari folder $ReleaseDir"
Write-Host "5. Publikasikan Release!"
