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

    // Export
    $routes->get('/export/csv', 'ExportController::csv');
    $routes->get('/export/pdf', 'ExportController::pdf');

    // Bills (server-side sync)
    $routes->get('/bills',                   'BillController::index');
    $routes->post('/bills/store',            'BillController::store');
    $routes->post('/bills/delete/(:segment)', 'BillController::delete/$1');

    // Import
    $routes->post('/import/csv', 'ImportController::csv');

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

    // Wallets
    $routes->get('/wallets',                   'WalletController::index');
    $routes->post('/wallets/store',            'WalletController::store');
    $routes->post('/wallets/delete/(:num)',    'WalletController::delete/$1');
    $routes->post('/wallets/default/(:num)',   'WalletController::setDefault/$1');
    $routes->post('/wallets/transfer',         'WalletController::transfer');
});

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
        $routes->post('recurring/delete/(:num)', 'Api\TransactionController::deleteRecurring/$1');

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

        // Wallets
        $routes->get('wallets', 'Api\WalletController::index');
        $routes->post('wallets/store', 'Api\WalletController::store');
        $routes->post('wallets/delete/(:num)', 'Api\WalletController::delete/$1');
        $routes->post('wallets/default/(:num)', 'Api\WalletController::setDefault/$1');
        $routes->post('wallets/transfer', 'Api\WalletController::transfer');

        // Settings
        $routes->get('settings', 'Api\SettingController::index');
        $routes->post('settings/currency', 'Api\SettingController::saveCurrency');
        $routes->post('settings/budget', 'Api\SettingController::saveBudget');
        $routes->post('settings/profile', 'Api\SettingController::saveProfile');
        $routes->post('settings/avatar', 'Api\SettingController::saveAvatar');
        $routes->post('settings/savings', 'Api\SettingController::saveSavings');
        $routes->post('settings/savings/delete', 'Api\SettingController::deleteSavings');
        $routes->post('settings/note', 'Api\SettingController::saveNote');
    });
});
