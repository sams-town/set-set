<?php

namespace App\Controllers;

use App\Models\InventoryAssetModel;
use App\Models\MaintenanceLogModel;
use App\Models\WorkOrderModel;

/**
 * QrScanController — Public View (tanpa auth)
 *
 * Route: GET /qr/{asset_code}
 *
 * Saat QR discan, pengguna (bahkan yang tidak login) dapat langsung melihat:
 * 1. Nama Asset
 * 2. Merk, Type, Serial Number
 * 3. Riwayat Perbaikan (apa yang pernah rusak, diganti, dll)
 * 4. Riwayat Preventive Maintenance
 * 5. Masa Garansi (terhitung otomatis)
 * 6. QR Code (untuk print ulang)
 */
class QrScanController extends BaseController
{
    protected InventoryAssetModel $assetModel;
    protected MaintenanceLogModel $logModel;
    protected WorkOrderModel      $woModel;

    protected $db;

    public function __construct()
    {
        $this->assetModel = new InventoryAssetModel();
        $this->logModel   = new MaintenanceLogModel();
        $this->db         = \Config\Database::connect();
    }

    /**
     * GET /qr/{asset_code}
     * View publik untuk scan QR
     */
    public function view(string $assetCode)
    {
        // Cari aset by asset_code
        $asset = $this->db->table('assets a')
            ->select('a.*, 
                      d.name AS department_name,
                      l.name AS location_name, l.building,
                      v.name AS vendor_name')
            ->join('departments d', 'd.id = a.department_id', 'left')
            ->join('locations l',   'l.id = a.location_id',   'left')
            ->join('vendors v',     'v.id = a.vendor_id',     'left')
            ->where('LOWER(a.asset_code)', strtolower($assetCode))
            ->where('a.deleted_at', null)
            ->get()
            ->getRowArray();

        if (! $asset) {
            return view('qr_scan/not_found', ['asset_code' => $assetCode]);
        }

        // 3. Riwayat Perbaikan (corrective maintenance)
        // Filter log action: perbaikan_mulai, perbaikan_selesai
        $repairHistory = $this->db->table('maintenance_logs ml')
            ->select('ml.id, ml.action, ml.description, ml.cost, ml.created_at,
                      wo.wo_code, wo.problem_desc, wo.action_taken, wo.priority,
                      u.name AS user_name')
            ->join('work_orders wo', 'wo.id = ml.work_order_id', 'left')
            ->join('users u',        'u.id = ml.user_id',        'left')
            ->where('ml.asset_id', $asset['id'])
            ->whereIn('ml.action', ['perbaikan_mulai', 'perbaikan_selesai'])
            ->orderBy('ml.created_at', 'DESC')
            ->limit(10)
            ->get()
            ->getResultArray();

        // 4. Riwayat Preventive Maintenance
        // Ambil dari work_orders yang type = preventive
        $pmHistory = $this->db->table('work_orders wo')
            ->select('wo.id, wo.wo_code, wo.type, wo.status, wo.scheduled_date,
                      wo.finish_date, wo.action_taken, wo.cost,
                      u.name AS assigned_to_name')
            ->join('users u', 'u.id = wo.assigned_to', 'left')
            ->where('wo.asset_id', $asset['id'])
            ->where('wo.type', 'preventive')
            ->orderBy('wo.scheduled_date', 'DESC')
            ->limit(10)
            ->get()
            ->getResultArray();

        // 5. Garansi (hitungan otomatis)
        $warrantyStatus = $this->calcWarrantyStatus($asset['warranty_expiry'] ?? null);

        // Umur aset
        $age = InventoryAssetModel::calcAge($asset['purchase_date'] ?? null);

        // QR code (base64 untuk langsung embed)
        helper('qrcode');
        $qrBase64 = qr_base64(base_url('qr/' . $assetCode), 6);

        return view('qr_scan/view', [
            'asset'           => $asset,
            'repair_history'  => $repairHistory,
            'pm_history'      => $pmHistory,
            'warranty_status' => $warrantyStatus,
            'age'             => $age,
            'qr_b64'          => $qrBase64,
        ]);
    }

    /**
     * Hitung status garansi
     */
    private function calcWarrantyStatus(?string $warrantyExpiry): array
    {
        if (! $warrantyExpiry) {
            return [
                'status'    => 'none',
                'label'     => 'Tidak ada garansi',
                'color'     => 'text-gray-400',
                'days_left' => null,
                'expired'   => false,
            ];
        }

        $expiry   = strtotime($warrantyExpiry);
        $now      = time();
        $daysLeft = (int) (($expiry - $now) / 86400);

        if ($daysLeft < 0) {
            return [
                'status'    => 'expired',
                'label'     => 'Garansi sudah habis',
                'detail'    => 'Expired ' . abs($daysLeft) . ' hari yang lalu',
                'color'     => 'text-red-600',
                'days_left' => $daysLeft,
                'expired'   => true,
                'date'      => date('d M Y', $expiry),
            ];
        }

        if ($daysLeft === 0) {
            return [
                'status'    => 'today',
                'label'     => 'Garansi habis hari ini',
                'detail'    => date('d M Y', $expiry),
                'color'     => 'text-red-500',
                'days_left' => 0,
                'expired'   => false,
                'date'      => date('d M Y', $expiry),
            ];
        }

        if ($daysLeft <= 30) {
            return [
                'status'    => 'expiring',
                'label'     => 'Garansi akan habis',
                'detail'    => $daysLeft . ' hari lagi',
                'color'     => 'text-orange-500',
                'days_left' => $daysLeft,
                'expired'   => false,
                'date'      => date('d M Y', $expiry),
            ];
        }

        return [
            'status'    => 'active',
            'label'     => 'Garansi masih aktif',
            'detail'    => $daysLeft . ' hari lagi',
            'color'     => 'text-green-600',
            'days_left' => $daysLeft,
            'expired'   => false,
            'date'      => date('d M Y', $expiry),
        ];
    }
}
