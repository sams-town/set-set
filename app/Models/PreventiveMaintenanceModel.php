<?php

namespace App\Models;

use CodeIgniter\Database\BaseConnection;

/**
 * PreventiveMaintenanceModel
 *
 * Mengelola jadwal PM recurring dan KPI dashboard:
 *  1. PM Due Today
 *  2. PM This Week
 *  3. PM Completed (bulan ini)
 *  4. PM Missed (overdue)
 *  5. Compliance %
 *  6. Equipment Critical (aset kritis yang ada PM aktif)
 *  7. Calendar data (30 hari ke depan)
 */
class PreventiveMaintenanceModel
{
    protected BaseConnection $db;

    // Mapping recurring → interval hari
    public const RECURRING_DAYS = [
        'daily'     => 1,
        'weekly'    => 7,
        'monthly'   => 30,
        'quarterly' => 90,
        'biannual'  => 180,
        'yearly'    => 365,
    ];

    public const RECURRING_LABELS = [
        'daily'     => 'Harian',
        'weekly'    => 'Mingguan',
        'monthly'   => 'Bulanan',
        'quarterly' => '3 Bulanan',
        'biannual'  => '6 Bulanan',
        'yearly'    => 'Tahunan',
    ];

    public const PRIORITY_COLORS = [
        'kritis' => 'bg-red-100 text-red-700',
        'tinggi' => 'bg-orange-100 text-orange-700',
        'sedang' => 'bg-yellow-100 text-yellow-700',
        'rendah' => 'bg-green-100 text-green-700',
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
            ->orderBy('ps.next_due', 'ASC')
            ->limit($limit, $offset)
            ->get()->getResultArray();
    }

    public function countFiltered(array $filters = []): int
    {
        $builder = $this->db->table('pm_schedules ps')
            ->where('ps.deleted_at', null);
        $this->applyFilters($builder, $filters);
        return (int) $builder->countAllResults();
    }

    public function getById(int $id): ?array
    {
        $row = $this->db->table('pm_schedules ps')
            ->select('ps.*,
                      a.name       AS asset_name,
                      a.asset_code AS asset_code,
                      a.category   AS asset_category,
                      a.brand      AS asset_brand,
                      a.condition  AS asset_condition,
                      a.photo      AS asset_photo,
                      d.name       AS department_name,
                      u.name       AS assigned_to_name,
                      u.phone      AS assigned_to_phone,
                      cb.name      AS created_by_name')
            ->join('assets a',       'a.id = ps.asset_id',    'left')
            ->join('departments d',  'd.id = a.department_id','left')
            ->join('users u',        'u.id = ps.assigned_to', 'left')
            ->join('users cb',       'cb.id = ps.created_by', 'left')
            ->where('ps.id', $id)
            ->where('ps.deleted_at', null)
            ->get()->getRowArray();

        return $row ?: null;
    }

    /**
     * Ambil semua PM yang due dalam range tanggal (untuk calendar)
     */
    public function getCalendarData(string $dateFrom, string $dateTo): array
    {
        return $this->db->table('pm_schedules ps')
            ->select('ps.id, ps.title, ps.recurring, ps.next_due, ps.priority, ps.is_active,
                      a.name AS asset_name, a.asset_code,
                      u.name AS assigned_to_name')
            ->join('assets a', 'a.id = ps.asset_id',    'left')
            ->join('users u',  'u.id = ps.assigned_to', 'left')
            ->where('ps.deleted_at', null)
            ->where('ps.is_active', 1)
            ->where('ps.next_due <=', $dateTo)
            ->orderBy('ps.next_due', 'ASC')
            ->get()->getResultArray();
    }

    /**
     * PM overdue: next_due sudah lewat dan last_done bukan hari ini
     */
    public function getOverdue(int $limit = 0): array
    {
        $builder = $this->baseQuery()
            ->where('ps.is_active', 1)
            ->where('ps.next_due <', date('Y-m-d'));

        if ($limit > 0) {
            $builder->limit($limit);
        }

        return $builder->orderBy('ps.next_due', 'ASC')->get()->getResultArray();
    }

    /**
     * PM yang due hari ini
     */
    public function getDueToday(): array
    {
        return $this->baseQuery()
            ->where('ps.is_active', 1)
            ->where('ps.next_due', date('Y-m-d'))
            ->orderBy('ps.priority', 'DESC')
            ->get()->getResultArray();
    }

    /**
     * PM yang due minggu ini (hari ini + 7 hari)
     */
    public function getDueThisWeek(): array
    {
        return $this->baseQuery()
            ->where('ps.is_active', 1)
            ->where('ps.next_due >=', date('Y-m-d'))
            ->where('ps.next_due <=', date('Y-m-d', strtotime('+7 days')))
            ->orderBy('ps.next_due', 'ASC')
            ->get()->getResultArray();
    }

    // ================================================================
    // DASHBOARD KPI STATS (7 indikator)
    // ================================================================

    public function getDashboardStats(): array
    {
        $today     = date('Y-m-d');
        $weekEnd   = date('Y-m-d', strtotime('+7 days'));
        $monthStart = date('Y-m-01');
        $monthEnd   = date('Y-m-t');

        // 1. PM Due Today
        $pmDueToday = (int) $this->db->table('pm_schedules')
            ->where('deleted_at', null)
            ->where('is_active', 1)
            ->where('next_due', $today)
            ->countAllResults();

        // 2. PM This Week (hari ini s/d +7 hari)
        $pmThisWeek = (int) $this->db->table('pm_schedules')
            ->where('deleted_at', null)
            ->where('is_active', 1)
            ->where('next_due >=', $today)
            ->where('next_due <=', $weekEnd)
            ->countAllResults();

        // 3. PM Completed bulan ini
        // Cek dari work_orders type=preventive yang done bulan ini
        $pmCompleted = (int) $this->db->table('work_orders')
            ->where('type', 'preventive')
            ->where('status', 'done')
            ->where('finish_date >=', $monthStart)
            ->where('finish_date <=', $monthEnd)
            ->countAllResults();

        // 4. PM Missed / Overdue
        $pmMissed = (int) $this->db->table('pm_schedules')
            ->where('deleted_at', null)
            ->where('is_active', 1)
            ->where('next_due <', $today)
            ->countAllResults();

        // 5. Compliance % = (completed / (completed + missed)) * 100
        $totalDue  = $pmCompleted + $pmMissed;
        $compliance = $totalDue > 0 ? round($pmCompleted / $totalDue * 100, 1) : 0;

        // 6. Equipment Critical = aset dengan kondisi rusak/kritis yang punya PM aktif
        $equipCritical = (int) $this->db->table('pm_schedules ps')
            ->join('assets a', 'a.id = ps.asset_id', 'inner')
            ->where('ps.deleted_at', null)
            ->where('ps.is_active', 1)
            ->where('ps.priority', 'kritis')
            ->countAllResults();

        // Total schedule aktif
        $totalActive = (int) $this->db->table('pm_schedules')
            ->where('deleted_at', null)
            ->where('is_active', 1)
            ->countAllResults();

        // Per-recurring breakdown (untuk chart)
        $recurringBreakdown = $this->db->table('pm_schedules')
            ->select('recurring, COUNT(*) AS total')
            ->where('deleted_at', null)
            ->where('is_active', 1)
            ->groupBy('recurring')
            ->get()->getResultArray();

        $breakdown = [];
        foreach (self::RECURRING_LABELS as $key => $label) {
            $breakdown[$key] = ['label' => $label, 'total' => 0];
        }
        foreach ($recurringBreakdown as $r) {
            if (isset($breakdown[$r['recurring']])) {
                $breakdown[$r['recurring']]['total'] = (int) $r['total'];
            }
        }

        return [
            'pm_due_today'     => $pmDueToday,
            'pm_this_week'     => $pmThisWeek,
            'pm_completed'     => $pmCompleted,
            'pm_missed'        => $pmMissed,
            'compliance'       => $compliance,
            'equipment_critical'=> $equipCritical,
            'total_active'     => $totalActive,
            'breakdown'        => $breakdown,
        ];
    }

    // ================================================================
    // CREATE / UPDATE / DELETE
    // ================================================================

    public function insert(array $data): int|false
    {
        // Hitung interval_days dari recurring
        $data['interval_days'] = self::RECURRING_DAYS[$data['recurring']] ?? 30;

        // Hitung next_due dari start_date atau hari ini
        if (empty($data['next_due'])) {
            $base           = $data['start_date'] ?? date('Y-m-d');
            $data['next_due'] = $base;
        }

        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');

        unset($data['start_date']); // bukan kolom tabel

        $this->db->table('pm_schedules')->insert($data);
        $id = $this->db->insertID();

        if ($id && ($data['schedule_type'] ?? 'pm') === 'calibration') {
            $this->db->table('assets')
                ->where('id', $data['asset_id'])
                ->update([
                    'requires_calibration'  => 1,
                    'next_calibration_date' => $data['next_due'],
                ]);
        }

        // Sync pm_interval_days ke tabel assets
        $this->syncPmIntervalToAsset((int) $data['asset_id']);

        return $id > 0 ? (int) $id : false;
    }

    public function update(int $id, array $data): bool
    {
        if (!empty($data['recurring'])) {
            $data['interval_days'] = self::RECURRING_DAYS[$data['recurring']] ?? 30;
        }
        $data['updated_at'] = date('Y-m-d H:i:s');
        $ok = $this->db->table('pm_schedules')->where('id', $id)->update($data);

        if ($ok) {
            $schedule = $this->getById($id);
            if ($schedule && ($schedule['schedule_type'] ?? 'pm') === 'calibration') {
                $this->db->table('assets')
                    ->where('id', $schedule['asset_id'])
                    ->update([
                        'requires_calibration'  => 1,
                        'next_calibration_date' => $schedule['next_due'],
                    ]);
            }
            // Sync pm_interval_days ke tabel assets
            if ($schedule) {
                $this->syncPmIntervalToAsset((int) $schedule['asset_id']);
            }
        }

        return $ok;
    }

    public function delete(int $id): bool
    {
        $schedule = $this->getById($id);
        $ok = $this->db->table('pm_schedules')
            ->where('id', $id)
            ->update(['deleted_at' => date('Y-m-d H:i:s'), 'is_active' => 0]);

        if ($ok && $schedule) {
            if (($schedule['schedule_type'] ?? 'pm') === 'calibration') {
                $this->db->table('assets')
                    ->where('id', $schedule['asset_id'])
                    ->update([
                        'next_calibration_date' => null,
                    ]);
            }
            // Sync pm_interval_days ke tabel assets
            $this->syncPmIntervalToAsset((int) $schedule['asset_id']);
        }

        return $ok;
    }

    // ================================================================
    // MARK AS DONE — catat pelaksanaan + geser next_due
    // ================================================================

    public function markAsDone(int $id, string $doneDate, ?string $notes = null): bool
    {
        $schedule = $this->getById($id);
        if (!$schedule) {
            return false;
        }

        $nextDue = self::calcNextDue($doneDate, $schedule['recurring']);

        $ok = $this->db->table('pm_schedules')
            ->where('id', $id)
            ->update([
                'last_done'  => $doneDate,
                'next_due'   => $nextDue,
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

        // Sync pm_interval_days ke tabel assets setelah mark done
        if ($ok) {
            $this->syncPmIntervalToAsset((int) $schedule['asset_id']);
        }

        return $ok;
    }

    // ================================================================
    // PRIVATE HELPERS
    // ================================================================

    /**
     * Sync pm_interval_days di tabel assets dari pm_schedules aktif.
     * Ambil interval_days terkecil dari semua PM aktif (bukan kalibrasi)
     * untuk aset tersebut, lalu update assets.pm_interval_days.
     */
    private function syncPmIntervalToAsset(int $assetId): void
    {
        $row = $this->db->table('pm_schedules')
            ->select('MIN(interval_days) AS min_interval')
            ->where('asset_id', $assetId)
            ->where('is_active', 1)
            ->where('deleted_at', null)
            ->where('schedule_type !=', 'calibration')
            ->get()->getRowArray();

        $interval = ($row && $row['min_interval'] !== null)
            ? (int) $row['min_interval']
            : null;

        $this->db->table('assets')
            ->where('id', $assetId)
            ->update(['pm_interval_days' => $interval]);
    }

    // ================================================================
    // STATIC HELPERS
    // ================================================================

    /**
     * Hitung next_due berdasarkan tanggal selesai + recurring
     */
    public static function calcNextDue(string $fromDate, string $recurring): string
    {
        $days = self::RECURRING_DAYS[$recurring] ?? 30;
        return date('Y-m-d', strtotime($fromDate . ' +' . $days . ' days'));
    }

    /**
     * Hitung status PM untuk badge
     * Return: 'overdue' | 'due_today' | 'upcoming' | 'ok'
     */
    public static function getDueStatus(string $nextDue): array
    {
        $today    = date('Y-m-d');
        $daysLeft = (int) ((strtotime($nextDue) - strtotime($today)) / 86400);

        if ($daysLeft < 0) {
            return [
                'status' => 'overdue',
                'label'  => 'Overdue ' . abs($daysLeft) . ' hari',
                'badge'  => 'bg-red-100 text-red-700',
                'icon'   => '🔴',
                'days'   => $daysLeft,
            ];
        }
        if ($daysLeft === 0) {
            return [
                'status' => 'due_today',
                'label'  => 'Due Hari Ini',
                'badge'  => 'bg-orange-100 text-orange-700',
                'icon'   => '🟠',
                'days'   => 0,
            ];
        }
        if ($daysLeft <= 7) {
            return [
                'status' => 'upcoming',
                'label'  => $daysLeft . ' hari lagi',
                'badge'  => 'bg-yellow-100 text-yellow-700',
                'icon'   => '🟡',
                'days'   => $daysLeft,
            ];
        }
        return [
            'status' => 'ok',
            'label'  => $daysLeft . ' hari lagi',
            'badge'  => 'bg-green-100 text-green-700',
            'icon'   => '🟢',
            'days'   => $daysLeft,
        ];
    }

    // ================================================================
    // DROPDOWN HELPERS
    // ================================================================

    public function getAssetsDropdown(): array
    {
        $rows = $this->db->table('assets a')
            ->select('a.id, a.asset_code, a.name, d.name AS dept')
            ->join('departments d', 'd.id = a.department_id', 'left')
            ->where('a.deleted_at', null)
            ->orderBy('a.name')
            ->get()->getResultArray();

        $out = [];
        foreach ($rows as $r) {
            $out[$r['id']] = "[{$r['asset_code']}] {$r['name']}" . ($r['dept'] ? " — {$r['dept']}" : '');
        }
        return $out;
    }

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

    // ================================================================
    // PRIVATE
    // ================================================================

    private function baseQuery()
    {
        return $this->db->table('pm_schedules ps')
            ->select('ps.*,
                      a.name       AS asset_name,
                      a.asset_code AS asset_code,
                      a.category   AS asset_category,
                      a.condition  AS asset_condition,
                      d.name       AS department_name,
                      u.name       AS assigned_to_name')
            ->join('assets a',      'a.id = ps.asset_id',    'left')
            ->join('departments d', 'd.id = a.department_id','left')
            ->join('users u',       'u.id = ps.assigned_to', 'left')
            ->where('ps.deleted_at', null);
    }

    private function applyFilters($builder, array $filters): void
    {
        if (!empty($filters['search'])) {
            $s = $filters['search'];
            $builder->groupStart()
                ->like('ps.title', $s)
                ->orLike('a.name', $s)
                ->orLike('a.asset_code', $s)
                ->groupEnd();
        }
        if (!empty($filters['schedule_type'])) {
            $builder->where('ps.schedule_type', $filters['schedule_type']);
        }
        if (!empty($filters['recurring'])) {
            $builder->where('ps.recurring', $filters['recurring']);
        }
        if (!empty($filters['priority'])) {
            $builder->where('ps.priority', $filters['priority']);
        }
        if (!empty($filters['assigned_to'])) {
            $builder->where('ps.assigned_to', $filters['assigned_to']);
        }
        if (!empty($filters['is_active'])) {
            $builder->where('ps.is_active', $filters['is_active']);
        }
        if (!empty($filters['status'])) {
            $today = date('Y-m-d');
            switch ($filters['status']) {
                case 'overdue':
                    $builder->where('ps.next_due <', $today)->where('ps.is_active', 1);
                    break;
                case 'due_today':
                    $builder->where('ps.next_due', $today)->where('ps.is_active', 1);
                    break;
                case 'this_week':
                    $builder->where('ps.next_due >=', $today)
                            ->where('ps.next_due <=', date('Y-m-d', strtotime('+7 days')))
                            ->where('ps.is_active', 1);
                    break;
            }
        }
    }
}
