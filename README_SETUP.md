# SiAset — Setup Guide

## Prasyarat
- PHP 8.1+
- MySQL 5.7+ / MariaDB 10.3+
- Composer
- GD extension aktif (untuk QR PNG)

---

## 1. Setup Database

Import SQL berikut di phpMyAdmin atau MySQL CLI:

```sql
CREATE DATABASE IF NOT EXISTS `db_asset_management`
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Kemudian jalankan SQL tabel (lihat file `database_schema.sql` atau pesan chat sebelumnya).
**Urutan import tabel wajib:**
1. `departments`
2. `locations`
3. `users`
4. `vendors`
5. `assets`
6. `work_orders`
7. `maintenance_logs`

---

## 2. Konfigurasi .env

Edit file `.env` di root project:

```ini
CI_ENVIRONMENT = development
app.baseURL    = 'http://localhost:8080/'

database.default.hostname = localhost
database.default.database = db_asset_management
database.default.username = root
database.default.password =
database.default.DBDriver = MySQLi
database.default.port      = 3306
```

---

## 3. Install Dependencies

```bash
composer install
```

---

## 4. Jalankan Migration & Seeder

```bash
# Migration (buat tabel jika belum)
php spark migrate

# Seeder (isi data awal)
php spark db:seed DatabaseSeeder
```

**Akun default setelah seeder:**
| Role  | Email               | Password  |
|-------|---------------------|-----------|
| Admin | admin@siaset.com    | admin123  |
| User  | user@siaset.com     | user123   |

---

## 5. Buat Folder Upload

```bash
mkdir -p public/uploads/assets
mkdir -p public/uploads/qrcodes
```

Pastikan folder `writable/` bisa ditulis oleh web server (chmod 755).

---

## 6. Jalankan Server Lokal

```bash
php spark serve
```

Akses: **http://localhost:8080**

---

## 7. URL Penting

| URL | Keterangan |
|-----|------------|
| `/login` | Halaman login |
| `/admin/dashboard` | Dashboard KPI |
| `/admin/inventory` | Modul Inventory Aset (Tailwind) |
| `/admin/assets` | Modul Aset lama (Bootstrap) |
| `/admin/borrows` | Peminjaman |
| `/admin/qr/{id}/label` | Cetak label QR satu aset |
| `/admin/qr/labels?ids=1,2,3` | Cetak label QR massal |
| `/admin/qr/{id}` | Tampil QR PNG (embed di img) |
| `/admin/qr/{id}/download` | Download QR PNG |

---

## 8. Struktur File Penting

```
app/
├── Controllers/Admin/
│   ├── DashboardController.php   ← KPI dari semua modul
│   ├── InventoryAssetController.php
│   └── QrController.php
├── Models/
│   ├── DashboardKpiModel.php     ← Semua query KPI
│   ├── InventoryAssetModel.php   ← Query Builder murni
│   └── MaintenanceLogModel.php
├── Helpers/
│   ├── asset_helper.php
│   └── qrcode_helper.php         ← Wrapper chillerlan/php-qrcode
└── Views/
    ├── dashboard/kpi.php          ← Dashboard Tailwind
    └── inventory/
        ├── _layout.php
        ├── index.php
        ├── form.php
        ├── detail.php
        ├── qr_label.php          ← Label cetak 1 aset
        └── qr_labels.php         ← Label cetak massal
```
