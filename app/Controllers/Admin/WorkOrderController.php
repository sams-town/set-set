<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\WorkOrderModel;
use App\Models\MaintenanceLogModel;
use App\Services\WhatsAppService;

/**
 * WorkOrderController
 *
 * Alur: User Input Keluhan + Foto → Assessment Teknisi (prioritas)
 *       → Pengerjaan + Foto Before → Testing + Foto After → Close WO
 *
 * Routes:
 *   GET  /admin/work-orders             → index (dashboard KPI + tabel)
 *   GET  /admin/work-orders/new         → create
 *   POST /admin/work-orders             → store
 *   GET  /admin/work-orders/{id}        → show
 *   GET  /admin/work-orders/{id}/edit   → edit
 *   POST /admin/work-orders/{id}/update → update
 *   POST /admin/work-orders/{id}/delete → delete
 *   POST /admin/work-orders/{id}/report → addReport
 */
class WorkOrderController extends BaseController
{
    protected WorkOrderModel      $model;
    protected MaintenanceLogModel $logModel;
    protected WhatsAppService     $wa;

    private const PER_PAGE = 20;

    private const STATUS_LIST   = ['open', 'in_progress', 'waiting_part', 'done', 'cancelled'];
    private const PRIORITY_LIST = ['rendah', 'sedang', 'tinggi', 'kritis'];
    private const TYPE_LIST     = ['corrective', 'preventive', 'inspection', 'kalibrasi_alat'];

    public function __construct()
    {
        $this->model    = new WorkOrderModel();
        $this->logModel = new MaintenanceLogModel();
        $this->wa       = new WhatsAppService();
    }

    // ================================================================
    // Helper — dept scope untuk non-admin
    // ================================================================
    private function getDeptScope(): ?int
    {
        if (session()->get('role') === 'admin') { return null; }
        return session()->get('department_id') ?: null;
    }

    // ================================================================
    // GET /admin/work-orders
    // ================================================================
    public function index()
    {
        $deptScope = $this->getDeptScope();

        $filters = [
            'search'        => $this->request->getGet('search'),
            'status'        => $this->request->getGet('status'),
            'priority'      => $this->request->getGet('priority'),
            'type'          => $this->request->getGet('type'),
            'assigned_to'   => $this->request->getGet('assigned_to'),
            // Non-admin dikunci ke dept sendiri, admin bisa filter bebas
            'department_id' => $deptScope ?? $this->request->getGet('department_id'),
            'category_wo'   => $this->request->getGet('category_wo'),
            'overdue'       => $this->request->getGet('overdue'),
        ];

        $page       = max(1, (int) $this->request->getGet('page'));
        $offset     = ($page - 1) * self::PER_PAGE;
        $total      = $this->model->countFiltered($filters);
        $workOrders = $this->model->getList($filters, self::PER_PAGE, $offset);
        $totalPages = $total > 0 ? (int) ceil($total / self::PER_PAGE) : 1;

        // Dashboard KPI stats
        $dashStats    = $this->model->getDashboardStats();
        $monthlyTrend = $this->model->getMonthlyTrend(6);
        $overdueCount = $this->model->getOverdueCount();

        // Chart data
        $chartTrend = json_encode([
            'labels'  => array_column($monthlyTrend, 'bulan'),
            'total'   => array_column($monthlyTrend, 'total'),
            'selesai' => array_column($monthlyTrend, 'selesai'),
        ]);

        return view('work_orders/index', [
            'title'         => 'Work Order',
            'work_orders'   => $workOrders,
            'filters'       => $filters,
            'page'          => $page,
            'total_pages'   => $totalPages,
            'total_records' => $total,
            'per_page'      => self::PER_PAGE,
            'status_list'   => self::STATUS_LIST,
            'priority_list' => self::PRIORITY_LIST,
            'technicians'   => $this->model->getTechniciansDropdown(),
            'departments'   => $this->model->getDepartmentsDropdown(),
            'categories_wo' => WorkOrderModel::CATEGORIES_WO,

            // Dashboard KPI
            'dash_stats'    => $dashStats,
            'overdue_count' => $overdueCount,
            'chart_trend'   => $chartTrend,
        ]);
    }

    // ================================================================
    // GET /admin/work-orders/new
    // ================================================================
    public function create()
    {
        $deptScope  = $this->getDeptScope();
        $preAssetId = $this->request->getGet('asset_id');
        $preType    = $this->request->getGet('type') ?? 'corrective';

        // Scope aset untuk non-admin: hanya tampilkan aset dari dept sendiri
        $assetsDropdown = $this->model->getAssetsDropdown($deptScope);

        return view('work_orders/form', [
            'title'         => 'Buat Work Order',
            'wo'            => null,
            'assets'        => $assetsDropdown,
            'technicians'   => $this->model->getTechniciansDropdown(),
            'vendors'       => $this->model->getVendorsDropdown(),
            'departments'   => $this->model->getDepartmentsDropdown(),
            'locations'     => $this->model->getLocationsDropdown(),
            'status_list'   => self::STATUS_LIST,
            'priority_list' => self::PRIORITY_LIST,
            'type_list'     => self::TYPE_LIST,
            'damage_types'  => WorkOrderModel::DAMAGE_TYPES,
            'categories_wo' => WorkOrderModel::CATEGORIES_WO,
            'sla_hours'     => WorkOrderModel::SLA_HOURS,
            'pre_asset_id'  => $preAssetId,
            'pre_type'      => $preType,
            'dept_scope'    => $deptScope,
        ]);
    }

    // ================================================================
    // POST /admin/work-orders
    // ================================================================
    public function store()
    {
        if (! $this->validate($this->storeRules())) {
            return redirect()->back()->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $woCode = $this->model->generateCode();

        // Upload foto keluhan dari user
        $photoComplaint = $this->handlePhotoUpload('photo_complaint');

        $woId = $this->model->insert([
            'wo_code'        => $woCode,
            'asset_id'       => (int) $this->request->getPost('asset_id'),
            'requested_by'   => session()->get('user_id'),
            'reporter_name'  => $this->request->getPost('reporter_name') ?: session()->get('user_name'),
            // Auto-isi dept dari session jika user bukan admin
            'department_id'  => $this->request->getPost('department_id') ?: session()->get('department_id') ?: null,
            'location_id'    => $this->request->getPost('location_id')    ?: null,
            'assigned_to'    => $this->request->getPost('assigned_to')    ?: null,
            'vendor_id'      => $this->request->getPost('vendor_id')      ?: null,
            'type'           => $this->request->getPost('type'),
            'damage_type'    => $this->request->getPost('damage_type'),
            'category_wo'    => $this->request->getPost('category_wo'),
            'priority'       => $this->request->getPost('priority'),
            'status'         => 'open',
            'problem_desc'   => $this->request->getPost('problem_desc'),
            'photo_complaint'=> $photoComplaint,
            'scheduled_date' => $this->request->getPost('scheduled_date') ?: null,
            'target_date'    => $this->request->getPost('target_date')    ?: null,
            'notes'          => $this->request->getPost('notes'),
        ]);

        if (! $woId) {
            return redirect()->back()->withInput()
                ->with('error', 'Gagal menyimpan Work Order. Silakan coba lagi.');
        }

        $this->logModel->record(
            (int) $this->request->getPost('asset_id'),
            'perbaikan_mulai',
            "WO {$woCode} dibuat oleh " . session()->get('user_name'),
            [], [], 0, $woId
        );

        $wo = $this->model->getById($woId);
        if ($wo) { $this->notifyWoNew($wo); }

        return redirect()->to('/admin/work-orders/' . $woId)
            ->with('success', "Work Order <strong>{$woCode}</strong> berhasil dibuat.");
    }

    // ================================================================
    // GET /admin/work-orders/{id}
    // ================================================================
    public function show(int $id)
    {
        $wo = $this->model->getById($id);
        if (! $wo) {
            return redirect()->to('/admin/work-orders')->with('error', 'Work Order tidak ditemukan.');
        }

        // Guard: non-admin hanya bisa lihat WO dari dept sendiri
        $deptScope = $this->getDeptScope();
        if ($deptScope !== null && !empty($wo['department_id'])
            && (int) $wo['department_id'] !== $deptScope) {
            return redirect()->to('/admin/work-orders')
                ->with('error', 'Anda tidak memiliki akses ke Work Order ini.');
        }

        // Hitung SLA status
        $slaStatus = $this->calcSlaStatus($wo);

        return view('work_orders/detail', [
            'title'      => 'Detail WO — ' . $wo['wo_code'],
            'wo'         => $wo,
            'logs'       => $this->logModel->getByAsset((int) $wo['asset_id']),
            'sla_status' => $slaStatus,
        ]);
    }

    // ================================================================
    // GET /admin/work-orders/{id}/edit
    // ================================================================
    public function edit(int $id)
    {
        $wo = $this->model->getById($id);
        if (! $wo) {
            return redirect()->to('/admin/work-orders')->with('error', 'Work Order tidak ditemukan.');
        }

        $role = session()->get('role');
        $userId = (int) session()->get('user_id');
        if ($role === 'technician') {
            if ((int) $wo['assigned_to'] !== $userId) {
                return redirect()->to('/admin/work-orders')->with('error', 'Anda hanya dapat merespon/mengedit Work Order yang ditugaskan kepada Anda.');
            }
        }

        return view('work_orders/form', [
            'title'         => 'Edit Work Order',
            'wo'            => $wo,
            'assets'        => $this->model->getAssetsDropdown(),
            'technicians'   => $this->model->getTechniciansDropdown(),
            'vendors'       => $this->model->getVendorsDropdown(),
            'departments'   => $this->model->getDepartmentsDropdown(),
            'locations'     => $this->model->getLocationsDropdown(),
            'status_list'   => self::STATUS_LIST,
            'priority_list' => self::PRIORITY_LIST,
            'type_list'     => self::TYPE_LIST,
            'damage_types'  => WorkOrderModel::DAMAGE_TYPES,
            'categories_wo' => WorkOrderModel::CATEGORIES_WO,
            'sla_hours'     => WorkOrderModel::SLA_HOURS,
            'pre_asset_id'  => null,
            'pre_type'      => null,
        ]);
    }

    // ================================================================
    // POST /admin/work-orders/{id}/update
    // ================================================================
    public function update(int $id)
    {
        $wo = $this->model->getById($id);
        if (! $wo) {
            return redirect()->to('/admin/work-orders')->with('error', 'Work Order tidak ditemukan.');
        }

        $role = session()->get('role');
        $userId = (int) session()->get('user_id');
        if ($role === 'technician') {
            if ((int) $wo['assigned_to'] !== $userId) {
                return redirect()->to('/admin/work-orders')->with('error', 'Anda hanya dapat merespon/mengedit Work Order yang ditugaskan kepada Anda.');
            }
        }

        if (! $this->validate($this->updateRules())) {
            return redirect()->back()->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $oldStatus = $wo['status'];
        $newStatus = $this->request->getPost('status');

        // Handle upload foto before/after
        $photoBefore = $this->handlePhotoUpload('photo_before') ?? $wo['photo_before'];
        $photoAfter  = $this->handlePhotoUpload('photo_after')  ?? $wo['photo_after'];

        $updateData = [
            'assigned_to'      => $this->request->getPost('assigned_to')      ?: null,
            'vendor_id'        => $this->request->getPost('vendor_id')        ?: null,
            'department_id'    => $this->request->getPost('department_id')    ?: null,
            'location_id'      => $this->request->getPost('location_id')      ?: null,
            'type'             => $this->request->getPost('type'),
            'damage_type'      => $this->request->getPost('damage_type'),
            'category_wo'      => $this->request->getPost('category_wo'),
            'priority'         => $this->request->getPost('priority'),
            'status'           => $newStatus,
            'problem_desc'     => $this->request->getPost('problem_desc'),
            'assessment_notes' => $this->request->getPost('assessment_notes'),
            'action_taken'     => $this->request->getPost('action_taken'),
            'material_used'    => $this->request->getPost('material_used'),
            'material_cost'    => $this->request->getPost('material_cost')    ?: null,
            'labor_cost'       => $this->request->getPost('labor_cost')       ?: null,
            'photo_before'     => $photoBefore,
            'photo_after'      => $photoAfter,
            'scheduled_date'   => $this->request->getPost('scheduled_date')   ?: null,
            'target_date'      => $this->request->getPost('target_date')      ?: null,
            'start_date'       => $this->request->getPost('start_date')       ?: null,
            'finish_date'      => $this->request->getPost('finish_date')      ?: null,
            'notes'            => $this->request->getPost('notes'),
        ];

        $this->model->update($id, $updateData);

        // Log perubahan status
        if ($oldStatus !== $newStatus) {
            $logAction = $newStatus === 'done' ? 'perbaikan_selesai' : 'diubah';
            $this->logModel->record(
                (int) $wo['asset_id'],
                $logAction,
                "Status WO {$wo['wo_code']}: {$oldStatus} → {$newStatus}",
                [], [],
                (float) ($this->request->getPost('labor_cost') ?: 0),
                $id
            );

            $updatedWo = $this->model->getById($id);
            if ($updatedWo) { $this->notifyWoStatusChange($updatedWo, $oldStatus); }
        }

        return redirect()->to('/admin/work-orders/' . $id)
            ->with('success', 'Work Order berhasil diperbarui.');
    }

    // ================================================================
    // POST /admin/work-orders/{id}/delete
    // ================================================================
    public function delete(int $id)
    {
        if (session()->get('role') !== 'admin') {
            return redirect()->to('/admin/work-orders')->with('error', 'Hanya administrator yang dapat menghapus Work Order.');
        }

        $wo = $this->model->getById($id);
        if (! $wo) {
            return redirect()->to('/admin/work-orders')->with('error', 'Work Order tidak ditemukan.');
        }

        if (! in_array($wo['status'], ['open', 'cancelled'])) {
            return redirect()->to('/admin/work-orders/' . $id)
                ->with('error', 'WO yang sedang berjalan tidak dapat dihapus.');
        }

        // Hapus foto terkait
        foreach (['photo_complaint', 'photo_before', 'photo_after'] as $field) {
            if (! empty($wo[$field])) {
                $path = FCPATH . 'uploads/work_orders/' . $wo[$field];
                if (file_exists($path)) { unlink($path); }
            }
        }

        $this->model->delete($id);

        return redirect()->to('/admin/work-orders')
            ->with('success', "Work Order <strong>{$wo['wo_code']}</strong> berhasil dihapus.");
    }

    // ================================================================
    // POST /admin/work-orders/{id}/report
    // Tambah laporan / catatan perbaikan + foto
    // ================================================================
    public function addReport(int $id)
    {
        $wo = $this->model->getById($id);
        if (! $wo) {
            return redirect()->to('/admin/work-orders')->with('error', 'Work Order tidak ditemukan.');
        }

        if (! $this->validate(['report_description' => 'required|min_length[5]'])) {
            return redirect()->back()->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $description = $this->request->getPost('report_description');
        $cost        = (float) ($this->request->getPost('report_cost') ?: 0);
        $action      = $this->request->getPost('report_action') ?: 'inspeksi';

        // Upload foto laporan (before atau after tergantung tahap)
        $photoField = ($action === 'perbaikan_selesai') ? 'photo_after' : 'photo_before';
        $photoName  = $this->handlePhotoUpload('report_photo');
        if ($photoName && empty($wo[$photoField])) {
            $this->model->update($id, [$photoField => $photoName]);
        }

        $this->logModel->record(
            (int) $wo['asset_id'],
            $action,
            $description,
            [], [], $cost, $id
        );

        // Update action_taken & biaya
        $updateData = ['action_taken' => $description];
        if ($cost > 0) {
            $existing = (float) ($wo['labor_cost'] ?? 0);
            $updateData['labor_cost'] = $existing + $cost;
            $updateData['cost']       = ((float)($wo['material_cost'] ?? 0)) + $existing + $cost;
        }
        $this->model->update($id, $updateData);

        $this->notifyNewReport([
            'asset_name'  => $wo['asset_name'],
            'asset_code'  => $wo['asset_code'],
            'action'      => $action,
            'description' => $description,
            'cost'        => $cost,
            'user_name'   => session()->get('user_name'),
            'wo_code'     => $wo['wo_code'],
        ]);

        return redirect()->to('/admin/work-orders/' . $id)
            ->with('success', 'Laporan berhasil ditambahkan.');
    }

    // ================================================================
    // PRIVATE — SLA Calculation
    // ================================================================

    private function calcSlaStatus(array $wo): array
    {
        $slaHours  = (int) ($wo['sla_hours'] ?? WorkOrderModel::SLA_HOURS[$wo['priority']] ?? 24);
        $created   = strtotime($wo['created_at']);
        $deadline  = $created + ($slaHours * 3600);
        $now       = time();

        if (in_array($wo['status'], ['done', 'cancelled'])) {
            $finish = $wo['finish_date'] ? strtotime($wo['finish_date'] . ' 23:59:59') : $now;
            $met    = $finish <= $deadline;
            return [
                'met'       => $met,
                'label'     => $met ? 'SLA Terpenuhi' : 'SLA Terlampaui',
                'color'     => $met ? 'text-green-600' : 'text-red-600',
                'bg'        => $met ? 'bg-green-50'   : 'bg-red-50',
                'deadline'  => date('d M Y H:i', $deadline),
                'hours_left'=> null,
            ];
        }

        $hoursLeft = ($deadline - $now) / 3600;
        if ($hoursLeft < 0) {
            return [
                'met'       => false,
                'label'     => 'Overdue ' . round(abs($hoursLeft), 1) . ' jam',
                'color'     => 'text-red-600',
                'bg'        => 'bg-red-50',
                'deadline'  => date('d M Y H:i', $deadline),
                'hours_left'=> round($hoursLeft, 1),
            ];
        }

        return [
            'met'       => true,
            'label'     => round($hoursLeft, 1) . ' jam tersisa',
            'color'     => $hoursLeft < 2 ? 'text-orange-600' : 'text-green-600',
            'bg'        => $hoursLeft < 2 ? 'bg-orange-50'   : 'bg-green-50',
            'deadline'  => date('d M Y H:i', $deadline),
            'hours_left'=> round($hoursLeft, 1),
        ];
    }

    // ================================================================
    // PRIVATE — Photo Upload (compress ke WebP)
    // ================================================================

    private function handlePhotoUpload(string $field): ?string
    {
        $photo = $this->request->getFile($field);

        if (! $photo || ! $photo->isValid() || $photo->hasMoved()) {
            return null;
        }

        $allowed = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
        if (! in_array($photo->getMimeType(), $allowed)) { return null; }
        if ($photo->getSizeByUnit('mb') > 5) { return null; }

        $uploadDir = FCPATH . 'uploads/work_orders/';
        if (! is_dir($uploadDir)) { mkdir($uploadDir, 0755, true); }

        $baseName  = pathinfo($photo->getRandomName(), PATHINFO_FILENAME);
        $finalName = $baseName . '.webp';
        $tmpPath   = $photo->getTempName();

        if ($this->compressToWebp($tmpPath, $photo->getMimeType(), $uploadDir . $finalName)) {
            return $finalName;
        }

        $originalName = $photo->getRandomName();
        $photo->move($uploadDir, $originalName);
        return $originalName;
    }

    private function compressToWebp(string $srcPath, string $mimeType, string $destPath): bool
    {
        if (! function_exists('imagewebp')) { return false; }

        $src = match ($mimeType) {
            'image/jpeg' => @imagecreatefromjpeg($srcPath),
            'image/png'  => @imagecreatefrompng($srcPath),
            'image/webp' => @imagecreatefromwebp($srcPath),
            'image/gif'  => @imagecreatefromgif($srcPath),
            default      => false,
        };
        if (! $src) { return false; }

        $origW = imagesx($src); $origH = imagesy($src);
        if ($origW > 1200) {
            $newW    = 1200;
            $newH    = (int) round($origH * (1200 / $origW));
            $resized = imagecreatetruecolor($newW, $newH);
            imagecopyresampled($resized, $src, 0, 0, 0, 0, $newW, $newH, $origW, $origH);
            imagedestroy($src);
            $src = $resized;
        }

        $result = imagewebp($src, $destPath, 82);
        imagedestroy($src);
        return $result;
    }

    // ================================================================
    // PRIVATE — WhatsApp Notifications
    // ================================================================

    private function notifyWoNew(array $wo): void
    {
        try {
            $message = WhatsAppService::buildWoNewMessage($wo);
            $this->wa->sendToAdmins($message);
            if (! empty($wo['assigned_to_phone'])) {
                $this->wa->send($wo['assigned_to_phone'], $message);
            }
        } catch (\Throwable $e) {
            log_message('error', '[WA notifyWoNew] ' . $e->getMessage());
        }
    }

    private function notifyWoStatusChange(array $wo, string $oldStatus): void
    {
        try {
            $message = WhatsAppService::buildWoStatusMessage($wo, $oldStatus);
            $this->wa->sendToAdmins($message);
            if (! empty($wo['requested_by_phone'])) {
                $this->wa->send($wo['requested_by_phone'], $message);
            }
        } catch (\Throwable $e) {
            log_message('error', '[WA notifyWoStatusChange] ' . $e->getMessage());
        }
    }

    private function notifyNewReport(array $data): void
    {
        try {
            $message = WhatsAppService::buildReportMessage($data);
            $this->wa->sendToAdmins($message);
        } catch (\Throwable $e) {
            log_message('error', '[WA notifyNewReport] ' . $e->getMessage());
        }
    }

    // ================================================================
    // PRIVATE — Validation Rules
    // ================================================================

    private function storeRules(): array
    {
        return [
            'asset_id'     => 'required|integer',
            'type'         => 'required|in_list[preventive,corrective,inspection,kalibrasi_alat]',
            'priority'     => 'required|in_list[rendah,sedang,tinggi,kritis]',
            'problem_desc' => 'required|min_length[10]',
        ];
    }

    private function updateRules(): array
    {
        return [
            'type'     => 'required|in_list[preventive,corrective,inspection,kalibrasi_alat]',
            'priority' => 'required|in_list[rendah,sedang,tinggi,kritis]',
            'status'   => 'required|in_list[open,in_progress,waiting_part,done,cancelled]',
        ];
    }
}
