<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */

// ─────────────────────────────────────────────────────────────────────────────
// AUTH (public)
// ─────────────────────────────────────────────────────────────────────────────
$routes->get('/migrate',  'MigrateController::index');
$routes->get('/login',    'AuthController::loginPage');
$routes->post('/login',   'AuthController::login');
$routes->get('/register', 'AuthController::registerPage');
$routes->post('/register','AuthController::register');
$routes->get('/logout',   'AuthController::logout');

// ─────────────────────────────────────────────────────────────────────────────
// PROTECTED (require login — via Auth filter)
// ─────────────────────────────────────────────────────────────────────────────
$routes->group('', ['filter' => 'auth'], function ($routes) {

    // Home / Dashboard
    $routes->get('/', 'HomeController::index');

    // Activity
    $routes->get('/activity', 'ActivityController::index');

    // Stats
    $routes->get('/stats', 'StatsController::index');

    // Belanja
    $routes->get('/belanja',       'BelanjaController::index');
    $routes->get('/belanja/sync',  'BelanjaController::sync');
    $routes->post('/belanja/sync', 'BelanjaController::sync');

    // Settings
    $routes->get('/settings',                          'SettingsController::index');
    $routes->post('/settings/currency',                'SettingsController::saveCurrency');
    $routes->post('/settings/budget',                  'SettingsController::saveBudget');
    $routes->post('/settings/profile',                 'SettingsController::saveProfile');
    $routes->post('/settings/avatar',                  'SettingsController::saveAvatar');
    $routes->post('/settings/category/store',          'SettingsController::storeCategory');
    $routes->post('/settings/category/delete/(:num)',  'SettingsController::deleteCategory/$1');

    // Transactions (AJAX)
    $routes->post('/transaction/store',         'TransactionController::store');
    $routes->post('/transaction/update/(:num)', 'TransactionController::update/$1');
    $routes->post('/transaction/delete/(:num)', 'TransactionController::delete/$1');
    $routes->get('/transaction/(:num)',         'TransactionController::show/$1');

    // Recurring
    $routes->post('/recurring/delete/(:num)', 'TransactionController::deleteRecurring/$1');
    $routes->get('/recurring',                'RecurringController::index');
    $routes->post('/recurring/store',         'RecurringController::store');
    $routes->post('/recurring/process',       'RecurringController::process');
    $routes->post('/recurring/execute/(:num)', 'RecurringController::execute/$1');
    $routes->post('/recurring/drop/(:num)',   'RecurringController::delete/$1');

    // Savings Goals
    $routes->get('/savings',                  'SavingsController::index');
    $routes->post('/savings/store',           'SavingsController::store');
    $routes->post('/savings/topup/(:num)',     'SavingsController::topup/$1');
    $routes->post('/savings/delete/(:num)',    'SavingsController::delete/$1');

    // Todo-List (Professional Task Manager)
    $routes->get('/todo',                      'TodoController::index');
    $routes->post('/todo/store',               'TodoController::store');
    $routes->post('/todo/toggle/(:num)',       'TodoController::toggle/$1');
    $routes->post('/todo/update/(:num)',       'TodoController::updateTask/$1');
    $routes->post('/todo/delete/(:num)',       'TodoController::delete/$1');

    // Export
    $routes->get('/export/csv', 'ExportController::csv');
    $routes->get('/export/pdf', 'ExportController::pdf');

    // Bills (server-side sync)
    $routes->get('/bills',                   'BillController::index');
    $routes->post('/bills/store',            'BillController::store');
    $routes->post('/bills/delete/(:segment)', 'BillController::delete/$1');

    // Import
    $routes->post('/import/csv', 'ImportController::csv');

    // Global Universal Search
    $routes->get('/search', 'SearchController::index');

    // Backup & Restore
    $routes->get('/backup/export',   'BackupController::export');
    $routes->post('/backup/restore', 'BackupController::restore');

    // Savings goal
    $routes->post('/settings/savings',        'SettingsController::saveSavings');
    $routes->post('/settings/savings/delete', 'SettingsController::deleteSavings');

    // Monthly note
    $routes->post('/settings/note', 'SettingsController::saveNote');

    // Hutang & Piutang
    $routes->get('/hutang',                    'DebtController::index');
    $routes->post('/hutang/store',             'DebtController::store');
    $routes->post('/hutang/pay/(:num)',        'DebtController::pay/$1');
    $routes->post('/hutang/settle/(:num)',     'DebtController::settle/$1');
    $routes->post('/hutang/delete/(:num)',     'DebtController::delete/$1');

    // Features Hub
    $routes->get('/features',                  'FeaturesController::index');
    $routes->get('/fitur',                     'FeaturesController::index');

    // Traveling & Trips
    $routes->get('/traveling',                 'TravelingController::index');
    $routes->get('/traveling/(:segment)',      'TravelingController::show/$1');
    $routes->post('/traveling/sync',           'TravelingController::sync');

    // Barang Storage Tracker
    $routes->get('/barang',                    'BarangController::index');
    $routes->post('/barang/store',             'BarangController::store');
    $routes->post('/barang/delete/(:segment)', 'BarangController::delete/$1');
    $routes->post('/barang/sync',              'BarangController::sync');

    // Wallets & Shared Wallets
    $routes->get('/wallets',                   'WalletController::index');
    $routes->post('/wallets/store',            'WalletController::store');
    $routes->post('/wallets/delete/(:num)',    'WalletController::delete/$1');
    $routes->post('/wallets/default/(:num)',   'WalletController::setDefault/$1');
    $routes->post('/wallets/transfer',         'WalletController::transfer');
    $routes->get('/wallets/members/(:num)',    'WalletController::getMembers/$1');
    $routes->post('/wallets/members/(:num)',   'WalletController::addMember/$1');
    $routes->post('/wallets/members/delete/(:num)', 'WalletController::removeMember/$1');

    // Kendaraan & Servis Tracker
    $routes->get('/kendaraan',                  'VehicleController::index');
    $routes->get('/kendaraan/(:num)',           'VehicleController::show/$1');
    $routes->post('/kendaraan/store',           'VehicleController::store');
    $routes->post('/kendaraan/delete/(:num)',   'VehicleController::delete/$1');
    $routes->post('/kendaraan/log/store',       'VehicleController::storeLog');
    $routes->post('/kendaraan/log/delete/(:num)','VehicleController::deleteLog/$1');

    $routes->get('/scan',                      'TransactionController::ocrPage');
    $routes->get('/scan-ocr',                  'TransactionController::ocrPage');
    $routes->post('/transaction/ocr-scan',     'TransactionController::ocrScan');
    $routes->get('/currency/rates',             'TravelingController::currencyRates');
    $routes->get('/currency/convert',           'TravelingController::currencyConvert');

    // Kasir Mini POS & Bisnis UMKM
    $routes->get('/pos',                        'PosController::index');
    $routes->post('/pos/checkout',              'PosController::checkout');
    $routes->get('/pos/orders',                 'PosController::orders');
    $routes->get('/pos/orders/poll',            'PosController::pollOrders');
    $routes->post('/pos/orders/update-status',  'PosController::updateOrderStatus');
    $routes->post('/pos/orders/pay',            'PosController::payOrder');
    $routes->get('/pos/order/receipt/(:num)',   'PosController::receipt/$1');
    $routes->get('/pos/shifts',                 'PosController::shifts');
    $routes->post('/pos/shifts/open',           'PosController::openShift');
    $routes->post('/pos/shifts/close',          'PosController::closeShift');
    $routes->get('/pos/shifts/active',          'PosController::activeShift');
    $routes->get('/pos/ingredients',            'PosIngredientController::index');
    $routes->post('/pos/ingredients/save',      'PosIngredientController::saveIngredient');
    $routes->post('/pos/ingredients/restock',   'PosIngredientController::restock');
    $routes->post('/pos/ingredients/delete/(:num)', 'PosIngredientController::deleteIngredient/$1');
    $routes->get('/pos/recipes/(:num)',         'PosIngredientController::getProductRecipe/$1');
    $routes->post('/pos/recipes/(:num)/save',   'PosIngredientController::saveProductRecipe/$1');
    $routes->get('/pos/qr',                     'PosController::qrPrint');
    $routes->post('/pos/store-profile',         'PosController::saveStoreProfile');
    $routes->get('/pos/kds',                    'PosController::kds');
    $routes->get('/pos/kds/poll',               'PosController::kdsPoll');
    $routes->get('/pos/vouchers',               'PosController::vouchers');
    $routes->post('/pos/vouchers/store',        'PosController::storeVoucher');
    $routes->post('/pos/vouchers/delete/(:num)','PosController::deleteVoucher/$1');
    $routes->get('/pos/loyalty',                'PosController::loyalty');
    $routes->get('/pos/products',               'PosController::products');
    $routes->post('/pos/products/store',        'PosController::storeProduct');
    $routes->post('/pos/products/adjust-stock', 'PosController::adjustStock');
    $routes->post('/pos/products/delete/(:num)','PosController::deleteProduct/$1');
    $routes->get('/pos/reports',                'PosController::reports');

    // TV & Live Streaming Web Player
    $routes->get('/tv',                         'TvController::index');
    $routes->get('/tv/chats',                   'TvController::chats');
    $routes->post('/tv/chats',                  'TvController::sendChat');

    // Layanan Darurat 24 Jam
    $routes->get('/emergency',                  'EmergencyController::index');
    $routes->get('/layanan-darurat',            'EmergencyController::index');

    // Kalkulator Zakat & Pajak
    $routes->get('/zakat-pajak',                'ZakatPajakController::index');
    $routes->get('/pajak-zakat',                'ZakatPajakController::index');

    // DuitKu Arcade Mini-Games Hub
    $routes->get('/arcade',                     'ArcadeController::index');
    $routes->get('/games',                      'ArcadeController::index');

    // Pusat Notifikasi & Pesan
    $routes->get('/notifications',              'NotificationController::index');
    $routes->get('/notifications/read/(:num)',  'NotificationController::markAsRead/$1');
    $routes->post('/notifications/read/(:num)', 'NotificationController::markAsRead/$1');
    $routes->get('/notifications/read-all',     'NotificationController::markAllAsRead');
    $routes->post('/notifications/read-all',    'NotificationController::markAllAsRead');
});

// ─────────────────────────────────────────────────────────────────────────────
// ADMINISTRATOR PANEL (Protected via 'admin' Filter)
// ─────────────────────────────────────────────────────────────────────────────
$routes->group('admin', ['filter' => 'admin'], function ($routes) {
    // Dashboard
    $routes->get('',                            'Admin\DashboardController::index');
    $routes->get('dashboard',                   'Admin\DashboardController::index');
    $routes->get('dashboard/poll',              'Admin\DashboardController::poll');

    // Notifications (Broadcast & Push to App)
    $routes->get('notifications',                    'Admin\NotificationController::index');
    $routes->post('notifications/store',             'Admin\NotificationController::store');
    $routes->post('notifications/store-update',      'Admin\NotificationController::storeUpdate');
    $routes->post('notifications/toggle-pin/(:num)', 'Admin\NotificationController::togglePin/$1');
    $routes->post('notifications/delete/(:num)',     'Admin\NotificationController::delete/$1');
    $routes->post('notifications/save-fcm',          'Admin\NotificationController::saveFcmConfig');
    $routes->get('notifications/test-fcm',           'Admin\NotificationController::testFcm');
    $routes->post('notifications/test-fcm',          'Admin\NotificationController::testFcm');

    // TV Streaming & M3U Playlist Manager
    $routes->get('tv',                          'Admin\TvController::index');
    $routes->post('tv/store',                   'Admin\TvController::store');
    $routes->post('tv/update/(:num)',           'Admin\TvController::update/$1');
    $routes->post('tv/toggle/(:num)',           'Admin\TvController::toggle/$1');
    $routes->post('tv/delete/(:num)',           'Admin\TvController::delete/$1');
    $routes->post('tv/import-m3u',              'Admin\TvController::importM3u');

    // User Management & Role Assignment
    $routes->get('users',                       'Admin\UserController::index');
    $routes->post('users/update-role/(:num)',   'Admin\UserController::updateRole/$1');
    $routes->post('users/reset-password/(:num)','Admin\UserController::resetPassword/$1');
    $routes->post('users/delete/(:num)',        'Admin\UserController::delete/$1');
});

// ─────────────────────────────────────────────────────────────────────────────
// PUBLIC MENU & MARKETPLACE ORDERING (Accessible by consumers via QR Code / URL)
// ─────────────────────────────────────────────────────────────────────────────
$routes->get('/menu/(:segment)',                           'PublicMenuController::index/$1');
$routes->get('/menu/(:segment)/status/(:segment)',          'PublicMenuController::orderStatus/$1/$2');
$routes->get('/menu/(:segment)/status-poll/(:segment)',     'PublicMenuController::pollStatus/$1/$2');
$routes->post('/menu/(:segment)/verify-voucher',           'PublicMenuController::verifyVoucher/$1');
$routes->get('/menu/(:segment)/stamps',                     'PublicMenuController::checkStamps/$1');
$routes->post('/menu/(:segment)/order',                     'PublicMenuController::placeOrder/$1');

// Aliases for Marketplace & Online Shop
$routes->get('/shop/(:segment)',                           'PublicMenuController::index/$1');
$routes->get('/shop/(:segment)/status/(:segment)',          'PublicMenuController::orderStatus/$1/$2');
$routes->get('/shop/(:segment)/status-poll/(:segment)',     'PublicMenuController::pollStatus/$1/$2');
$routes->post('/shop/(:segment)/verify-voucher',           'PublicMenuController::verifyVoucher/$1');
$routes->get('/shop/(:segment)/stamps',                     'PublicMenuController::checkStamps/$1');
$routes->post('/shop/(:segment)/order',                     'PublicMenuController::placeOrder/$1');

$routes->get('/toko/(:segment)',                           'PublicMenuController::index/$1');
$routes->get('/toko/(:segment)/status/(:segment)',          'PublicMenuController::orderStatus/$1/$2');
$routes->get('/toko/(:segment)/status-poll/(:segment)',     'PublicMenuController::pollStatus/$1/$2');
$routes->post('/toko/(:segment)/verify-voucher',           'PublicMenuController::verifyVoucher/$1');
$routes->get('/toko/(:segment)/stamps',                     'PublicMenuController::checkStamps/$1');
$routes->post('/toko/(:segment)/order',                     'PublicMenuController::placeOrder/$1');

// ─────────────────────────────────────────────────────────────────────────────
// MARKETPLACE — JUAL BELI & SEWA (Public & Member)
// ─────────────────────────────────────────────────────────────────────────────
$routes->get('/marketplace',                         'MarketplaceController::index');
$routes->get('/jual-beli-sewa',                      'MarketplaceController::index');
$routes->get('/marketplace/create',                  'MarketplaceController::create', ['filter' => 'auth']);
$routes->post('/marketplace/store',                 'MarketplaceController::store',  ['filter' => 'auth']);
$routes->get('/marketplace/edit/(:num)',             'MarketplaceController::edit/$1',   ['filter' => 'auth']);
$routes->post('/marketplace/update/(:num)',          'MarketplaceController::update/$1', ['filter' => 'auth']);
$routes->post('/marketplace/image/delete/(:num)',    'MarketplaceController::deleteImage/$1', ['filter' => 'auth']);
$routes->get('/marketplace/item/(:num)',             'MarketplaceController::detail/$1');
$routes->get('/p/(:num)',                            'MarketplaceController::detail/$1');
$routes->post('/marketplace/comment/(:num)',         'MarketplaceController::comment/$1', ['filter' => 'auth']);
$routes->post('/marketplace/order/(:num)',           'MarketplaceController::order/$1',   ['filter' => 'auth']);
$routes->post('/marketplace/order/delete/(:num)',    'MarketplaceController::deleteOrder/$1', ['filter' => 'auth']);
$routes->post('/marketplace/order-status/(:num)',    'MarketplaceController::updateOrderStatus/$1', ['filter' => 'auth']);
$routes->get('/marketplace/chat/messages',           'MarketplaceController::chatMessages',   ['filter' => 'auth']);
$routes->post('/marketplace/chat/send',              'MarketplaceController::sendChatMessage', ['filter' => 'auth']);
$routes->get('/marketplace/chat/unread-count',       'MarketplaceController::unreadChatCount', ['filter' => 'auth']);
$routes->get('/chat',                                'MarketplaceController::conversations',   ['filter' => 'auth']);
$routes->get('/pesan',                               'MarketplaceController::conversations',   ['filter' => 'auth']);
$routes->get('/friends/search',                      'MarketplaceController::searchFriends',   ['filter' => 'auth']);
$routes->post('/friends/request',                    'MarketplaceController::sendFriendRequest', ['filter' => 'auth']);
$routes->post('/friends/respond',                    'MarketplaceController::respondFriendRequest', ['filter' => 'auth']);
$routes->get('/chat/direct/messages',                'MarketplaceController::directChatMessages', ['filter' => 'auth']);
$routes->post('/chat/direct/send',                   'MarketplaceController::sendDirectChatMessage', ['filter' => 'auth']);
$routes->post('/marketplace/status/(:num)',          'MarketplaceController::updateStatus/$1', ['filter' => 'auth']);
$routes->post('/marketplace/delete/(:num)',          'MarketplaceController::delete/$1', ['filter' => 'auth']);
$routes->get('/u/(:segment)',                        'MarketplaceController::userStore/$1');

// Unduh APK & Panduan Instalasi
$routes->get('/download',                            'MarketplaceController::downloadPage');
$routes->get('/release',                             'MarketplaceController::downloadPage');



// ─────────────────────────────────────────────────────────────────────────────
// API (mobile app — Bearer token auth)
// ─────────────────────────────────────────────────────────────────────────────
$routes->group('api', function ($routes) {

    // Public: auth
    $routes->post('login',    'Api\AuthController::login');
    $routes->post('register', 'Api\AuthController::register');
    $routes->get('me',        'Api\AuthController::me',  ['filter' => 'api_auth']);
    $routes->post('logout',   'Api\AuthController::logout', ['filter' => 'api_auth']);

    // Protected
    $routes->group('', ['filter' => 'api_auth'], function ($routes) {

        // Notifications & Broadcasts
        $routes->get('notifications',               'Api\NotificationController::index');
        $routes->post('notifications/read/(:num)',  'Api\NotificationController::markAsRead/$1');
        $routes->post('notifications/read-all',     'Api\NotificationController::markAllAsRead');

        // TV & Live Streaming Channels
        $routes->get('tv/channels',                 'Api\TvController::index');
        $routes->get('tv/channels/(:num)',          'Api\TvController::show/$1');
        $routes->get('tv/chats',                    'Api\TvController::chats');
        $routes->post('tv/chats',                   'Api\TvController::sendChat');

        // Jellyfin Movie Streaming API
        $routes->get('jellyfin/movies',             'Api\JellyfinController::index');

        // Emergency Services Directory API
        $routes->get('emergency',                   'EmergencyController::apiList');

        // My Home / Barang API
        $routes->get('barang',                      'Api\BarangController::index');
        $routes->post('barang/store',               'Api\BarangController::store');
        $routes->post('barang/delete/(:segment)',   'Api\BarangController::delete/$1');
        $routes->post('barang/sync',                'Api\BarangController::sync');

        // Todos API
        $routes->get('todos',                       'TodoController::apiList');
        $routes->post('todos/store',                'TodoController::apiStore');
        $routes->post('todos/toggle/(:num)',        'TodoController::apiToggle/$1');
        $routes->post('todos/delete/(:num)',        'TodoController::apiDelete/$1');

        // Dashboard / home
        $routes->get('dashboard', 'Api\DashboardController::index');

        // Stats
        $routes->get('stats', 'Api\StatsController::index');

        // Categories
        $routes->get('categories', 'Api\CategoryController::index');
        $routes->post('categories/store', 'Api\CategoryController::store');
        $routes->post('categories/delete/(:num)', 'Api\CategoryController::delete/$1');

        // Transactions + activity
        $routes->get('activity', 'Api\ActivityController::index');
        $routes->get('transaction/(:num)', 'Api\TransactionController::show/$1');
        $routes->post('transaction/store', 'Api\TransactionController::store');
        $routes->post('transaction/update/(:num)', 'Api\TransactionController::update/$1');
        $routes->post('transaction/delete/(:num)', 'Api\TransactionController::delete/$1');
        $routes->post('transaction/ocr-scan', 'Api\TransactionController::ocrScan');
        $routes->post('recurring/delete/(:num)', 'Api\TransactionController::deleteRecurring/$1');
        $routes->get('recurring', 'Api\RecurringController::index');
        $routes->post('recurring/store', 'Api\RecurringController::store');
        $routes->post('recurring/process', 'Api\RecurringController::process');
        $routes->post('recurring/execute/(:num)', 'Api\RecurringController::execute/$1');

        // Belanja (offline-first sync)
        $routes->get('belanja',  'Api\BelanjaController::index');
        $routes->post('belanja/sync', 'Api\BelanjaController::sync');

        // Bills
        $routes->get('bills', 'Api\BillController::index');
        $routes->post('bills/store', 'Api\BillController::store');
        $routes->post('bills/delete/(:segment)', 'Api\BillController::delete/$1');

        // Debts
        $routes->get('hutang', 'Api\DebtController::index');
        $routes->post('hutang/store', 'Api\DebtController::store');
        $routes->post('hutang/pay/(:num)', 'Api\DebtController::pay/$1');
        $routes->post('hutang/settle/(:num)', 'Api\DebtController::settle/$1');
        $routes->post('hutang/delete/(:num)', 'Api\DebtController::delete/$1');

        // Wallets & Shared Wallets
        $routes->get('wallets', 'Api\WalletController::index');
        $routes->post('wallets/store', 'Api\WalletController::store');
        $routes->post('wallets/delete/(:num)', 'Api\WalletController::delete/$1');
        $routes->post('wallets/default/(:num)', 'Api\WalletController::setDefault/$1');
        $routes->post('wallets/transfer', 'Api\WalletController::transfer');
        $routes->get('wallets/members/(:num)', 'Api\WalletController::members/$1');
        $routes->post('wallets/members/(:num)', 'Api\WalletController::addMember/$1');
        $routes->post('wallets/members/delete/(:num)', 'Api\WalletController::removeMember/$1');

        // Traveling & Trips
        $routes->get('traveling', 'Api\TravelingController::index');
        $routes->post('traveling/sync', 'Api\TravelingController::sync');

        // Settings
        $routes->get('settings', 'Api\SettingController::index');
        $routes->post('settings/currency', 'Api\SettingController::saveCurrency');
        $routes->post('settings/budget', 'Api\SettingController::saveBudget');
        $routes->post('settings/profile', 'Api\SettingController::saveProfile');
        $routes->post('settings/avatar', 'Api\SettingController::saveAvatar');
        $routes->post('settings/savings', 'Api\SettingController::saveSavings');
        $routes->post('settings/savings/delete', 'Api\SettingController::deleteSavings');
        $routes->post('settings/note', 'Api\SettingController::saveNote');

        // Savings Goals
        $routes->get('savings', 'Api\SavingsController::index');
        $routes->post('savings/store', 'Api\SavingsController::store');
        $routes->post('savings/topup/(:num)', 'Api\SavingsController::topup/$1');
        $routes->post('savings/delete/(:num)', 'Api\SavingsController::delete/$1');

        // Vehicles & Maintenance Tracker
        $routes->get('vehicles',                    'Api\VehicleController::index');
        $routes->get('vehicles/(:num)',             'Api\VehicleController::show/$1');
        $routes->post('vehicles/store',             'Api\VehicleController::store');
        $routes->post('vehicles/delete/(:num)',     'Api\VehicleController::delete/$1');
        $routes->get('vehicles/logs',               'Api\VehicleController::logs');
        $routes->post('vehicles/logs/store',        'Api\VehicleController::storeLog');
        $routes->post('vehicles/logs/delete/(:num)','Api\VehicleController::deleteLog/$1');

        // Kasir Mini POS & Bisnis UMKM
        $routes->get('pos',                         'Api\PosController::index');
        $routes->post('pos/checkout',               'Api\PosController::checkout');
        $routes->get('pos/orders',                  'Api\PosController::orders');
        $routes->post('pos/orders/update-status',   'Api\PosController::updateOrderStatus');
        $routes->post('pos/orders/pay',             'Api\PosController::payOrder');
        $routes->get('pos/shifts',                  'Api\PosController::getShifts');
        $routes->get('pos/shifts/active',           'Api\PosController::getActiveShift');
        $routes->post('pos/shifts/open',            'Api\PosController::openShift');
        $routes->post('pos/shifts/close',           'Api\PosController::closeShift');
        $routes->get('pos/store-profile',           'Api\PosController::getStoreProfile');
        $routes->post('pos/store-profile',          'Api\PosController::saveStoreProfile');
        $routes->get('pos/vouchers',                'Api\PosController::getVouchers');
        $routes->post('pos/vouchers/store',         'Api\PosController::storeVoucher');
        $routes->post('pos/vouchers/delete/(:num)', 'Api\PosController::deleteVoucher/$1');
        $routes->get('pos/loyalty',                 'Api\PosController::getLoyaltyStamps');
        $routes->get('pos/products',                'Api\PosController::products');
        $routes->post('pos/products/store',         'Api\PosController::storeProduct');
        $routes->post('pos/products/adjust-stock',  'Api\PosController::adjustStock');
        $routes->post('pos/products/delete/(:num)', 'Api\PosController::deleteProduct/$1');
        $routes->get('pos/ingredients',             'Api\PosController::ingredients');
        $routes->post('pos/ingredients/store',      'Api\PosController::storeIngredient');
        $routes->post('pos/ingredients/restock',    'Api\PosController::restockIngredient');
        $routes->post('pos/ingredients/delete/(:num)','Api\PosController::deleteIngredient/$1');
        $routes->get('pos/recipes/(:num)',          'Api\PosController::productRecipe/$1');
        $routes->post('pos/recipes/(:num)/save',    'Api\PosController::saveProductRecipe/$1');
        $routes->get('pos/history',                 'Api\PosController::history');
        $routes->get('pos/order/(:num)',            'Api\PosController::orderDetail/$1');
        $routes->get('pos/reports',                 'Api\PosController::reports');

        // Currency converter
        $routes->get('currency/rates',              'Api\TravelingController::currencyRates');
        $routes->get('currency/convert',            'Api\TravelingController::currencyConvert');

        // Global Universal Search
        $routes->get('search',                      'Api\SearchController::index');

        // Backup & Restore
        $routes->get('backup/export',               'Api\BackupController::export');
        $routes->post('backup/restore',             'Api\BackupController::restore');

        // Marketplace (Jual Beli & Sewa)
        $routes->get('marketplace',                  'Api\MarketplaceController::index');
        $routes->get('marketplace/item/(:num)',      'Api\MarketplaceController::show/$1');
        $routes->post('marketplace/store',           'Api\MarketplaceController::store');
        $routes->post('marketplace/comment/(:num)',  'Api\MarketplaceController::comment/$1');
        $routes->post('marketplace/order/(:num)',        'Api\MarketplaceController::order/$1');
        $routes->post('marketplace/order/delete/(:num)', 'Api\MarketplaceController::deleteOrder/$1');
        $routes->post('marketplace/order-status/(:num)', 'Api\MarketplaceController::updateOrderStatus/$1');
        $routes->get('marketplace/my-listings',          'Api\MarketplaceController::myListings');
        $routes->post('marketplace/status/(:num)',   'Api\MarketplaceController::updateStatus/$1');
        $routes->post('marketplace/delete/(:num)',   'Api\MarketplaceController::delete/$1');
        $routes->get('marketplace/chat/messages',        'Api\MarketplaceController::chatMessages');
        $routes->post('marketplace/chat/send',           'Api\MarketplaceController::sendChatMessage');
        $routes->get('marketplace/chat/conversations',   'Api\MarketplaceController::chatConversations');

        // Friends & Direct WhatsApp-style Chat API
        $routes->get('friends',                           'Api\ChatController::friends');
        $routes->get('friends/requests',                  'Api\ChatController::requests');
        $routes->post('friends/request',                  'Api\ChatController::sendFriendRequest');
        $routes->post('friends/respond',                  'Api\ChatController::respondFriendRequest');
        $routes->get('friends/search',                    'Api\ChatController::searchUsers');
        $routes->get('chat/direct/messages',              'Api\ChatController::directMessages');
        $routes->post('chat/direct/send',                 'Api\ChatController::sendDirectMessage');
        $routes->get('chat/all-conversations',            'Api\ChatController::allConversations');
    });
});

// ─────────────────────────────────────────────────────────────────────────────
// USER STORE DOMAIN FALLBACK (e.g. domain/{username})
// ─────────────────────────────────────────────────────────────────────────────
$routes->get('(:segment)', 'MarketplaceController::userStore/$1');

