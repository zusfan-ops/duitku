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
});
