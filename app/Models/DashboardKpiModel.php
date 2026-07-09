<?php

namespace App\Models;

use CodeIgniter\Database\BaseConnection;

/**
 * DashboardKpiModel
 *
 * Seluruh query 15 indikator GA Dashboard dikumpulkan di sini.
 *
 * Indikator:
 *  1.  Total Asset
 *  2.  Total Nilai Asset
 *  3.  Asset Aktif (status = tersedia)
 *  4.  Rusak Ringan
 *  5.  Rusak Berat
 *  6.  Dalam Perbaikan
 *  7.  Pending Approval (WO open belum ada assigned_to)
 *  8.  Work Order Open
 *  9.  Work Order Closed
 *  10. Preventive Maintenance Due
 *  11. Preventive Maintenance Done
 *  12. Asset Expired Warranty
 *  13. Vendor Aktif
 *  14. Pengadaan Bulan Ini (Rp)
 *  15. Budget Terserap (% dari total anggaran WO bulan ini)
 */
class DashboardKpiModel
{
    protected BaseConnection $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    // ================================================================
    // A. KPI ASET (indikator 1–6, 12)
    // ================================================================

    /**
     * Ringkasan lengkap aset — satu GROUP BY status + satu GROUP BY condition
     * Return keys: total, tersedia, dipinjam, dalam_perbaikan, dihapus,
     *              kondisi[baik|rusak_ringan|rusak_berat],
     *              expired_warranty
     */
    public function assetSummary(): array
    {
        // Status
        $statusRows = $this->db->table('assets')
            ->select('status, COUNT(*) AS n')
            ->where('deleted_at', null)
            ->groupBy('status')
            ->get()->getResultArray();

        // Status groups mapping
        $normalList = ['Aktif', 'Standby', 'Terpasang', 'Siap Operasi', 'tersedia'];
        $perhatianList = ['Jadwal PM', 'Kalibrasi', 'Menunggu Instalasi', 'Menunggu Sparepart', 'Pengadaan'];
        $warningList = ['Rusak Ringan', 'Corrective Maintenance', 'Idle', 'Mutasi', 'dalam_perbaikan', 'diperbaiki'];
        $criticalList = ['Rusak Berat', 'Tidak Beroperasi', 'Obsolete', 'Penghapusan', 'dihapus'];

        $out = [
            'total'           => 0,
            'normal'          => 0,
            'perhatian'       => 0,
            'warning'         => 0,
            'critical'        => 0,
            'tersedia'        => 0,
            'dipinjam'        => 0,
            'dalam_perbaikan' => 0,
            'dihapus'         => 0,
        ];

        foreach ($statusRows as $r) {
            $status = $r['status'];
            $n = (int) $r['n'];

            if (in_array($status, $normalList)) {
                $out['normal'] += $n;
            } elseif (in_array($status, $perhatianList)) {
                $out['perhatian'] += $n;
            } elseif (in_array($status, $warningList)) {
                $out['warning'] += $n;
            } elseif (in_array($status, $criticalList)) {
                $out['critical'] += $n;
            }

            // Real repair count (Corrective Maintenance / Menunggu Sparepart)
            if (in_array($status, ['Corrective Maintenance', 'Menunggu Sparepart', 'dalam_perbaikan'])) {
                $out['dalam_perbaikan'] += $n;
            }

            $out['total'] += $n;
        }

        $out['tersedia'] = $out['normal'];
        $out['dipinjam'] = $out['warning'];
        $out['dihapus'] = $out['critical'];

        $out['kondisi'] = [
            'baik'         => $out['normal'] + $out['perhatian'],
            'rusak_ringan' => $out['warning'],
            'rusak_berat'  => $out['critical'],
        ];

        // Indikator 12 — Garansi sudah/akan habis (expired today or earlier)
        $out['expired_warranty'] = (int) $this->db->table('assets')
            ->where('deleted_at', null)
            ->where('warranty_expiry IS NOT NULL', null, false)
            ->where('warranty_expiry <', date('Y-m-d'))
            ->countAllResults();

        // Garansi akan habis dalam 90 hari (untuk tabel warning)
        $out['warranty_soon'] = (int) $this->db->table('assets')
            ->where('deleted_at', null)
            ->where('warranty_expiry >=', date('Y-m-d'))
            ->where('warranty_expiry <=', date('Y-m-d', strtotime('+90 days')))
            ->countAllResults();

        return $out;
    }

    /**
     * Indikator 2 — Total nilai aset (purchase_price)
     */
    public function totalAssetValue(): float
    {
        $row = $this->db->table('assets')
            ->selectSum('purchase_price', 'total')
            ->where('deleted_at', null)
            ->get()->getRowArray();

        return (float) ($row['total'] ?? 0);
    }

    /**
     * Indikator 14 — Total nilai pengadaan bulan ini
     * (aset yang dibuat/purchase_date di bulan berjalan)
     */
    public function pengadaanBulanIni(): float
    {
        $row = $this->db->table('assets')
            ->selectSum('purchase_price', 'total')
            ->where('deleted_at', null)
            ->where('MONTH(purchase_date)', date('n'))
            ->where('YEAR(purchase_date)', date('Y'))
            ->get()->getRowArray();

        return (float) ($row['total'] ?? 0);
    }

    /**
     * 5 aset dengan garansi paling dekat kadaluarsa (untuk tabel)
     */
    public function warrantyExpiringSoon(int $limit = 5): array
    {
        return $this->db->table('assets a')
            ->select('a.id, a.asset_code, a.name, a.warranty_expiry,
                      d.name AS department_name')
            ->join('departments d', 'd.id = a.department_id', 'left')
            ->where('a.deleted_at', null)
            ->where('a.warranty_expiry >=', date('Y-m-d'))
            ->where('a.warranty_expiry <=', date('Y-m-d', strtotime('+90 days')))
            ->orderBy('a.warranty_expiry', 'ASC')
            ->limit($limit)
            ->get()->getResultArray();
    }

    // ================================================================
    // B. KPI WORK ORDER (indikator 7, 8, 9, 10, 11)
    // ================================================================

    /**
     * Ringkasan Work Order lengkap
     * Return: total, open, in_progress, waiting_part, done, cancelled,
     *         kritis_bulan_ini, pending_approval,
     *         pm_due, pm_done, corrective_open, corrective_done
     */
    public function workOrderSummary(): array
    {
        if (! $this->tableExists('work_orders')) {
            return $this->emptyWoSummary();
        }

        // Status summary
        $statusRows = $this->db->table('work_orders')
            ->select('status, COUNT(*) AS n')
            ->groupBy('status')
            ->get()->getResultArray();

        $out = $this->emptyWoSummary();
        foreach ($statusRows as $r) {
            if (array_key_exists($r['status'], $out)) {
                $out[$r['status']] = (int) $r['n'];
            }
            $out['total'] += (int) $r['n'];
        }

        // Indikator 7 — Pending Approval: WO open + belum ada assigned_to
        $out['pending_approval'] = (int) $this->db->table('work_orders')
            ->where('status', 'open')
            ->where('assigned_to', null)
            ->countAllResults();

        // WO kritis aktif
        $out['kritis_bulan_ini'] = (int) $this->db->table('work_orders')
            ->where('priority', 'kritis')
            ->whereNotIn('status', ['done', 'cancelled'])
            ->countAllResults();

        // Indikator 10 — Preventive Maintenance Due
        // (WO type=preventive yang belum selesai / terjadwal hari ini ke belakang)
        $out['pm_due'] = (int) $this->db->table('work_orders')
            ->where('type', 'preventive')
            ->whereIn('status', ['open', 'in_progress', 'waiting_part'])
            ->where('scheduled_date <=', date('Y-m-d'))
            ->countAllResults();

        // Indikator 11 — Preventive Maintenance Done (sepanjang masa)
        $out['pm_done'] = (int) $this->db->table('work_orders')
            ->where('type', 'preventive')
            ->where('status', 'done')
            ->countAllResults();

        // WO Closed = done + cancelled (indikator 9)
        $out['closed'] = $out['done'] + $out['cancelled'];

        return $out;
    }

    /**
     * Indikator 15 — Budget Terserap (%)
     * Budget = total cost semua WO done bulan ini
     * Anggaran pembanding = total cost semua WO (done + open) bulan ini
     * Jika tidak ada anggaran, fallback ke persentase dari total biaya maintenance YTD
     */
    public function budgetTerserap(): array
    {
        if (! $this->tableExists('work_orders')) {
            return ['persen' => 0, 'terserap' => 0.0, 'total_anggaran' => 0.0];
        }

        // Total anggaran bulan ini = semua WO yang punya cost di bulan ini
        $rowTotal = $this->db->table('work_orders')
            ->selectSum('cost', 'total')
            ->where('cost IS NOT NULL', null, false)
            ->where('MONTH(created_at)', date('n'))
            ->where('YEAR(created_at)', date('Y'))
            ->get()->getRowArray();

        // Terserap = hanya yang status done bulan ini
        $rowDone = $this->db->table('work_orders')
            ->selectSum('cost', 'total')
            ->where('status', 'done')
            ->where('cost IS NOT NULL', null, false)
            ->where('MONTH(finish_date)', date('n'))
            ->where('YEAR(finish_date)', date('Y'))
            ->get()->getRowArray();

        $totalAnggaran = (float) ($rowTotal['total'] ?? 0);
        $terserap      = (float) ($rowDone['total'] ?? 0);
        $persen        = $totalAnggaran > 0
            ? round(($terserap / $totalAnggaran) * 100, 1)
            : 0;

        return [
            'persen'          => $persen,
            'terserap'        => $terserap,
            'total_anggaran'  => $totalAnggaran,
        ];
    }

    /**
     * Total biaya maintenance bulan berjalan (dari maintenance_logs)
     */
    public function maintenanceCostThisMonth(): float
    {
        if (! $this->tableExists('maintenance_logs')) {
            return 0.0;
        }

        $row = $this->db->table('maintenance_logs')
            ->selectSum('cost', 'total')
            ->where('MONTH(created_at)', date('n'))
            ->where('YEAR(created_at)', date('Y'))
            ->get()->getRowArray();

        return (float) ($row['total'] ?? 0);
    }

    /**
     * Trend biaya 6 bulan terakhir (untuk chart)
     */
    public function maintenanceCostTrend(int $months = 6): array
    {
        if (! $this->tableExists('maintenance_logs')) {
            return [];
        }

        return $this->db->query("
            SELECT
                DATE_FORMAT(created_at, '%b %y') AS bulan,
                DATE_FORMAT(created_at, '%Y%m')  AS sort_key,
                SUM(cost) AS total
            FROM maintenance_logs
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? MONTH)
              AND cost IS NOT NULL
            GROUP BY sort_key, bulan
            ORDER BY sort_key ASC
        ", [$months])->getResultArray();
    }

    /**
     * 5 WO open terbaru (untuk tabel dashboard)
     */
    public function recentOpenWorkOrders(int $limit = 5): array
    {
        if (! $this->tableExists('work_orders')) {
            return [];
        }

        return $this->db->table('work_orders wo')
            ->select('wo.id, wo.wo_code, wo.type, wo.priority, wo.status,
                      wo.problem_desc, wo.scheduled_date, wo.created_at,
                      a.name AS asset_name, a.asset_code,
                      u.name AS requested_by_name,
                      t.name AS assigned_to_name')
            ->join('assets a', 'a.id = wo.asset_id',     'left')
            ->join('users u',  'u.id = wo.requested_by', 'left')
            ->join('users t',  't.id = wo.assigned_to',  'left')
            ->whereIn('wo.status', ['open', 'in_progress', 'waiting_part'])
            ->orderBy('FIELD(wo.priority, "kritis","tinggi","sedang","rendah")', '', false)
            ->orderBy('wo.created_at', 'DESC')
            ->limit($limit)
            ->get()->getResultArray();
    }

    // ================================================================
    // C. KPI VENDOR (indikator 13)
    // ================================================================

    public function totalActiveVendors(): int
    {
        if (! $this->tableExists('vendors')) {
            return 0;
        }

        return (int) $this->db->table('vendors')
            ->where('is_active', 1)
            ->where('deleted_at', null)
            ->countAllResults();
    }

    // ================================================================
    // D. KPI USER & UMUM
    // ================================================================

    public function totalActiveUsers(): int
    {
        return (int) $this->db->table('users')
            ->where('is_active', 1)
            ->where('deleted_at', null)
            ->countAllResults();
    }

    // ================================================================
    // E. CHART DATA
    // ================================================================

    public function assetByDepartment(): array
    {
        if (! $this->tableExists('departments')) {
            return [];
        }

        return $this->db->table('assets a')
            ->select('d.name AS department, COUNT(a.id) AS total')
            ->join('departments d', 'd.id = a.department_id', 'left')
            ->where('a.deleted_at', null)
            ->groupBy('a.department_id')
            ->orderBy('total', 'DESC')
            ->limit(8)
            ->get()->getResultArray();
    }

    // ================================================================
    // F. AKTIVITAS TERBARU
    // ================================================================

    public function recentActivity(int $limit = 10): array
    {
        if (! $this->tableExists('maintenance_logs')) {
            return [];
        }

        return $this->db->table('maintenance_logs ml')
            ->select('ml.action, ml.description, ml.created_at, ml.cost,
                      a.name AS asset_name, a.asset_code,
                      u.name AS user_name')
            ->join('assets a', 'a.id = ml.asset_id', 'left')
            ->join('users u',  'u.id = ml.user_id',  'left')
            ->orderBy('ml.created_at', 'DESC')
            ->limit($limit)
            ->get()->getResultArray();
    }

    // ================================================================
    // PRIVATE HELPERS
    // ================================================================

    private function tableExists(string $table): bool
    {
        return $this->db->tableExists($table);
    }

    private function emptyWoSummary(): array
    {
        return [
            'total'           => 0,
            'open'            => 0,
            'in_progress'     => 0,
            'waiting_part'    => 0,
            'done'            => 0,
            'cancelled'       => 0,
            'closed'          => 0,
            'kritis_bulan_ini'=> 0,
            'pending_approval'=> 0,
            'pm_due'          => 0,
            'pm_done'         => 0,
        ];
    }
}
