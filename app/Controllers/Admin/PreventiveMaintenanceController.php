<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\PreventiveMaintenanceModel;
use App\Models\WorkOrderModel;
use App\Models\MaintenanceLogModel;

/**
 * PreventiveMaintenanceController
 *
 * Mengelola schedule PM recurring dan dashboard PM
 *
 * Routes:
 *   GET  /admin/pm              → index (dashboard + table)
 *   GET  /admin/pm/new          → create
 *   POST /admin/pm              → store
 *   GET  /admin/pm/{id}         → show
 *   GET  /admin/pm/{id}/edit    → edit
 *   POST /admin/pm/{id}/update  → update
 *   POST /admin/pm/{id}/delete  → delete
 *   POST /admin/pm/{id}/mark-done → markAsDone (catat pelaksanaan + auto WO)
 */
class PreventiveMaintenanceController extends BaseController
{
    protected PreventiveMaintenanceModel $model;
    protected WorkOrderModel             $woModel;
    protected MaintenanceLogModel        $logModel;

    private const PER_PAGE = 20;

    public function __construct()
    {
        $this->model    = new PreventiveMaintenanceModel();
        $this->woModel  = new WorkOrderModel();
        $this->logModel = new MaintenanceLogModel();
    }

    // ================================================================
    // GET /admin/pm
    // ================================================================
    public function index()
    {
        $filters = [
            'search'        => $this->request->getGet('search'),
            'schedule_type' => $this->request->getGet('schedule_type'),
            'recurring'     => $this->request->getGet('recurring'),
            'priority'      => $this->request->getGet('priority'),
            'status'        => $this->request->getGet('status'),
            'assigned_to'   => $this->request->getGet('assigned_to'),
            'is_active'     => $this->request->getGet('is_active'),
        ];

        $page       = max(1, (int) $this->request->getGet('page'));
        $offset     = ($page - 1) * self::PER_PAGE;
        $total      = $this->model->countFiltered($filters);
        $schedules  = $this->model->getList($filters, self::PER_PAGE, $offset);
        $totalPages = $total > 0 ? (int) ceil($total / self::PER_PAGE) : 1;

        // Dashboard KPI
        $dashStats = $this->model->getDashboardStats();

        // Calendar data (30 hari ke depan)
        $calendarData = $this->model->getCalendarData(
            date('Y-m-d'),
            date('Y-m-d', strtotime('+30 days'))
        );

        // Chart breakdown recurring
        $chartBreakdown = json_encode([
            'labels' => array_column($dashStats['breakdown'], 'label'),
            'data'   => array_column($dashStats['breakdown'], 'total'),
        ]);

        return view('pm/index', [
            'title'          => 'Preventive Maintenance',
            'schedules'      => $schedules,
            'filters'        => $filters,
            'page'           => $page,
            'total_pages'    => $totalPages,
            'total_records'  => $total,
            'per_page'       => self::PER_PAGE,
            'dash_stats'     => $dashStats,
            'calendar_data'  => $calendarData,
            'chart_breakdown'=> $chartBreakdown,
            'technicians'    => $this->model->getTechniciansDropdown(),
            'recurring_opts' => PreventiveMaintenanceModel::RECURRING_LABELS,
        ]);
    }

    // ================================================================
    // GET /admin/pm/new
    // ================================================================
    public function create()
    {
        $assetId = $this->request->getGet('asset_id');
        return view('pm/form', [
            'title'                    => 'Buat Schedule PM',
            'schedule'                 => null,
            'assets'                   => $this->model->getAssetsDropdown(),
            'technicians'              => $this->model->getTechniciansDropdown(),
            'recurring_opts'           => PreventiveMaintenanceModel::RECURRING_LABELS,
            'priority_opts'            => ['rendah', 'sedang', 'tinggi', 'kritis'],
            'preselected_asset_id'     => $assetId,
        ]);
    }

    // ================================================================
    // POST /admin/pm
    // ================================================================
    public function store()
    {
        $rules = [
            'asset_id'      => 'required|integer',
            'schedule_type' => 'required|in_list[pm,calibration]',
            'title'         => 'required|min_length[5]|max_length[200]',
            'recurring'     => 'required|in_list[daily,weekly,monthly,quarterly,biannual,yearly]',
            'priority'      => 'required|in_list[rendah,sedang,tinggi,kritis]',
            'start_date'    => 'required|valid_date',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $scheduleId = $this->model->insert([
            'asset_id'           => $this->request->getPost('asset_id'),
            'schedule_type'      => $this->request->getPost('schedule_type'),
            'title'              => $this->request->getPost('title'),
            'description'        => $this->request->getPost('description'),
            'recurring'          => $this->request->getPost('recurring'),
            'priority'           => $this->request->getPost('priority'),
            'assigned_to'        => $this->request->getPost('assigned_to') ?: null,
            'start_date'         => $this->request->getPost('start_date'), // akan jadi next_due
            'estimated_duration' => $this->request->getPost('estimated_duration') ?: null,
            'is_active'          => 1,
            'created_by'         => session()->get('user_id'),
        ]);

        if (! $scheduleId) {
            return redirect()->back()->withInput()
                ->with('error', 'Gagal membuat schedule PM. Silakan coba lagi.');
        }

        return redirect()->to('/admin/pm')
            ->with('success', 'Schedule PM berhasil dibuat.');
    }

    // ================================================================
    // GET /admin/pm/{id}
    // ================================================================
    public function show(int $id)
    {
        $schedule = $this->model->getById($id);
        if (! $schedule) {
            return redirect()->to('/admin/pm')->with('error', 'Schedule tidak ditemukan.');
        }

        // Riwayat WO preventive untuk aset ini
        $woHistory = $this->woModel->getList([
            'asset_id' => $schedule['asset_id'],
            'type'     => 'preventive',
        ], 10, 0);

        $dueStatus = PreventiveMaintenanceModel::getDueStatus($schedule['next_due']);

        return view('pm/detail', [
            'title'      => 'Detail Schedule PM',
            'schedule'   => $schedule,
            'due_status' => $dueStatus,
            'wo_history' => $woHistory,
        ]);
    }

    // ================================================================
    // GET /admin/pm/{id}/edit
    // ================================================================
    public function edit(int $id)
    {
        $schedule = $this->model->getById($id);
        if (! $schedule) {
            return redirect()->to('/admin/pm')->with('error', 'Schedule tidak ditemukan.');
        }

        return view('pm/form', [
            'title'                => 'Edit Schedule PM',
            'schedule'             => $schedule,
            'assets'               => $this->model->getAssetsDropdown(),
            'technicians'          => $this->model->getTechniciansDropdown(),
            'recurring_opts'       => PreventiveMaintenanceModel::RECURRING_LABELS,
            'priority_opts'        => ['rendah', 'sedang', 'tinggi', 'kritis'],
            'preselected_asset_id' => null,
        ]);
    }

    // ================================================================
    // POST /admin/pm/{id}/update
    // ================================================================
    public function update(int $id)
    {
        $schedule = $this->model->getById($id);
        if (! $schedule) {
            return redirect()->to('/admin/pm')->with('error', 'Schedule tidak ditemukan.');
        }

        $rules = [
            'schedule_type' => 'required|in_list[pm,calibration]',
            'title'         => 'required|min_length[5]|max_length[200]',
            'recurring'     => 'required|in_list[daily,weekly,monthly,quarterly,biannual,yearly]',
            'priority'      => 'required|in_list[rendah,sedang,tinggi,kritis]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $this->model->update($id, [
            'schedule_type'      => $this->request->getPost('schedule_type'),
            'title'              => $this->request->getPost('title'),
            'description'        => $this->request->getPost('description'),
            'recurring'          => $this->request->getPost('recurring'),
            'priority'           => $this->request->getPost('priority'),
            'assigned_to'        => $this->request->getPost('assigned_to') ?: null,
            'estimated_duration' => $this->request->getPost('estimated_duration') ?: null,
            'is_active'          => (int) $this->request->getPost('is_active'),
        ]);

        return redirect()->to('/admin/pm/' . $id)
            ->with('success', 'Schedule PM berhasil diperbarui.');
    }

    // ================================================================
    // POST /admin/pm/{id}/delete
    // ================================================================
    public function delete(int $id)
    {
        if (session()->get('role') !== 'admin') {
            return redirect()->to('/admin/pm')->with('error', 'Hanya administrator yang dapat menghapus jadwal PM.');
        }

        $schedule = $this->model->getById($id);
        if (! $schedule) {
            return redirect()->to('/admin/pm')->with('error', 'Schedule tidak ditemukan.');
        }

        $this->model->delete($id);

        return redirect()->to('/admin/pm')
            ->with('success', 'Schedule PM <strong>' . esc($schedule['title']) . '</strong> berhasil dihapus.');
    }

    // ================================================================
    // POST /admin/pm/{id}/mark-done
    // Catat pelaksanaan PM + auto-generate Work Order
    // ================================================================
    public function markAsDone(int $id)
    {
        $schedule = $this->model->getById($id);
        if (! $schedule) {
            return redirect()->to('/admin/pm')->with('error', 'Schedule tidak ditemukan.');
        }

        $rules = [
            'done_date'    => 'required|valid_date',
            'action_taken' => 'permit_empty|min_length[5]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $doneDate    = $this->request->getPost('done_date');
        $actionTaken = $this->request->getPost('action_taken') ?: 'PM dilakukan sesuai jadwal';
        $cost        = (float) ($this->request->getPost('cost') ?: 0);

        // 1. Update schedule: last_done & next_due
        $this->model->markAsDone($id, $doneDate, $actionTaken);

        $isCalibration = ($schedule['schedule_type'] ?? 'pm') === 'calibration';

        // 2. Auto-generate Work Order (status done)
        $woCode = $this->woModel->generateCode();
        $woId   = $this->woModel->insert([
            'wo_code'       => $woCode,
            'asset_id'      => $schedule['asset_id'],
            'type'          => $isCalibration ? 'kalibrasi_alat' : 'preventive',
            'priority'      => $schedule['priority'],
            'status'        => 'done',
            'problem_desc'  => ($isCalibration ? 'Kalibrasi Berkala: ' : 'PM Rutin: ') . $schedule['title'],
            'action_taken'  => $actionTaken,
            'requested_by'  => session()->get('user_id'),
            'assigned_to'   => $schedule['assigned_to'],
            'start_date'    => $doneDate,
            'finish_date'   => $doneDate,
            'cost'          => $cost,
            'notes'         => 'Auto-generated dari PM Schedule #' . $id,
        ]);

        // Sync with asset calibration columns if type is calibration
        if ($isCalibration) {
            $nextDue = date('Y-m-d', strtotime($doneDate . ' + ' . $schedule['interval_days'] . ' days'));
            $assetModel = new \App\Models\InventoryAssetModel();
            $assetModel->update((int) $schedule['asset_id'], [
                'requires_calibration' => 1,
                'last_calibration_date' => $doneDate,
                'next_calibration_date' => $nextDue,
            ]);
        }

        // 3. Log maintenance
        if ($woId) {
            $this->logModel->record(
                (int) $schedule['asset_id'],
                'pemeliharaan_preventif',
                $actionTaken,
                [], [],
                $cost,
                $woId
            );
        }

        return redirect()->to('/admin/pm/' . $id)
            ->with('success', 'PM berhasil dicatat. Work Order <strong>' . $woCode . '</strong> otomatis dibuat.');
    }

    // ================================================================
    // GET /admin/pm/calendar (optional JSON endpoint untuk FullCalendar)
    // ================================================================
    public function calendarJson()
    {
        $start = $this->request->getGet('start') ?? date('Y-m-01');
        $end   = $this->request->getGet('end')   ?? date('Y-m-t', strtotime('+1 month'));

        $data = $this->model->getCalendarData($start, $end);

        // Format untuk FullCalendar
        $events = [];
        foreach ($data as $pm) {
            $events[] = [
                'id'             => $pm['id'],
                'title'          => $pm['asset_name'] . ' — ' . $pm['title'],
                'start'          => $pm['next_due'],
                'backgroundColor'=> $this->getColorByPriority($pm['priority']),
                'borderColor'    => $this->getColorByPriority($pm['priority']),
                'url'            => base_url('admin/pm/' . $pm['id']),
            ];
        }

        return $this->response->setJSON($events);
    }

    // ================================================================
    // PRIVATE
    // ================================================================

    private function getColorByPriority(string $priority): string
    {
        return match ($priority) {
            'kritis' => '#ef4444',
            'tinggi' => '#f97316',
            'sedang' => '#eab308',
            'rendah' => '#22c55e',
            default  => '#6b7280',
        };
    }
}
