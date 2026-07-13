<?php

namespace App\Models;

use CodeIgniter\Database\BaseConnection;

/**
 * ProcurementRequestModel
 *
 * Alur status:
 * draft → pending_atasan → pending_direktur → approved
 *       → rfq → po → received → registered
 *       → rejected / cancelled (bisa dari mana saja)
 */
class ProcurementRequestModel
{
    protected BaseConnection $db;

    public const STATUS_FLOW = [
        'draft'             => ['label' => 'Draft',              'icon' => '📝', 'color' => 'bg-gray-100 text-gray-600'],
        'pending_atasan'    => ['label' => 'Pending Atasan',     'icon' => '⏳', 'color' => 'bg-yellow-100 text-yellow-700'],
        'pending_direktur'  => ['label' => 'Pending Direktur',   'icon' => '⏳', 'color' => 'bg-orange-100 text-orange-700'],
        'approved'          => ['label' => 'Approved',           'icon' => '✅', 'color' => 'bg-green-100 text-green-700'],
        'rejected'          => ['label' => 'Ditolak',            'icon' => '❌', 'color' => 'bg-red-100 text-red-700'],
        'rfq'               => ['label' => 'RFQ / Penawaran',    'icon' => '📋', 'color' => 'bg-blue-100 text-blue-700'],
        'po'                => ['label' => 'PO Dibuat',          'icon' => '🛒', 'color' => 'bg-indigo-100 text-indigo-700'],
        'received'          => ['label' => 'Barang Diterima',    'icon' => '📦', 'color' => 'bg-teal-100 text-teal-700'],
        'registered'        => ['label' => 'Teregistrasi',       'icon' => '🗃️', 'color' => 'bg-purple-100 text-purple-700'],
        'cancelled'         => ['label' => 'Dibatalkan',         'icon' => '🚫', 'color' => 'bg-gray-100 text-gray-400'],
    ];

    public const URGENCY = [
        'low'      => ['label' => 'Rendah',  'color' => 'bg-green-100 text-green-700'],
        'normal'   => ['label' => 'Normal',  'color' => 'bg-blue-100 text-blue-700'],
        'high'     => ['label' => 'Tinggi',  'color' => 'bg-orange-100 text-orange-700'],
        'critical' => ['label' => 'Kritis',  'color' => 'bg-red-100 text-red-700'],
    ];

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    // ================================================================
    // READ
    // ================================================================

    public function getList(array $filters = [], int $limit = 20, int $offset = 0): array
    {
        $b = $this->baseQuery();
        $this->applyFilters($b, $filters);
        return $b->orderBy('pr.created_at', 'DESC')->limit($limit, $offset)->get()->getResultArray();
    }

    public function countFiltered(array $filters = []): int
    {
        $b = $this->db->table('procurement_requests pr')->where('pr.deleted_at', null);
        $this->applyFilters($b, $filters);
        return (int) $b->countAllResults();
    }

    public function getById(int $id): ?array
    {
        $row = $this->db->table('procurement_requests pr')
            ->select('pr.*,
                      d.name  AS department_name,
                      u.name  AS requested_by_name, u.phone AS requested_by_phone,
                      a.name  AS atasan_name,
                      di.name AS direktur_name')
            ->join('departments d', 'd.id = pr.department_id',       'left')
            ->join('users u',       'u.id = pr.requested_by',        'left')
            ->join('users a',       'a.id = pr.approved_atasan',     'left')
            ->join('users di',      'di.id = pr.approved_direktur',  'left')
            ->where('pr.id', $id)->where('pr.deleted_at', null)
            ->get()->getRowArray();
        return $row ?: null;
    }

    // ================================================================
    // DASHBOARD STATS
    // ================================================================

    public function getDashboardStats(): array
    {
        $rows = $this->db->table('procurement_requests')
            ->select('status, COUNT(*) AS n')
            ->where('deleted_at', null)
            ->groupBy('status')
            ->get()->getResultArray();

        $stats = [];
        foreach (array_keys(self::STATUS_FLOW) as $key) { $stats[$key] = 0; }
        $stats['total'] = 0;

        foreach ($rows as $r) {
            if (isset($stats[$r['status']])) { $stats[$r['status']] = (int) $r['n']; }
            $stats['total'] += (int) $r['n'];
        }

        // Pending approval = pending_atasan + pending_direktur
        $stats['pending_approval'] = $stats['pending_atasan'] + $stats['pending_direktur'];

        // PO berjalan = status po
        $stats['po_berjalan'] = $stats['po'];

        // PO selesai = received + registered
        $stats['po_selesai'] = $stats['received'] + $stats['registered'];

        // Nilai pengadaan total (dari PO completed)
        $row = $this->db->table('purchase_orders')
            ->selectSum('total', 'nilai')
            ->whereIn('status', ['completed', 'partial'])
            ->get()->getRowArray();
        $stats['nilai_pengadaan'] = (float) ($row['nilai'] ?? 0);

        // Nilai bulan ini
        $row2 = $this->db->table('purchase_orders')
            ->selectSum('total', 'nilai')
            ->where('MONTH(created_at)', date('n'))
            ->where('YEAR(created_at)', date('Y'))
            ->get()->getRowArray();
        $stats['nilai_bulan_ini'] = (float) ($row2['nilai'] ?? 0);

        return $stats;
    }

    /**
     * Lead Time Vendor — rata-rata per vendor (hari)
     */
    public function getLeadTimeByVendor(): array
    {
        return $this->db->query("
            SELECT
                v.name AS vendor_name,
                COUNT(po.id)              AS total_po,
                ROUND(AVG(po.lead_time_days), 1) AS avg_lead_time,
                SUM(po.total)             AS total_nilai
            FROM purchase_orders po
            JOIN vendors v ON v.id = po.vendor_id
            WHERE po.status IN ('completed','partial')
              AND po.lead_time_days IS NOT NULL
            GROUP BY v.id, v.name
            ORDER BY total_po DESC
            LIMIT 5
        ")->getResultArray();
    }

    /**
     * Top Vendor berdasarkan total nilai PO
     */
    public function getTopVendors(): array
    {
        return $this->db->query("
            SELECT
                v.name AS vendor_name,
                v.phone AS vendor_phone,
                COUNT(po.id) AS total_po,
                SUM(po.total) AS total_nilai,
                ROUND(AVG(po.lead_time_days), 1) AS avg_lead_time
            FROM purchase_orders po
            JOIN vendors v ON v.id = po.vendor_id
            WHERE po.status IN ('completed','partial','confirmed')
            GROUP BY v.id, v.name, v.phone
            ORDER BY total_nilai DESC
            LIMIT 5
        ")->getResultArray();
    }

    /**
     * Trend pengadaan 6 bulan (untuk chart)
     */
    public function getTrend(int $months = 6): array
    {
        return $this->db->query("
            SELECT
                DATE_FORMAT(created_at, '%b %Y') AS bulan,
                DATE_FORMAT(created_at, '%Y%m')  AS sort_key,
                COUNT(*) AS total_request,
                SUM(total_estimated) AS total_nilai
            FROM procurement_requests
            WHERE deleted_at IS NULL
              AND created_at >= DATE_SUB(NOW(), INTERVAL ? MONTH)
            GROUP BY sort_key, bulan
            ORDER BY sort_key ASC
        ", [$months])->getResultArray();
    }

    // ================================================================
    // WRITE
    // ================================================================

    public function insert(array $data): int|false
    {
        if (empty($data['total_estimated']) && !empty($data['quantity']) && !empty($data['estimated_price'])) {
            $data['total_estimated'] = $data['quantity'] * $data['estimated_price'];
        }
        $data['created_at'] = $data['updated_at'] = date('Y-m-d H:i:s');
        $this->db->table('procurement_requests')->insert($data);
        $id = $this->db->insertID();
        return $id > 0 ? (int) $id : false;
    }

    public function update(int $id, array $data): bool
    {
        if (!empty($data['quantity']) && !empty($data['estimated_price'])) {
            $data['total_estimated'] = $data['quantity'] * $data['estimated_price'];
        }
        $data['updated_at'] = date('Y-m-d H:i:s');
        return $this->db->table('procurement_requests')->where('id', $id)->update($data);
    }

    public function delete(int $id): bool
    {
        return $this->db->table('procurement_requests')->where('id', $id)
            ->update(['deleted_at' => date('Y-m-d H:i:s'), 'status' => 'cancelled']);
    }

    // ================================================================
    // GENERATE CODE — PR-YYYYMM-NNN
    // ================================================================

    public function generateCode(): string
    {
        $prefix = 'PR-' . date('Ym') . '-';
        $last = $this->db->table('procurement_requests')
            ->select('request_code')->like('request_code', $prefix, 'after')
            ->orderBy('request_code', 'DESC')->limit(1)->get()->getRowArray();
        $seq = 1;
        if ($last) { $parts = explode('-', $last['request_code']); $seq = (int) end($parts) + 1; }
        return $prefix . str_pad($seq, 3, '0', STR_PAD_LEFT);
    }

    // ================================================================
    // PRIVATE
    // ================================================================

    private function baseQuery()
    {
        return $this->db->table('procurement_requests pr')
            ->select('pr.*, d.name AS department_name, u.name AS requested_by_name')
            ->join('departments d', 'd.id = pr.department_id', 'left')
            ->join('users u',       'u.id = pr.requested_by', 'left')
            ->where('pr.deleted_at', null);
    }

    private function applyFilters($b, array $f): void
    {
        if (!empty($f['search'])) {
            $s = $f['search'];
            $b->groupStart()->like('pr.request_code', $s)->orLike('pr.title', $s)->groupEnd();
        }
        if (!empty($f['status']))        { $b->where('pr.status', $f['status']); }
        if (!empty($f['urgency']))       { $b->where('pr.urgency', $f['urgency']); }
        if (!empty($f['department_id'])) { $b->where('pr.department_id', $f['department_id']); }
        if (!empty($f['requested_by']))  { $b->where('pr.requested_by', $f['requested_by']); }
    }
}
