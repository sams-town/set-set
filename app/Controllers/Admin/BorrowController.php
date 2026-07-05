<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\BorrowModel;
use App\Models\InventoryAssetModel;
use App\Models\UserModel;
use App\Models\MaintenanceLogModel;

class BorrowController extends BaseController
{
    protected BorrowModel          $borrowModel;
    protected InventoryAssetModel  $assetModel;
    protected UserModel            $userModel;
    protected MaintenanceLogModel  $logModel;

    public function __construct()
    {
        $this->borrowModel = new BorrowModel();
        $this->assetModel  = new InventoryAssetModel();
        $this->userModel   = new UserModel();
        $this->logModel    = new MaintenanceLogModel();
    }

    // ── Helper: dept scope untuk non-admin ───────────────────────
    private function getDeptScope(): ?int
    {
        if (session()->get('role') === 'admin') { return null; }
        return session()->get('department_id') ?: null;
    }

    // GET /admin/borrows
    public function index()
    {
        $deptScope = $this->getDeptScope();

        $filters = [
            'status'        => $this->request->getGet('status'),
            'user_id'       => $this->request->getGet('user_id'),
            'search'        => $this->request->getGet('search'),
            'department_id' => $deptScope, // null = semua (admin), int = filter dept
        ];

        return view('borrows/index', [
            'title'   => 'Peminjaman Aset',
            'borrows' => $this->borrowModel->getWithRelations($filters),
            'filters' => $filters,
            'overdue' => count($this->borrowModel->getOverdue($deptScope)),
        ]);
    }

    // GET /admin/borrows/new
    public function create()
    {
        $deptScope = $this->getDeptScope();

        // Aset tersedia — scope ke dept jika bukan admin
        $assetFilters = ['status' => 'tersedia'];
        if ($deptScope !== null) {
            $assetFilters['department_id'] = $deptScope;
        }
        $assets = $this->assetModel->getList($assetFilters, 500, 0);

        return view('borrows/create', [
            'title'  => 'Catat Peminjaman',
            'assets' => $assets,
            'users'  => $this->userModel->getActiveUsers(),
        ]);
    }

    // POST /admin/borrows
    public function store()
    {
        if (! $this->validate([
            'asset_id'    => 'required|integer',
            'user_id'     => 'required|integer',
            'borrow_date' => 'required|valid_date',
        ])) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $assetId = (int) $this->request->getPost('asset_id');
        $asset   = $this->assetModel->getById($assetId);

        if (! $asset || $asset['status'] !== 'tersedia') {
            return redirect()->back()->withInput()->with('error', 'Aset tidak tersedia untuk dipinjam.');
        }

        $borrowCode = $this->borrowModel->generateCode();

        $this->borrowModel->insert([
            'borrow_code'      => $borrowCode,
            'asset_id'         => $assetId,
            'user_id'          => $this->request->getPost('user_id'),
            'borrow_date'      => $this->request->getPost('borrow_date'),
            'return_date_plan' => $this->request->getPost('return_date_plan') ?: null,
            'status'           => 'dipinjam',
            'purpose'          => $this->request->getPost('purpose'),
            'approved_by'      => session()->get('user_id'),
        ]);

        // Update status aset ke dipinjam
        $this->assetModel->update($assetId, ['status' => 'dipinjam']);
        $this->logModel->record($assetId, 'dipinjam', 'Dipinjam dengan kode: ' . $borrowCode);

        return redirect()->to('/admin/borrows')->with('success', 'Peminjaman berhasil dicatat.');
    }

    // GET /admin/borrows/{id}
    public function show(int $id)
    {
        $borrow = $this->borrowModel->getDetailById($id);
        if (! $borrow) {
            return redirect()->to('/admin/borrows')->with('error', 'Data peminjaman tidak ditemukan.');
        }

        // Guard: non-admin hanya bisa lihat peminjaman dari dept sendiri
        $deptScope = $this->getDeptScope();
        if ($deptScope !== null) {
            $asset = $this->assetModel->getById((int) $borrow['asset_id']);
            if ($asset && (int)($asset['department_id'] ?? 0) !== $deptScope) {
                return redirect()->to('/admin/borrows')
                    ->with('error', 'Anda tidak memiliki akses ke data peminjaman ini.');
            }
        }

        return view('borrows/detail', [
            'title'  => 'Detail Peminjaman',
            'borrow' => $borrow,
        ]);
    }

    // POST /admin/borrows/{id}/return
    public function returnAsset(int $id)
    {
        $borrow = $this->borrowModel->find($id);
        if (! $borrow || $borrow['status'] === 'dikembalikan') {
            return redirect()->to('/admin/borrows')->with('error', 'Data tidak valid.');
        }

        $returnDate = date('Y-m-d');

        // Update borrow
        $this->borrowModel->update($id, [
            'return_date_actual' => $returnDate,
            'status'             => 'dikembalikan',
            'notes'              => $this->request->getPost('notes'),
        ]);

        // Update status aset kembali ke tersedia
        $this->assetModel->update((int) $borrow['asset_id'], ['status' => 'tersedia']);
        $this->logModel->record((int) $borrow['asset_id'], 'dikembalikan', 'Dikembalikan pada ' . $returnDate);

        return redirect()->to('/admin/borrows/' . $id)->with('success', 'Aset berhasil dikembalikan.');
    }
}
