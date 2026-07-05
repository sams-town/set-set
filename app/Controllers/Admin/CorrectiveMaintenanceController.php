<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\MaintenanceLogModel;
use App\Models\InventoryAssetModel;

/**
 * CorrectiveMaintenanceController
 *
 * Menyimpan & menganalisis histori kerusakan aset sebagai basis
 * pengambilan keputusan perbaikan dan penggantian.
 *
 * Dashboard 5 analitik:
 *  1. Top 10 Asset Rusak
 *  2. Biaya Perbaikan (trend + summary)
 *  3. Downtime (tidak dapat digunakan)
 *  4. Repeat Breakdown (kerusakan berulang)
 *  5. Root Cause (penyebab kerusakan terbanyak)
 *
 * Routes:
 *   GET /admin/cm              → index  (dashboard + tabel histori)
 *   GET /admin/cm/asset/{id}   → show   (detail per aset)
 *   GET /admin/cm/export       → export (JSON untuk chart embed)
 */
class CorrectiveMaintenanceController extends BaseController
{
    protected MaintenanceLogModel  $logModel;
    protected InventoryAssetModel  $assetModel;

    private const PER_PAGE = 20;

    public function __construct()
    {
        $this->logModel   = new MaintenanceLogModel();
        $this->assetModel = new InventoryAssetModel();
    }

    // ================================================================
    // GET /admin/cm — Dashboard + Histori
    // ================================================================
    public function index()
    {
        // Filter periode
        $dateFrom = $this->request->getGet('date_from') ?? '';
        $dateTo   = $this->request->getGet('date_to')   ?? '';

        // Filter tabel histori
        $filters = [
            'search'        => $this->request->getGet('search'),
            'status'        => $this->request->getGet('status'),
            'priority'      => $this->request->getGet('priority'),
            'damage_type'   => $this->request->getGet('damage_type'),
            'department_id' => $this->request->getGet('department_id'),
            'date_from'     => $dateFrom,
            'date_to'       => $dateTo,
        ];

        $page       = max(1, (int) $this->request->getGet('page'));
        $offset     = ($page - 1) * self::PER_PAGE;
        $total      = $this->logModel->countAllWithFilters($filters);
        $histories  = $this->logModel->getAllWithFilters($filters, self::PER_PAGE, $offset);
        $totalPages = $total > 0 ? (int) ceil($total / self::PER_PAGE) : 1;

        // ── Analytics ──────────────────────────────────────────────
        $dashStats  = $this->logModel->getDashboardStats();
        $biayaTrend = $this->logModel->getBiayaPerBulan(12);
        $repeat     = $this->logModel->getRepeatBreakdown(90, 2);
        $rootCauses = $this->logModel->getRootCauseStats($dateFrom, $dateTo);
        $top10      = $this->logModel->getTop10Broken($dateFrom, $dateTo);
        $downtime   = $this->logModel->getDowntimeStats();

        // ── Chart JSON ─────────────────────────────────────────────
        // Trend biaya 12 bulan
        $chartBiaya = json_encode([
            'labels'   => array_column($biayaTrend, 'bulan'),
            'total'    => array_map(fn($r) => (float) $r['total_biaya'],    $biayaTrend),
            'material' => array_map(fn($r) => (float) $r['biaya_material'], $biayaTrend),
            'jasa'     => array_map(fn($r) => (float) $r['biaya_jasa'],     $biayaTrend),
            'wo_count' => array_map(fn($r) => (int)   $r['total_wo'],       $biayaTrend),
        ]);

        // Root cause donut
        $chartRootCause = json_encode([
            'labels' => array_column(array_slice($rootCauses, 0, 7), 'damage_type'),
            'data'   => array_map(fn($r) => (int) $r['total'], array_slice($rootCauses, 0, 7)),
        ]);

        // Top 10 bar chart
        $chartTop10 = json_encode([
            'labels' => array_map(fn($r) => $r['asset_name'] . ' (' . $r['asset_code'] . ')', $top10),
            'data'   => array_column($top10, 'total_wo'),
            'biaya'  => array_map(fn($r) => (float) $r['total_cost'], $top10),
        ]);

        // Downtime bar chart
        $chartDowntime = json_encode([
            'labels' => array_map(
                fn($r) => $r['asset_name'] . ' (' . $r['asset_code'] . ')',
                $downtime['top_assets']
            ),
            'data'   => array_map(fn($r) => (float) $r['total_downtime_hours'], $downtime['top_assets']),
        ]);

        // Damage types untuk dropdown filter
        $damageTypes = array_unique(array_filter(
            array_column($rootCauses, 'damage_type')
        ));

        return view('cm/index', [
            'title'           => 'Corrective Maintenance',

            // Dashboard Analytics
            'dash_stats'      => $dashStats,
            'biaya_summary'   => $dashStats['biaya_summary'],
            'downtime'        => $downtime,
            'repeat'          => $repeat,
            'root_causes'     => array_slice($rootCauses, 0, 8),
            'top10'           => $top10,

            // Histori Tabel
            'histories'       => $histories,
            'filters'         => $filters,
            'page'            => $page,
            'total_pages'     => $totalPages,
            'total_records'   => $total,
            'per_page'        => self::PER_PAGE,

            // Chart JSON
            'chart_biaya'     => $chartBiaya,
            'chart_root_cause'=> $chartRootCause,
            'chart_top10'     => $chartTop10,
            'chart_downtime'  => $chartDowntime,

            // Dropdown filter
            'damage_types'    => $damageTypes,
            'departments'     => $this->assetModel->getDepartmentsDropdown(),
        ]);
    }

    // ================================================================
    // GET /admin/cm/asset/{id} — Detail histori per aset
    // ================================================================
    public function showAsset(int $assetId)
    {
        $asset = $this->assetModel->getById($assetId);
        if (! $asset) {
            return redirect()->to('/admin/cm')
                ->with('error', 'Aset tidak ditemukan.');
        }

        // Riwayat corrective lengkap aset ini
        $history = $this->logModel->getAssetCorrectiveHistory($assetId);

        // Repeat breakdown aset ini saja
        $repeatInfo = null;
        if (count($history) >= 2) {
            $totalCost     = array_sum(array_column($history, 'cost'));
            $firstDate     = end($history)['created_at'];
            $lastDate      = $history[0]['created_at'];
            $daySpan       = max(1, (int) ((strtotime($lastDate) - strtotime($firstDate)) / 86400));
            $avgInterval   = $daySpan > 0 && count($history) > 1
                ? round($daySpan / (count($history) - 1))
                : 0;

            $repeatInfo = [
                'count'        => count($history),
                'total_cost'   => $totalCost,
                'avg_interval' => $avgInterval,
                'first_date'   => $firstDate,
                'last_date'    => $lastDate,
                'is_repeat'    => count($history) >= 2,
            ];
        }

        // Trend biaya per bulan untuk aset ini
        $trendRaw = $this->db_query_asset_trend($assetId);
        $chartAssetTrend = json_encode([
            'labels' => array_column($trendRaw, 'bulan'),
            'data'   => array_map(fn($r) => (float) $r['total_cost'], $trendRaw),
            'count'  => array_map(fn($r) => (int)   $r['total_wo'],   $trendRaw),
        ]);

        // Downtime aset ini
        $totalDowntime = 0;
        foreach ($history as $h) {
            if ($h['start_date'] && $h['finish_date']) {
                $totalDowntime += max(0, (int) ((strtotime($h['finish_date'] . ' 23:59:59') - strtotime($h['start_date'])) / 3600));
            } elseif ($h['repair_time']) {
                $totalDowntime += round($h['repair_time'] / 60, 1);
            }
        }

        // Damage type frequency untuk aset ini
        $dmgFreq = [];
        foreach ($history as $h) {
            $dmg = $h['damage_type'] ?? 'Tidak diketahui';
            $dmgFreq[$dmg] = ($dmgFreq[$dmg] ?? 0) + 1;
        }
        arsort($dmgFreq);

        $age = InventoryAssetModel::calcAge($asset['purchase_date'] ?? null);

        return view('cm/asset_detail', [
            'title'           => 'Histori Corrective — ' . $asset['name'],
            'asset'           => $asset,
            'age'             => $age,
            'history'         => $history,
            'repeat_info'     => $repeatInfo,
            'total_downtime'  => $totalDowntime,
            'dmg_frequency'   => $dmgFreq,
            'chart_asset_trend' => $chartAssetTrend,
        ]);
    }

    // ================================================================
    // PRIVATE — Query trend biaya per aset
    // ================================================================
    private function db_query_asset_trend(int $assetId): array
    {
        $db = \Config\Database::connect();
        return $db->query("
            SELECT
                DATE_FORMAT(created_at, '%b %Y') AS bulan,
                DATE_FORMAT(created_at, '%Y%m')  AS sort_key,
                COUNT(*)                         AS total_wo,
                COALESCE(SUM(cost), 0)           AS total_cost
            FROM work_orders
            WHERE asset_id = ?
              AND type = 'corrective'
            GROUP BY sort_key, bulan
            ORDER BY sort_key ASC
            LIMIT 24
        ", [$assetId])->getResultArray();
    }
}
