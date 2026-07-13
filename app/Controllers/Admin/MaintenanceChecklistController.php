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

        // Simpan catatan dan lain-lain
        $notes = $this->request->getPost('notes');
        $this->checklistModel->update($checklistId, ['notes' => $notes]);

        return redirect()->to("/admin/checklist/{$checklistId}/edit")
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
