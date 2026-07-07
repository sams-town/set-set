<?php

namespace App\Models;

use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\Database\BaseConnection;

/**
 * WorkOrderModel
 *
 * Modul Work Order — alur:
 * User Input Keluhan + Foto → Assessment Teknisi (prioritas)
 * → Pengerjaan + Foto Before → Testing + Foto After → Close WO
 */
class WorkOrderModel
{
    protected BaseConnection $db;

    // SLA default per prioritas (dalam jam)
    public const SLA_HOURS = [
        'kritis' => 4,
        'tinggi' => 8,
        'sedang' => 24,
        'rendah' => 72,
    ];

    // Jenis kerusakan
    public const DAMAGE_TYPES = [
        'Mekanik / Fisik',
        'Elektrik / Kelistrikan',
        'Software / Sistem',
        'Jaringan / Koneksi',
        'Kebocoran / Plumbing',
        'AC / Pendingin',
        'Furniture / Perabot',
        'Kendaraan',
        'Lainnya',
    ];

    // Kategori WO
    public const CATEGORIES_WO = [
        'Mekanikal',
        'Elektrikal',
        'Plumbing',
        'Sipil',
        'IT',
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
        $builder = $this->baseQuery();
        $this->applyFilters($builder, $filters);

        return $builder
            ->orderBy('FIELD(wo.priority,"kritis","tinggi","sedang","rendah")', '', false)
            ->orderBy('wo.created_at', 'DESC')
            ->limit($limit, $offset)
            ->get()->getResultArray();
    }

    public function countFiltered(array $filters = []): int
    {
        $builder = $this->db->table('work_orders wo');
        $this->applyFilters($builder, $filters);
        return (int) $builder->countAllResults();
    }

    public function getById(int $id): ?array
    {
        $row = $this->db->table('work_orders wo')
            ->select('wo.*,
                      a.name  AS asset_name,      a.asset_code,   a.category AS asset_category,
                      a.brand AS asset_brand,      a.photo        AS asset_photo,
                      u.name  AS requested_by_name, u.phone       AS requested_by_phone,
                      t.name  AS assigned_to_name,  t.phone       AS assigned_to_phone,
                      v.name  AS vendor_name,        v.phone       AS vendor_phone,
                      d.name  AS department_name,
                      l.name  AS location_name,      l.building    AS location_building')
            ->join('assets a',       'a.id = wo.asset_id',      'left')
            ->join('users u',        'u.id = wo.requested_by',  'left')
            ->join('users t',        't.id = wo.assigned_to',   'left')
            ->join('vendors v',      'v.id = wo.vendor_id',     'left')
            ->join('departments d',  'd.id = wo.department_id', 'left')
            ->join('locations l',    'l.id = wo.location_id',   'left')
            ->where('wo.id', $id)
            ->get()->getRowArray();

        return $row ?: null;
    }

    // ================================================================
    // DASHBOARD KPI STATS
    // ================================================================

    /**
     * Ringkasan status WO untuk 8 kartu dashboard
     */
    public function getDashboardStats(): array
    {
        // Hitung per status
        $statusRows = $this->db->table('work_orders')
            ->select('status, COUNT(*) AS n')
            ->groupBy('status')
            ->get()->getResultArray();

        $stats = [
            'total'        => 0,
            'open'         => 0,
            'in_progress'  => 0,
            'waiting_part' => 0,
            'done'         => 0,
            'cancelled'    => 0,
        ];
        foreach ($statusRows as $r) {
            if (array_key_exists($r['status'], $stats)) {
                $stats[$r['status']] = (int) $r['n'];
            }
            $stats['total'] += (int) $r['n'];
        }

        // WO baru hari ini
        $stats['new_today'] = (int) $this->db->table('work_orders')
            ->where('DATE(created_at)', date('Y-m-d'))
            ->countAllResults();

        // WO Closed = done + cancelled
        $stats['closed'] = $stats['done'] + $stats['cancelled'];

        // SLA compliance: WO done yang selesai sebelum target_date
        $totalDone = $stats['done'];
        if ($totalDone > 0) {
            $slaOk = (int) $this->db->table('work_orders')
                ->where('status', 'done')
                ->where('finish_date IS NOT NULL', null, false)
                ->where('target_date IS NOT NULL', null, false)
                ->where('finish_date <=', $this->db->escape('work_orders.target_date'), false)
                ->countAllResults();

            // Fallback: hitung via query langsung
            $slaOk = (int) $this->db->query("
                SELECT COUNT(*) AS n FROM work_orders
                WHERE status = 'done'
                  AND finish_date IS NOT NULL
                  AND target_date IS NOT NULL
                  AND finish_date <= target_date
            ")->getRowArray()['n'];

            $stats['sla_compliance'] = round($slaOk / $totalDone * 100, 1);
            $stats['sla_ok']         = $slaOk;
        } else {
            $stats['sla_compliance'] = 0;
            $stats['sla_ok']         = 0;
        }

        // Average Response Time (menit) — dari WO yang punya response_time
        $avgResponse = $this->db->query("
            SELECT AVG(response_time) AS avg_rt
            FROM work_orders
            WHERE response_time IS NOT NULL AND response_time > 0
        ")->getRowArray();
        $stats['avg_response_time'] = round((float) ($avgResponse['avg_rt'] ?? 0));

        // Average Repair Time (menit)
        $avgRepair = $this->db->query("
            SELECT AVG(repair_time) AS avg_rt
            FROM work_orders
            WHERE repair_time IS NOT NULL AND repair_time > 0
        ")->getRowArray();
        $stats['avg_repair_time'] = round((float) ($avgRepair['avg_rt'] ?? 0));

        return $stats;
    }

    /**
     * Trend WO per bulan (6 bulan) — untuk chart
     */
    public function getMonthlyTrend(int $months = 6): array
    {
        return $this->db->query("
            SELECT
                DATE_FORMAT(created_at, '%b %y') AS bulan,
                DATE_FORMAT(created_at, '%Y%m')  AS sort_key,
                COUNT(*) AS total,
                SUM(status = 'done') AS selesai
            FROM work_orders
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? MONTH)
            GROUP BY sort_key, bulan
            ORDER BY sort_key ASC
        ", [$months])->getResultArray();
    }

    /**
     * WO overdue: sudah melewati target_date dan belum done/cancelled
     */
    public function getOverdueCount(): int
    {
        return (int) $this->db->table('work_orders')
            ->where('target_date IS NOT NULL', null, false)
            ->where('target_date <', date('Y-m-d'))
            ->whereNotIn('status', ['done', 'cancelled'])
            ->countAllResults();
    }

    // ================================================================
    // WRITE
    // ================================================================

    public function insert(array $data): int|false
    {
        // Set SLA otomatis dari prioritas jika belum diisi
        if (empty($data['sla_hours']) && !empty($data['priority'])) {
            $data['sla_hours'] = self::SLA_HOURS[$data['priority']] ?? 24;
        }

        // Set target_date otomatis dari sla_hours jika belum diisi
        if (empty($data['target_date']) && !empty($data['sla_hours'])) {
            $data['target_date'] = date('Y-m-d', strtotime('+' . $data['sla_hours'] . ' hours'));
        }

        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');

        $this->db->table('work_orders')->insert($data);
        $id = $this->db->insertID();
        return $id > 0 ? $id : false;
    }

    public function update(int $id, array $data): bool
    {
        // Recalculate SLA jika prioritas berubah
        if (!empty($data['priority']) && empty($data['sla_hours'])) {
            $data['sla_hours'] = self::SLA_HOURS[$data['priority']] ?? 24;
        }

        // Auto-set response_time saat status berubah ke in_progress
        if (!empty($data['status']) && $data['status'] === 'in_progress') {
            $existing = $this->getById($id);
            if ($existing && empty($existing['response_time'])) {
                $created  = strtotime($existing['created_at']);
                $data['response_time'] = (int) round((time() - $created) / 60);
            }
        }

        // Auto-set repair_time saat status berubah ke done
        if (!empty($data['status']) && $data['status'] === 'done') {
            $existing = $existing ?? $this->getById($id);
            if ($existing && empty($existing['repair_time']) && !empty($existing['start_date'])) {
                $start = strtotime($existing['start_date']);
                $data['repair_time'] = (int) round((time() - $start) / 60);
            }
            // Set finish_date jika belum diisi
            if (empty($data['finish_date'])) {
                $data['finish_date'] = date('Y-m-d');
            }
            // Hitung total cost = material + labor
            $matCost   = (float) ($data['material_cost'] ?? ($existing['material_cost'] ?? 0));
            $laborCost = (float) ($data['labor_cost']    ?? ($existing['labor_cost']    ?? 0));
            if ($matCost > 0 || $laborCost > 0) {
                $data['cost'] = $matCost + $laborCost;
            }
        }

        $data['updated_at'] = date('Y-m-d H:i:s');
        return $this->db->table('work_orders')->where('id', $id)->update($data);
    }

    public function delete(int $id): bool
    {
        return $this->db->table('work_orders')->where('id', $id)->delete();
    }

    // ================================================================
    // GENERATE KODE WO: WO-YYYYMMDD-NNN
    // ================================================================

    public function generateCode(): string
    {
        $prefix = 'WO-' . date('Ymd') . '-';
        $last   = $this->db->table('work_orders')
            ->select('wo_code')
            ->like('wo_code', $prefix, 'after')
            ->orderBy('wo_code', 'DESC')
            ->limit(1)
            ->get()->getRowArray();

        $seq = 1;
        if ($last) {
            $parts = explode('-', $last['wo_code']);
            $seq   = (int) end($parts) + 1;
        }
        return $prefix . str_pad($seq, 3, '0', STR_PAD_LEFT);
    }

    // ================================================================
    // DROPDOWN HELPERS
    // ================================================================

    public function getTechniciansDropdown(): array
    {
        $rows = $this->db->table('users')
            ->select('id, name')
            ->whereIn('role', ['admin', 'technician'])
            ->where('is_active', 1)
            ->where('deleted_at', null)
            ->orderBy('name')
            ->get()->getResultArray();

        $out = [];
        foreach ($rows as $r) { $out[$r['id']] = $r['name']; }
        return $out;
    }

    public function getAssetsDropdown(?int $deptId = null): array
    {
        $builder = $this->db->table('assets a')
            ->select('a.id, a.asset_code, a.name, d.name AS dept')
            ->join('departments d', 'd.id = a.department_id', 'left')
            ->where('a.deleted_at', null)
            ->orderBy('a.name');

        if ($deptId !== null) {
            $builder->where('a.department_id', $deptId);
        }

        $rows = $builder->get()->getResultArray();

        $out = [];
        foreach ($rows as $r) {
            $out[$r['id']] = "[{$r['asset_code']}] {$r['name']}" . ($r['dept'] ? " — {$r['dept']}" : '');
        }
        return $out;
    }

    public function getVendorsDropdown(): array
    {
        $rows = $this->db->table('vendors')
            ->select('id, name')
            ->where('is_active', 1)
            ->where('deleted_at', null)
            ->orderBy('name')
            ->get()->getResultArray();

        $out = [];
        foreach ($rows as $r) { $out[$r['id']] = $r['name']; }
        return $out;
    }

    public function getDepartmentsDropdown(): array
    {
        $rows = $this->db->table('departments')
            ->select('id, name')
            ->where('is_active', 1)
            ->where('deleted_at', null)
            ->orderBy('name')
            ->get()->getResultArray();

        $out = [];
        foreach ($rows as $r) { $out[$r['id']] = $r['name']; }
        return $out;
    }

    public function getLocationsDropdown(): array
    {
        $rows = $this->db->table('locations l')
            ->select('l.id, l.name, l.building')
            ->where('l.deleted_at', null)
            ->orderBy('l.name')
            ->get()->getResultArray();

        $out = [];
        foreach ($rows as $r) {
            $out[$r['id']] = $r['name'] . ($r['building'] ? ' — ' . $r['building'] : '');
        }
        return $out;
    }

    // ================================================================
    // HELPER: format waktu menit → label
    // ================================================================

    public static function formatMinutes(int $minutes): string
    {
        if ($minutes < 60)      return $minutes . ' menit';
        if ($minutes < 1440)    return round($minutes / 60, 1) . ' jam';
        return round($minutes / 1440, 1) . ' hari';
    }

    // ================================================================
    // PRIVATE
    // ================================================================

    private function baseQuery(): BaseBuilder
    {
        return $this->db->table('work_orders wo')
            ->select('wo.*, 
                      a.name  AS asset_name,       a.asset_code,
                      u.name  AS requested_by_name,
                      t.name  AS assigned_to_name,
                      v.name  AS vendor_name,
                      d.name  AS department_name,
                      l.name  AS location_name')
            ->join('assets a',      'a.id = wo.asset_id',      'left')
            ->join('users u',       'u.id = wo.requested_by',  'left')
            ->join('users t',       't.id = wo.assigned_to',   'left')
            ->join('vendors v',     'v.id = wo.vendor_id',     'left')
            ->join('departments d', 'd.id = wo.department_id', 'left')
            ->join('locations l',   'l.id = wo.location_id',   'left');
    }

    private function applyFilters(BaseBuilder $builder, array $filters): void
    {
        if (!empty($filters['search'])) {
            $s = $filters['search'];
            $builder->groupStart()
                ->like('wo.wo_code', $s)
                ->orLike('a.name', $s)
                ->orLike('a.asset_code', $s)
                ->orLike('u.name', $s)
                ->orLike('wo.reporter_name', $s)
                ->groupEnd();
        }
        if (!empty($filters['status']))      { $builder->where('wo.status', $filters['status']); }
        if (!empty($filters['priority']))    { $builder->where('wo.priority', $filters['priority']); }
        if (!empty($filters['type']))        { $builder->where('wo.type', $filters['type']); }
        if (!empty($filters['assigned_to'])) { $builder->where('wo.assigned_to', $filters['assigned_to']); }
        if (!empty($filters['department_id'])){ $builder->where('wo.department_id', $filters['department_id']); }
        if (!empty($filters['category_wo'])) { $builder->where('wo.category_wo', $filters['category_wo']); }
        if (!empty($filters['overdue'])) {
            $builder->where('wo.target_date IS NOT NULL', null, false)
                    ->where('wo.target_date <', date('Y-m-d'))
                    ->whereNotIn('wo.status', ['done', 'cancelled']);
        }
    }
}
