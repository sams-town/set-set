<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\InventoryAssetModel;
use App\Models\MaintenanceChecklistModel;

/**
 * MaintenanceChecklistController — Controller untuk checklist pemeliharaan
 */
class MaintenanceChecklistController extends BaseController
{
    protected InventoryAssetModel      $assetModel;
    protected MaintenanceChecklistModel $checklistModel;

    protected $db;

    public function __construct()
    {
        $this->assetModel     = new InventoryAssetModel();
        $this->checklistModel = new MaintenanceChecklistModel();
        $this->db             = \Config\Database::connect();
    }

    private const PER_PAGE = 20;

    /**
     * Daftar semua riwayat checklist
     * GET /admin/checklist
     */
    public function index()
    {
        $filters = [
            'search'       => $this->request->getGet('search'),
            'date_from'    => $this->request->getGet('date_from'),
            'date_to'      => $this->request->getGet('date_to'),
            'technician_id'=> $this->request->getGet('technician_id'),
        ];

        $page       = max(1, (int) $this->request->getGet('page'));
        $offset     = ($page - 1) * self::PER_PAGE;
        $total      = $this->checklistModel->countFiltered($filters);
        $checklists = $this->checklistModel->getList($filters, self::PER_PAGE, $offset);
        $totalPages = $total > 0 ? (int) ceil($total / self::PER_PAGE) : 1;

        // Dropdown teknisi
        $technicians = $this->db->table('users')
            ->select('id, name')
            ->whereIn('role', ['admin', 'technician'])
            ->where('is_active', 1)
            ->where('deleted_at', null)
            ->orderBy('name')
            ->get()->getResultArray();

        return view('maintenance_checklist/index', [
            'title'        => 'Riwayat Checklist Pemeliharaan',
            'checklists'   => $checklists,
            'filters'      => $filters,
            'technicians'  => $technicians,
            'page'         => $page,
            'total_pages'  => $totalPages,
            'total_records'=> $total,
            'per_page'     => self::PER_PAGE,
        ]);
    }

    /**
     * Halaman untuk mengisi checklist dari QR scan atau dari aset
     * GET /admin/checklist/new/{assetCode}
     */
    public function new(string $assetCode)
    {
        // Cari aset
        $asset = $this->db->table('assets a')
            ->select('a.*, d.name AS department_name, l.name AS location_name')
            ->join('departments d', 'd.id = a.department_id', 'left')
            ->join('locations l', 'l.id = a.location_id', 'left')
            ->where('LOWER(a.asset_code)', strtolower($assetCode))
            ->where('a.deleted_at', null)
            ->get()
            ->getRowArray();

        if (!$asset) {
            return redirect()->to('/admin/inventory')->with('error', 'Aset tidak ditemukan');
        }

        // Dapatkan atau buat template checklist untuk kategori aset
        $template = $this->checklistModel->getTemplateForCategory($asset['category'] ?? '');
        if (!$template) {
            $templateId = $this->checklistModel->createDefaultTemplate($asset['category'] ?? '');
            $template = $this->checklistModel->getTemplateForCategory($asset['category'] ?? '');
        }

        // Buat checklist instance baru
        $userId = session()->get('user_id');
        $checklistId = $this->checklistModel->createChecklistFromTemplate(
            $asset['id'],
            $template['id'],
            $userId
        );

        // Redirect ke halaman edit checklist
        return redirect()->to("/admin/checklist/{$checklistId}/edit");
    }

    /**
     * Edit checklist instance
     * GET /admin/checklist/{checklistId}/edit
     */
    public function edit(int $checklistId)
    {
        $checklist = $this->checklistModel->getChecklistInstance($checklistId);
        if (!$checklist) {
            return redirect()->to('/admin/inventory')->with('error', 'Checklist tidak ditemukan');
        }

        return view('maintenance_checklist/edit', [
            'title'       => 'Isi Checklist Pemeliharaan',
            'checklist'   => $checklist,
        ]);
    }

    /**
     * Simpan checklist
     * POST /admin/checklist/{checklistId}
     */
    public function update(int $checklistId)
    {
        $checklist = $this->checklistModel->getChecklistInstance($checklistId);
        if (!$checklist) {
            return redirect()->to('/admin/inventory')->with('error', 'Checklist tidak ditemukan');
        }

        // Simpan jawaban item checklist
        $answers = $this->request->getPost('answers') ?? [];
        $this->checklistModel->saveChecklistAnswers($checklistId, $answers);

        // Simpan catatan dan tanda tangan
        $this->checklistModel->update($checklistId, [
            'notes'                => $this->request->getPost('notes'),
            'technician_signature' => $this->request->getPost('technician_signature') ?: null,
            'supervisor_signature' => $this->request->getPost('supervisor_signature') ?: null,
            'user_signature'       => $this->request->getPost('user_signature') ?: null,
        ]);

        return redirect()->to("/admin/checklist/{$checklistId}")
            ->with('success', 'Checklist berhasil disimpan');
    }

    /**
     * View checklist (readonly)
     * GET /admin/checklist/{checklistId}
     */
    public function show(int $checklistId)
    {
        $checklist = $this->checklistModel->getChecklistInstance($checklistId);
        if (!$checklist) {
            return redirect()->to('/admin/inventory')->with('error', 'Checklist tidak ditemukan');
        }

        return view('maintenance_checklist/show', [
            'title'       => 'Detail Checklist Pemeliharaan',
            'checklist'   => $checklist,
        ]);
    }
}
