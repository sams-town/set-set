<?php

namespace App\Models;

use CodeIgniter\Database\BaseConnection;

/**
 * MaintenanceLogModel
 *
 * Riwayat semua aktivitas aset + analytics Corrective Maintenance:
 *  1. Top 10 Asset Rusak   — aset yang paling sering masuk WO corrective
 *  2. Biaya Perbaikan      — total & trend per bulan
 *  3. Downtime             — estimasi dari durasi WO open → done
 *  4. Repeat Breakdown     — aset yang rusak ≥ 2x dalam 90 hari
 *  5. Root Cause           — damage_type paling sering dari work_orders
 */
class MaintenanceLogModel
{
    protected BaseConnection $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    // ================================================================
    // WRITE — catat log aktivitas
    // ================================================================

    public function record(
        int    $assetId,
        string $action,
        string $description = '',
        array  $oldValue    = [],
        array  $newValue    = [],
        float  $cost        = 0,
        int    $workOrderId = 0
    ): bool {
        return $this->db->table('maintenance_logs')->insert([
            'asset_id'      => $assetId,
            'work_order_id' => $workOrderId > 0 ? $workOrderId : null,
            'user_id'       => session()->get('user_id'),
            'action'        => $action,
            'description'   => $description,
            'old_value'     => !empty($oldValue) ? json_encode($oldValue, JSON_UNESCAPED_UNICODE) : null,
            'new_value'     => !empty($newValue) ? json_encode($newValue, JSON_UNESCAPED_UNICODE) : null,
            'cost'          => $cost > 0 ? $cost : null,
            'created_at'    => date('Y-m-d H:i:s'),
        ]);
    }

    // ================================================================
    // READ — per aset & global
    // ================================================================

    public function getByAsset(int $assetId): array
    {
        return $this->db->table('maintenance_logs ml')
            ->select('ml.*, u.name AS user_name, wo.wo_code')
            ->join('users u',        'u.id  = ml.user_id',       'left')
            ->join('work_orders wo', 'wo.id = ml.work_order_id', 'left')
            ->where('ml.asset_id', $assetId)
            ->orderBy('ml.created_at', 'DESC')
            ->get()->getResultArray();
    }

    public function getRecent(int $limit = 10): array
    {
        return $this->db->table('maintenance_logs ml')
            ->select('ml.*, a.name AS asset_name, a.asset_code, u.name AS user_name')
            ->join('assets a', 'a.id = ml.asset_id', 'left')
            ->join('users u',  'u.id = ml.user_id',  'left')
            ->orderBy('ml.created_at', 'DESC')
            ->limit($limit)
            ->get()->getResultArray();
    }

    /**
     * Semua log corrective dengan filter untuk halaman histori
     */
    public function getAllWithFilters(array $filters = [], int $limit = 25, int $offset = 0): array
    {
        $builder = $this->db->table('work_orders wo')
            ->select('wo.id AS wo_id, wo.wo_code, wo.type, wo.priority, wo.status,
                      wo.problem_desc, wo.damage_type, wo.category_wo,
                      wo.action_taken, wo.material_used,
                      wo.cost, wo.material_cost, wo.labor_cost,
                      wo.start_date, wo.finish_date, wo.created_at, wo.target_date,
                      wo.response_time, wo.repair_time,
                      wo.photo_complaint, wo.photo_before, wo.photo_after,
                      a.id AS asset_id, a.name AS asset_name, a.asset_code,
                      a.category AS asset_category, a.brand, a.condition,
                      d.name AS department_name,
                      u.name AS reporter_name,
                      t.name AS technician_name,
                      v.name AS vendor_name')
            ->join('assets a',      'a.id = wo.asset_id',      'left')
            ->join('departments d', 'd.id = a.department_id',  'left')
            ->join('users u',       'u.id = wo.requested_by',  'left')
            ->join('users t',       't.id = wo.assigned_to',   'left')
            ->join('vendors v',     'v.id = wo.vendor_id',     'left')
            ->where('wo.type', 'corrective');

        $this->applyFilters($builder, $filters);

        return $builder
            ->orderBy('wo.created_at', 'DESC')
            ->limit($limit, $offset)
            ->get()->getResultArray();
    }

    public function countAllWithFilters(array $filters = []): int
    {
        $builder = $this->db->table('work_orders wo')
            ->join('assets a', 'a.id = wo.asset_id', 'left')
            ->where('wo.type', 'corrective');
        $this->applyFilters($builder, $filters);
        return (int) $builder->countAllResults();
    }

    // ================================================================
    // ANALYTICS 1 — TOP 10 ASSET RUSAK TERBANYAK
    // ================================================================

    /**
     * 10 aset paling sering masuk corrective WO
     * Bisa difilter per periode
     */
    public function getTop10Broken(string $dateFrom = '', string $dateTo = ''): array
    {
        $builder = $this->db->table('work_orders wo')
            ->select('a.id AS asset_id, a.name AS asset_name, a.asset_code,
                      a.category AS asset_category, a.brand,
                      d.name AS department_name,
                      COUNT(wo.id)   AS total_wo,
                      SUM(wo.cost)   AS total_cost,
                      MAX(wo.created_at) AS last_breakdown')
            ->join('assets a',      'a.id = wo.asset_id',     'inner')
            ->join('departments d', 'd.id = a.department_id', 'left')
            ->where('wo.type', 'corrective');

        if ($dateFrom) { $builder->where('wo.created_at >=', $dateFrom . ' 00:00:00'); }
        if ($dateTo)   { $builder->where('wo.created_at <=', $dateTo   . ' 23:59:59'); }

        return $builder
            ->groupBy('a.id, a.name, a.asset_code, a.category, a.brand, d.name')
            ->orderBy('total_wo', 'DESC')
            ->limit(10)
            ->get()->getResultArray();
    }

    // ================================================================
    // ANALYTICS 2 — BIAYA PERBAIKAN PER BULAN (trend 12 bulan)
    // ================================================================

    public function getBiayaPerBulan(int $months = 12): array
    {
        return $this->db->query("
            SELECT
                DATE_FORMAT(wo.created_at, '%b %Y') AS bulan,
                DATE_FORMAT(wo.created_at, '%Y%m')  AS sort_key,
                COUNT(wo.id)                         AS total_wo,
                COALESCE(SUM(wo.cost), 0)            AS total_biaya,
                COALESCE(SUM(wo.material_cost), 0)   AS biaya_material,
                COALESCE(SUM(wo.labor_cost), 0)      AS biaya_jasa
            FROM work_orders wo
            WHERE wo.type = 'corrective'
              AND wo.created_at >= DATE_SUB(NOW(), INTERVAL ? MONTH)
            GROUP BY sort_key, bulan
            ORDER BY sort_key ASC
        ", [$months])->getResultArray();
    }

    /**
     * Total biaya summary (bulan ini, tahun ini, all time)
     */
    public function getBiayaSummary(): array
    {
        $row = $this->db->query("
            SELECT
                COALESCE(SUM(CASE WHEN MONTH(created_at) = MONTH(NOW()) AND YEAR(created_at) = YEAR(NOW())
                               THEN cost END), 0)  AS bulan_ini,
                COALESCE(SUM(CASE WHEN YEAR(created_at) = YEAR(NOW())
                               THEN cost END), 0)  AS tahun_ini,
                COALESCE(SUM(cost), 0)             AS all_time,
                COUNT(CASE WHEN MONTH(created_at) = MONTH(NOW()) AND YEAR(created_at) = YEAR(NOW())
                          THEN 1 END)              AS wo_bulan_ini,
                COUNT(*)                           AS wo_total
            FROM work_orders
            WHERE type = 'corrective'
        ")->getRowArray();

        return $row ?? ['bulan_ini' => 0, 'tahun_ini' => 0, 'all_time' => 0, 'wo_bulan_ini' => 0, 'wo_total' => 0];
    }

    // ================================================================
    // ANALYTICS 3 — DOWNTIME (tidak dapat digunakan)
    // ================================================================

    /**
     * Total downtime dari WO corrective status done:
     * downtime = finish_date - start_date (dalam jam)
     * Jika tidak ada start/finish, gunakan response_time + repair_time
     */
    public function getDowntimeStats(): array
    {
        $data = $this->db->query("
            SELECT
                a.id                         AS asset_id,
                a.name                       AS asset_name,
                a.asset_code,
                d.name                       AS department_name,
                COUNT(wo.id)                 AS total_wo,
                SUM(
                    CASE
                        WHEN wo.start_date IS NOT NULL AND wo.finish_date IS NOT NULL
                        THEN TIMESTAMPDIFF(HOUR, wo.start_date, CONCAT(wo.finish_date, ' 23:59:59'))
                        WHEN wo.repair_time IS NOT NULL
                        THEN ROUND(wo.repair_time / 60, 1)
                        ELSE 0
                    END
                )                            AS total_downtime_hours,
                MAX(CASE
                        WHEN wo.start_date IS NOT NULL AND wo.finish_date IS NOT NULL
                        THEN TIMESTAMPDIFF(HOUR, wo.start_date, CONCAT(wo.finish_date, ' 23:59:59'))
                        WHEN wo.repair_time IS NOT NULL
                        THEN ROUND(wo.repair_time / 60, 1)
                        ELSE 0
                    END)                     AS max_downtime_hours
            FROM work_orders wo
            INNER JOIN assets a ON a.id = wo.asset_id
            LEFT  JOIN departments d ON d.id = a.department_id
            WHERE wo.type = 'corrective'
              AND wo.status = 'done'
            GROUP BY a.id, a.name, a.asset_code, d.name
            HAVING total_downtime_hours > 0
            ORDER BY total_downtime_hours DESC
            LIMIT 10
        ")->getResultArray();

        // Total downtime semua aset
        $summary = $this->db->query("
            SELECT
                COALESCE(SUM(
                    CASE
                        WHEN start_date IS NOT NULL AND finish_date IS NOT NULL
                        THEN TIMESTAMPDIFF(HOUR, start_date, CONCAT(finish_date, ' 23:59:59'))
                        WHEN repair_time IS NOT NULL THEN ROUND(repair_time / 60, 1)
                        ELSE 0
                    END
                ), 0) AS total_hours,
                COUNT(*) AS total_wo
            FROM work_orders
            WHERE type = 'corrective' AND status = 'done'
        ")->getRowArray();

        return [
            'top_assets'  => $data,
            'total_hours' => (float) ($summary['total_hours'] ?? 0),
            'total_wo'    => (int)   ($summary['total_wo']    ?? 0),
        ];
    }

    // ================================================================
    // ANALYTICS 4 — REPEAT BREAKDOWN
    // ================================================================

    /**
     * Aset yang mengalami kerusakan berulang (≥ 2 kali dalam 90 hari)
     * Disertai interval rata-rata antar kerusakan
     */
    public function getRepeatBreakdown(int $daysPeriod = 90, int $minCount = 2): array
    {
        return $this->db->query("
            SELECT
                a.id          AS asset_id,
                a.name        AS asset_name,
                a.asset_code,
                a.category    AS asset_category,
                d.name        AS department_name,
                COUNT(wo.id)  AS breakdown_count,
                GROUP_CONCAT(wo.damage_type ORDER BY wo.created_at SEPARATOR ', ')
                              AS damage_types,
                MIN(wo.created_at) AS first_breakdown,
                MAX(wo.created_at) AS last_breakdown,
                ROUND(
                    DATEDIFF(MAX(wo.created_at), MIN(wo.created_at)) / NULLIF(COUNT(wo.id) - 1, 0)
                , 0)          AS avg_interval_days,
                COALESCE(SUM(wo.cost), 0) AS total_cost
            FROM work_orders wo
            INNER JOIN assets a ON a.id = wo.asset_id
            LEFT  JOIN departments d ON d.id = a.department_id
            WHERE wo.type = 'corrective'
              AND wo.created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
            GROUP BY a.id, a.name, a.asset_code, a.category, d.name
            HAVING breakdown_count >= ?
            ORDER BY breakdown_count DESC, total_cost DESC
            LIMIT 20
        ", [$daysPeriod, $minCount])->getResultArray();
    }

    // ================================================================
    // ANALYTICS 5 — ROOT CAUSE ANALYSIS
    // ================================================================

    /**
     * Distribusi damage_type dari WO corrective
     * = penyebab kerusakan paling sering
     */
    public function getRootCauseStats(string $dateFrom = '', string $dateTo = ''): array
    {
        $builder = $this->db->table('work_orders')
            ->select('damage_type, category_wo,
                      COUNT(*)           AS total,
                      COALESCE(SUM(cost), 0) AS total_cost,
                      ROUND(AVG(repair_time), 0) AS avg_repair_minutes')
            ->where('type', 'corrective')
            ->where('damage_type IS NOT NULL', null, false);

        if ($dateFrom) { $builder->where('created_at >=', $dateFrom . ' 00:00:00'); }
        if ($dateTo)   { $builder->where('created_at <=', $dateTo   . ' 23:59:59'); }

        return $builder
            ->groupBy('damage_type, category_wo')
            ->orderBy('total', 'DESC')
            ->get()->getResultArray();
    }

    /**
     * Summary semua analytics sekaligus untuk dashboard
     */
    public function getDashboardStats(): array
    {
        $biaya    = $this->getBiayaSummary();
        $downtime = $this->getDowntimeStats();

        // Repeat breakdown count
        $repeatCount = count($this->getRepeatBreakdown(90, 2));

        // WO corrective open saat ini
        $openNow = (int) $this->db->table('work_orders')
            ->where('type', 'corrective')
            ->whereIn('status', ['open', 'in_progress', 'waiting_part'])
            ->countAllResults();

        // Root cause terbanyak
        $rootCauses = $this->getRootCauseStats();
        $topRootCause = !empty($rootCauses) ? $rootCauses[0]['damage_type'] : '-';

        return [
            'top10_broken'  => $this->getTop10Broken(),
            'biaya_summary' => $biaya,
            'downtime'      => $downtime,
            'repeat_count'  => $repeatCount,
            'open_now'      => $openNow,
            'top_root_cause'=> $topRootCause,
            'root_causes'   => array_slice($rootCauses, 0, 8),
        ];
    }

    /**
     * Riwayat lengkap kerusakan untuk satu aset
     */
    public function getAssetCorrectiveHistory(int $assetId): array
    {
        return $this->db->table('work_orders wo')
            ->select('wo.*, t.name AS technician_name, u.name AS reporter_name, v.name AS vendor_name')
            ->join('users t', 't.id = wo.assigned_to',  'left')
            ->join('users u', 'u.id = wo.requested_by', 'left')
            ->join('vendors v', 'v.id = wo.vendor_id',  'left')
            ->where('wo.asset_id', $assetId)
            ->where('wo.type', 'corrective')
            ->orderBy('wo.created_at', 'DESC')
            ->get()->getResultArray();
    }

    /**
     * getAllWithRelations — alias untuk BorrowController compatibility
     */
    public function getWithRelations(array $filters = []): array
    {
        return $this->getAllWithFilters($filters, 500, 0);
    }

    /**
     * getAllWithRelations untuk ReportController
     */
    public function getAllWithRelations(int $limit = 500): array
    {
        return $this->db->table('maintenance_logs ml')
            ->select('ml.*, a.name AS asset_name, a.asset_code,
                      u.name AS user_name, wo.wo_code')
            ->join('assets a',      'a.id  = ml.asset_id',      'left')
            ->join('users u',       'u.id  = ml.user_id',       'left')
            ->join('work_orders wo','wo.id = ml.work_order_id', 'left')
            ->orderBy('ml.created_at', 'DESC')
            ->limit($limit)
            ->get()->getResultArray();
    }

    // ================================================================
    // PRIVATE HELPERS
    // ================================================================

    private function applyFilters($builder, array $filters): void
    {
        if (!empty($filters['search'])) {
            $s = $filters['search'];
            $builder->groupStart()
                ->like('a.name', $s)
                ->orLike('a.asset_code', $s)
                ->orLike('wo.wo_code', $s)
                ->orLike('wo.problem_desc', $s)
                ->orLike('wo.damage_type', $s)
                ->groupEnd();
        }
        if (!empty($filters['status']))        { $builder->where('wo.status', $filters['status']); }
        if (!empty($filters['priority']))      { $builder->where('wo.priority', $filters['priority']); }
        if (!empty($filters['damage_type']))   { $builder->where('wo.damage_type', $filters['damage_type']); }
        if (!empty($filters['department_id'])) { $builder->where('a.department_id', $filters['department_id']); }
        if (!empty($filters['asset_id']))      { $builder->where('wo.asset_id', $filters['asset_id']); }
        if (!empty($filters['date_from']))     { $builder->where('wo.created_at >=', $filters['date_from'] . ' 00:00:00'); }
        if (!empty($filters['date_to']))       { $builder->where('wo.created_at <=', $filters['date_to']   . ' 23:59:59'); }
        if (!empty($filters['type']))          { $builder->where('wo.type', $filters['type']); }
    }
}
