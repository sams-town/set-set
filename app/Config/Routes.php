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

    // ── Master Data (Admin Only) ────────────────────────────────
    $routes->group('', ['filter' => 'role:admin'], function ($routes) {
        // Categories
        $routes->get('categories',                'Admin\CategoryController::index');
        $routes->get('categories/new',            'Admin\CategoryController::create');
        $routes->post('categories',               'Admin\CategoryController::store');
        $routes->get('categories/(:num)/edit',    'Admin\CategoryController::edit/$1');
        $routes->post('categories/(:num)/update', 'Admin\CategoryController::update/$1');
        $routes->post('categories/(:num)/delete', 'Admin\CategoryController::delete/$1');

        // Asset Templates
        $routes->get('asset-templates',                 'Admin\AssetTemplateController::index');
        $routes->get('asset-templates/new',             'Admin\AssetTemplateController::create');
        $routes->post('asset-templates',                'Admin\AssetTemplateController::store');
        $routes->get('asset-templates/(:num)/edit',     'Admin\AssetTemplateController::edit/$1');
        $routes->post('asset-templates/(:num)/update',  'Admin\AssetTemplateController::update/$1');
        $routes->post('asset-templates/(:num)/delete',  'Admin\AssetTemplateController::delete/$1');

        // Locations
        $routes->get('locations',                 'Admin\LocationController::index');
        $routes->get('locations/new',             'Admin\LocationController::create');
        $routes->post('locations',                'Admin\LocationController::store');
        $routes->get('locations/(:num)/edit',     'Admin\LocationController::edit/$1');
        $routes->post('locations/(:num)/update',  'Admin\LocationController::update/$1');
        $routes->post('locations/(:num)/delete',  'Admin\LocationController::delete/$1');

        // Departments
        $routes->get('departments',                 'Admin\DepartmentController::index');
        $routes->get('departments/new',             'Admin\DepartmentController::create');
        $routes->post('departments',                'Admin\DepartmentController::store');
        $routes->get('departments/(:num)/edit',     'Admin\DepartmentController::edit/$1');
        $routes->post('departments/(:num)/update',  'Admin\DepartmentController::update/$1');
        $routes->post('departments/(:num)/delete',  'Admin\DepartmentController::delete/$1');

        // Room Types
        $routes->get('room-types',                  'Admin\RoomTypeController::index');
        $routes->get('room-types/new',              'Admin\RoomTypeController::create');
        $routes->post('room-types',                 'Admin\RoomTypeController::store');
        $routes->get('room-types/(:num)/edit',      'Admin\RoomTypeController::edit/$1');
        $routes->post('room-types/(:num)/update',   'Admin\RoomTypeController::update/$1');
        $routes->post('room-types/(:num)/delete',   'Admin\RoomTypeController::delete/$1');

        // Reports
        $routes->get('reports',       'Admin\ReportController::index');
        $routes->get('reports/print', 'Admin\ReportController::print');
    });

    // ── Borrows (Admin & User Only) ─────────────────────────────
    $routes->group('borrows', ['filter' => 'role:admin,user'], function ($routes) {
        $routes->get('/',                   'Admin\BorrowController::index');
        $routes->get('new',                 'Admin\BorrowController::create');
        $routes->post('/',                  'Admin\BorrowController::store');
        $routes->get('(:num)',              'Admin\BorrowController::show/$1');
        $routes->post('(:num)/return',      'Admin\BorrowController::returnAsset/$1');
    });

    // ── Procurement ─────────────────────────────────────────────
    $routes->group('procurement', ['filter' => 'role:admin,user,pembelian'], function ($routes) {
        $routes->get('/',                          'Admin\ProcurementController::index');
        $routes->get('new',                        'Admin\ProcurementController::create');
        $routes->post('/',                         'Admin\ProcurementController::store');
        $routes->get('(:num)',                     'Admin\ProcurementController::show/$1');
        $routes->get('(:num)/edit',                'Admin\ProcurementController::edit/$1');
        $routes->post('(:num)/update',             'Admin\ProcurementController::update/$1');
        $routes->post('(:num)/submit',             'Admin\ProcurementController::submit/$1');

        // Only Admin and Pembelian can access approval and PO
        $routes->group('', ['filter' => 'role:admin,pembelian'], function ($routes) {
            $routes->post('(:num)/approve',          'Admin\ProcurementController::approve/$1');
            $routes->post('(:num)/reject',           'Admin\ProcurementController::reject/$1');
            $routes->post('(:num)/set-rfq',          'Admin\ProcurementController::setRfq/$1');
            $routes->get('(:num)/po/new',            'Admin\ProcurementController::createPo/$1');
            $routes->post('(:num)/po',               'Admin\ProcurementController::storePo/$1');
            $routes->get('po/(:num)',                'Admin\ProcurementController::showPo/$1');
            $routes->post('po/(:num)/receive',       'Admin\ProcurementController::receivePo/$1');
            $routes->post('po/(:num)/register',      'Admin\ProcurementController::registerAsset/$1');
        });
    });

    // ── Inventory Asset (Admin & User Only, Delete: Admin Only) ─
    $routes->group('inventory', ['filter' => 'role:admin,user'], function ($routes) {
        $routes->get('/',                    'Admin\InventoryAssetController::index');
        $routes->get('new',                  'Admin\InventoryAssetController::create');
        $routes->post('/',                   'Admin\InventoryAssetController::store');
        $routes->get('(:num)',               'Admin\InventoryAssetController::show/$1');
        $routes->get('(:num)/edit',          'Admin\InventoryAssetController::edit/$1');
        $routes->post('(:num)/update',       'Admin\InventoryAssetController::update/$1');
        $routes->post('(:num)/delete',       'Admin\InventoryAssetController::delete/$1', ['filter' => 'role:admin']);
    });

    // ── Work Orders (Admin, User & Technician, Delete: Admin Only)
    $routes->group('work-orders', ['filter' => 'role:admin,user,technician'], function ($routes) {
        $routes->get('/',                    'Admin\WorkOrderController::index');
        $routes->get('new',                  'Admin\WorkOrderController::create');
        $routes->post('/',                   'Admin\WorkOrderController::store');
        $routes->get('(:num)',               'Admin\WorkOrderController::show/$1');
        $routes->get('(:num)/edit',          'Admin\WorkOrderController::edit/$1');
        $routes->post('(:num)/update',       'Admin\WorkOrderController::update/$1');
        $routes->post('(:num)/delete',       'Admin\WorkOrderController::delete/$1', ['filter' => 'role:admin']);
        $routes->post('(:num)/report',       'Admin\WorkOrderController::addReport/$1');
    });

    // ── Corrective Maintenance (Admin & Technician Only) ────────
    $routes->group('cm', ['filter' => 'role:admin,technician'], function ($routes) {
        $routes->get('/',                'Admin\CorrectiveMaintenanceController::index');
        $routes->get('asset/(:num)',     'Admin\CorrectiveMaintenanceController::showAsset/$1');
    });

    // ── Preventive Maintenance (Admin & Technician Only) ────────
    $routes->group('pm', ['filter' => 'role:admin,technician'], function ($routes) {
        $routes->get('/',                        'Admin\PreventiveMaintenanceController::index');
        $routes->get('new',                      'Admin\PreventiveMaintenanceController::create');
        $routes->post('/',                       'Admin\PreventiveMaintenanceController::store');
        $routes->get('calendar-json',            'Admin\PreventiveMaintenanceController::calendarJson');
        $routes->get('(:num)',                   'Admin\PreventiveMaintenanceController::show/$1');
        $routes->get('(:num)/edit',              'Admin\PreventiveMaintenanceController::edit/$1');
        $routes->post('(:num)/update',           'Admin\PreventiveMaintenanceController::update/$1');
        $routes->post('(:num)/delete',           'Admin\PreventiveMaintenanceController::delete/$1', ['filter' => 'role:admin']);
        $routes->post('(:num)/mark-done',        'Admin\PreventiveMaintenanceController::markAsDone/$1');
    });
    
    // ── Maintenance Checklist (Admin & Technician Only) ─────────
    $routes->group('checklist', ['filter' => 'role:admin,technician'], function ($routes) {
        $routes->get('/',                       'Admin\MaintenanceChecklistController::index');
        $routes->get('new/(:segment)',          'Admin\MaintenanceChecklistController::new/$1');
        $routes->get('(:num)/edit',             'Admin\MaintenanceChecklistController::edit/$1');
        $routes->post('(:num)',                 'Admin\MaintenanceChecklistController::update/$1');
        $routes->get('(:num)',                  'Admin\MaintenanceChecklistController::show/$1');
    });

    // ── Settings (Admin only) ───────────────────────────────────
    $routes->group('settings', ['filter' => 'role:admin'], function ($routes) {
        $routes->get('/',             'Admin\SettingController::index');
        $routes->post('whatsapp',     'Admin\SettingController::saveWhatsapp');
        $routes->post('wa-test',      'Admin\SettingController::testWhatsapp');
        $routes->post('app',          'Admin\SettingController::saveApp');
    });

    // ── Users (Admin only) ──────────────────────────────────────
    $routes->group('users', ['filter' => 'role:admin'], function ($routes) {
        $routes->get('/',                'Admin\UserController::index');
        $routes->get('new',              'Admin\UserController::create');
        $routes->post('/',               'Admin\UserController::store');
        $routes->get('(:num)/edit',      'Admin\UserController::edit/$1');
        $routes->post('(:num)/update',   'Admin\UserController::update/$1');
        $routes->post('(:num)/delete',   'Admin\UserController::delete/$1');
    });
});

