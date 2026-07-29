<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\DashboardKpiModel;

/**
 * DashboardController
 *
 * Mengumpulkan 15 indikator GA Dashboard dan mengirimkannya ke view
 *
 * Route: GET /admin/dashboard
 */
class DashboardController extends BaseController
{
    protected DashboardKpiModel $kpi;

    public function __construct()
    {
        $this->kpi = new DashboardKpiModel();
    }

    // ---------------------------------------------------------------
    // GET /admin/dashboard
    // ---------------------------------------------------------------
    public function index()
    {
        $role = session()->get('role');
        if ($role === 'user') {
            // Dashboard sederhana untuk user
            $userId    = (int) session()->get('user_id');
            $deptScope = session()->get('department_id') ?: null;
            $db = \Config\Database::connect();

            // WO yang user buat
            $myWoTotal  = (int) $db->table('work_orders')->where('requested_by', $userId)->countAllResults();
            $myWoOpen   = (int) $db->table('work_orders')->where('requested_by', $userId)->where('status', 'open')->countAllResults();
            $myWoDone   = (int) $db->table('work_orders')->where('requested_by', $userId)->where('status', 'done')->countAllResults();

            // WO terbaru milik user
            $myWoRecent = $db->table('work_orders wo')
                ->select('wo.id, wo.wo_code, wo.status, wo.priority, wo.problem_desc, wo.created_at, a.name AS asset_name')
                ->join('assets a', 'a.id = wo.asset_id', 'left')
                ->where('wo.requested_by', $userId)
                ->orderBy('wo.created_at', 'DESC')
                ->limit(5)->get()->getResultArray();

            // Aset di dept user
            $myAssets = $deptScope
                ? (int) $db->table('assets')->where('department_id', $deptScope)->where('deleted_at', null)->countAllResults()
                : 0;

            return view('dashboard/user', [
                'title'       => 'Dashboard',
                'myWoTotal'   => $myWoTotal,
                'myWoOpen'    => $myWoOpen,
                'myWoDone'    => $myWoDone,
                'myWoRecent'  => $myWoRecent,
                'myAssets'    => $myAssets,
            ]);
        }
        if ($role === 'pembelian') {
            return redirect()->to('/admin/procurement');
        }
        if ($role === 'technician') {
            return redirect()->to('/admin/work-orders');
        }

        // ── A. Aset ────────────────────────────────────────────────
        $assetSummary     = $this->kpi->assetSummary();
        $totalValue       = $this->kpi->totalAssetValue();
        $pengadaanBulanIni= $this->kpi->pengadaanBulanIni();
        $warrantySoon     = $this->kpi->warrantyExpiringSoon(5);
        $assetByDept      = $this->kpi->assetByDepartment();

        // ── B. Work Order ──────────────────────────────────────────
        $woSummary        = $this->kpi->workOrderSummary();
        $openWOs          = $this->kpi->recentOpenWorkOrders(5);

        // ── C. Biaya ───────────────────────────────────────────────
        $costThisMonth    = $this->kpi->maintenanceCostThisMonth();
        $costTrend        = $this->kpi->maintenanceCostTrend(6);
        $budgetTerserap   = $this->kpi->budgetTerserap();

        // ── D. Vendor & User ───────────────────────────────────────
        $totalVendors     = $this->kpi->totalActiveVendors();
        $totalUsers       = $this->kpi->totalActiveUsers();

        // ── E. Aktivitas ───────────────────────────────────────────
        $recentActivity   = $this->kpi->recentActivity(12);

        // ── Chart Data ─────────────────────────────────────────────
        $chartCondition = json_encode([
            'labels' => ['Normal', 'Warning', 'Critical'],
            'data'   => [
                $assetSummary['normal'],
                $assetSummary['warning'],
                $assetSummary['critical'],
            ],
            'colors' => ['#22c55e', '#f59e0b', '#ef4444'],
        ]);

        $chartCostTrend = json_encode([
            'labels' => array_column($costTrend, 'bulan'),
            'data'   => array_map(fn($r) => (float) $r['total'], $costTrend),
        ]);

        $chartDept = json_encode([
            'labels' => array_column($assetByDept, 'department'),
            'data'   => array_column($assetByDept, 'total'),
        ]);

        return view('dashboard/kpi', [
            'title' => 'General Affairs Dashboard',

            // ── 15 Indikator Utama ────────────────────────────────
            // 1. Total Asset
            'total_asset'           => $assetSummary['total'],

            // 2. Total Nilai Asset
            'total_nilai_asset'     => $totalValue,

            // 3. Asset Aktif (status = tersedia)
            'asset_aktif'           => $assetSummary['tersedia'],

            // 4. Rusak Ringan
            'rusak_ringan'          => $assetSummary['kondisi']['rusak_ringan'],

            // 5. Rusak Berat
            'rusak_berat'           => $assetSummary['kondisi']['rusak_berat'],

            // 6. Dalam Perbaikan
            'dalam_perbaikan'       => $assetSummary['dalam_perbaikan'],

            // 7. Pending Approval (WO open tanpa assigned_to)
            'pending_approval'      => $woSummary['pending_approval'],

            // 8. Work Order Open
            'wo_open'               => $woSummary['open'] + $woSummary['in_progress'],

            // 9. Work Order Closed
            'wo_closed'             => $woSummary['closed'],

            // 10. Preventive Maintenance Due
            'pm_due'                => $woSummary['pm_due'],

            // 11. Preventive Maintenance Done
            'pm_done'               => $woSummary['pm_done'],

            // 12. Asset Expired Warranty
            'asset_expired_warranty'=> $assetSummary['expired_warranty'],

            // 13. Vendor Aktif
            'vendor_aktif'          => $totalVendors,

            // 14. Pengadaan Bulan Ini
            'pengadaan_bulan_ini'   => $pengadaanBulanIni,

            // 15. Budget Terserap (%)
            'budget_terserap_persen'=> $budgetTerserap['persen'],
            'budget_terserap_value' => $budgetTerserap['terserap'],

            // ── Data Pendukung ────────────────────────────────────
            'asset_summary'         => $assetSummary,
            'wo_summary'            => $woSummary,
            'cost_this_month'       => $costThisMonth,
            'total_users'           => $totalUsers,

            // Tabel
            'warranty_soon'         => $warrantySoon,
            'open_wos'              => $openWOs,
            'recent_activity'       => $recentActivity,

            // Chart JSON
            'chart_condition'       => $chartCondition,
            'chart_cost_trend'      => $chartCostTrend,
            'chart_dept'            => $chartDept,
        ]);
    }
}
