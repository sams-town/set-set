<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */

// ---------------------------------------------------------------
// Root → redirect ke login
// ---------------------------------------------------------------
$routes->get('/', function () {
    return redirect()->to('/login');
});

// ---------------------------------------------------------------
// Auth (tidak perlu filter login)
// ---------------------------------------------------------------
$routes->get('login',  'Auth\AuthController::login');
$routes->post('login', 'Auth\AuthController::loginProcess');
$routes->get('logout', 'Auth\AuthController::logout');

// ---------------------------------------------------------------
// QR Scan — Publik (tanpa auth, untuk scan QR di lapangan)
// ---------------------------------------------------------------
$routes->get('qr/(:segment)', 'QrScanController::view/$1');

// ---------------------------------------------------------------
// Admin — semua dilindungi filter 'auth'
// ---------------------------------------------------------------
$routes->group('admin', ['filter' => 'auth'], function ($routes) {

    // Dashboard
    $routes->get('dashboard', 'Admin\DashboardController::index');
    $routes->get('/',         'Admin\DashboardController::index'); // /admin → dashboard

    // ── QR Code ─────────────────────────────────────────────────
    $routes->get('qr/(:num)',          'Admin\QrController::png/$1');
    $routes->get('qr/(:num)/svg',      'Admin\QrController::svg/$1');
    $routes->get('qr/(:num)/download', 'Admin\QrController::download/$1');
    $routes->get('qr/(:num)/label',    'Admin\QrController::label/$1');
    $routes->get('qr/labels',          'Admin\QrController::labels');

    // ── Categories ──────────────────────────────────────────────
    $routes->get('categories',                'Admin\CategoryController::index');
    $routes->get('categories/new',            'Admin\CategoryController::create');
    $routes->post('categories',               'Admin\CategoryController::store');
    $routes->get('categories/(:num)/edit',    'Admin\CategoryController::edit/$1');
    $routes->post('categories/(:num)/update', 'Admin\CategoryController::update/$1');
    $routes->post('categories/(:num)/delete', 'Admin\CategoryController::delete/$1');

    // ── Locations ───────────────────────────────────────────────
    $routes->get('locations',                 'Admin\LocationController::index');
    $routes->get('locations/new',             'Admin\LocationController::create');
    $routes->post('locations',                'Admin\LocationController::store');
    $routes->get('locations/(:num)/edit',     'Admin\LocationController::edit/$1');
    $routes->post('locations/(:num)/update',  'Admin\LocationController::update/$1');
    $routes->post('locations/(:num)/delete',  'Admin\LocationController::delete/$1');

    // ── Borrows ─────────────────────────────────────────────────
    $routes->get('borrows',                   'Admin\BorrowController::index');
    $routes->get('borrows/new',               'Admin\BorrowController::create');
    $routes->post('borrows',                  'Admin\BorrowController::store');
    $routes->get('borrows/(:num)',            'Admin\BorrowController::show/$1');
    $routes->post('borrows/(:num)/return',    'Admin\BorrowController::returnAsset/$1');

    // ── Procurement ─────────────────────────────────────────────
    $routes->get('procurement',                          'Admin\ProcurementController::index');
    $routes->get('procurement/new',                      'Admin\ProcurementController::create');
    $routes->post('procurement',                         'Admin\ProcurementController::store');
    $routes->get('procurement/po/(:num)',                'Admin\ProcurementController::showPo/$1');
    $routes->post('procurement/po/(:num)/receive',       'Admin\ProcurementController::receivePo/$1');
    $routes->post('procurement/po/(:num)/register',      'Admin\ProcurementController::registerAsset/$1');
    $routes->get('procurement/(:num)',                   'Admin\ProcurementController::show/$1');
    $routes->get('procurement/(:num)/edit',              'Admin\ProcurementController::edit/$1');
    $routes->post('procurement/(:num)/update',           'Admin\ProcurementController::update/$1');
    $routes->post('procurement/(:num)/submit',           'Admin\ProcurementController::submit/$1');
    $routes->post('procurement/(:num)/approve',          'Admin\ProcurementController::approve/$1');
    $routes->post('procurement/(:num)/reject',           'Admin\ProcurementController::reject/$1');
    $routes->post('procurement/(:num)/set-rfq',          'Admin\ProcurementController::setRfq/$1');
    $routes->get('procurement/(:num)/po/new',            'Admin\ProcurementController::createPo/$1');
    $routes->post('procurement/(:num)/po',               'Admin\ProcurementController::storePo/$1');

    // ── Inventory Asset (skema baru + Tailwind) ─────────────────
    $routes->get('inventory',                    'Admin\InventoryAssetController::index');
    $routes->get('inventory/new',                'Admin\InventoryAssetController::create');
    $routes->post('inventory',                   'Admin\InventoryAssetController::store');
    $routes->get('inventory/(:num)',             'Admin\InventoryAssetController::show/$1');
    $routes->get('inventory/(:num)/edit',        'Admin\InventoryAssetController::edit/$1');
    $routes->post('inventory/(:num)/update',     'Admin\InventoryAssetController::update/$1');
    $routes->post('inventory/(:num)/delete',     'Admin\InventoryAssetController::delete/$1');

    // ── Work Orders ─────────────────────────────────────────────
    $routes->get('work-orders',                     'Admin\WorkOrderController::index');
    $routes->get('work-orders/new',                 'Admin\WorkOrderController::create');
    $routes->post('work-orders',                    'Admin\WorkOrderController::store');
    $routes->get('work-orders/(:num)',              'Admin\WorkOrderController::show/$1');
    $routes->get('work-orders/(:num)/edit',         'Admin\WorkOrderController::edit/$1');
    $routes->post('work-orders/(:num)/update',      'Admin\WorkOrderController::update/$1');
    $routes->post('work-orders/(:num)/delete',      'Admin\WorkOrderController::delete/$1');
    $routes->post('work-orders/(:num)/report',      'Admin\WorkOrderController::addReport/$1');

    // ── Corrective Maintenance ──────────────────────────────────
    $routes->get('cm',                'Admin\CorrectiveMaintenanceController::index');
    $routes->get('cm/asset/(:num)',   'Admin\CorrectiveMaintenanceController::showAsset/$1');

    // ── Preventive Maintenance ──────────────────────────────────
    $routes->get('pm',                        'Admin\PreventiveMaintenanceController::index');
    $routes->get('pm/new',                    'Admin\PreventiveMaintenanceController::create');
    $routes->post('pm',                       'Admin\PreventiveMaintenanceController::store');
    $routes->get('pm/calendar-json',          'Admin\PreventiveMaintenanceController::calendarJson');
    $routes->get('pm/(:num)',                 'Admin\PreventiveMaintenanceController::show/$1');
    $routes->get('pm/(:num)/edit',            'Admin\PreventiveMaintenanceController::edit/$1');
    $routes->post('pm/(:num)/update',         'Admin\PreventiveMaintenanceController::update/$1');
    $routes->post('pm/(:num)/delete',         'Admin\PreventiveMaintenanceController::delete/$1');
    $routes->post('pm/(:num)/mark-done',      'Admin\PreventiveMaintenanceController::markAsDone/$1');

    // ── Reports ─────────────────────────────────────────────────
    $routes->get('reports',       'Admin\ReportController::index');
    $routes->get('reports/print', 'Admin\ReportController::print');

    // ── Departments ─────────────────────────────────────────────
    $routes->get('departments',                 'Admin\DepartmentController::index');
    $routes->get('departments/new',             'Admin\DepartmentController::create');
    $routes->post('departments',                'Admin\DepartmentController::store');
    $routes->get('departments/(:num)/edit',     'Admin\DepartmentController::edit/$1');
    $routes->post('departments/(:num)/update',  'Admin\DepartmentController::update/$1');
    $routes->post('departments/(:num)/delete',  'Admin\DepartmentController::delete/$1');

    // ── Room Types ──────────────────────────────────────────────
    $routes->get('room-types',                  'Admin\RoomTypeController::index');
    $routes->get('room-types/new',              'Admin\RoomTypeController::create');
    $routes->post('room-types',                 'Admin\RoomTypeController::store');
    $routes->get('room-types/(:num)/edit',      'Admin\RoomTypeController::edit/$1');
    $routes->post('room-types/(:num)/update',   'Admin\RoomTypeController::update/$1');
    $routes->post('room-types/(:num)/delete',   'Admin\RoomTypeController::delete/$1');

    // ── Settings ────────────────────────────────────────────────
    $routes->group('settings', ['filter' => 'role:admin'], function ($routes) {
        $routes->get('/',             'Admin\SettingController::index');
        $routes->post('whatsapp',     'Admin\SettingController::saveWhatsapp');
        $routes->post('wa-test',      'Admin\SettingController::testWhatsapp');
        $routes->post('app',          'Admin\SettingController::saveApp');
    });

    // ── Users (admin only) ──────────────────────────────────────
    $routes->group('users', ['filter' => 'role:admin'], function ($routes) {
        $routes->get('/',                'Admin\UserController::index');
        $routes->get('new',              'Admin\UserController::create');
        $routes->post('/',               'Admin\UserController::store');
        $routes->get('(:num)/edit',      'Admin\UserController::edit/$1');
        $routes->post('(:num)/update',   'Admin\UserController::update/$1');
        $routes->post('(:num)/delete',   'Admin\UserController::delete/$1');
    });
});
