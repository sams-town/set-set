<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\InventoryAssetModel;
use App\Models\MaintenanceLogModel;

class InventoryAssetController extends BaseController
{
    protected InventoryAssetModel $model;
    protected MaintenanceLogModel $logModel;

    private const PER_PAGE = 15;

    private const CATEGORIES = [
        'Building Assets',
        'Utility Assets',
        'Clinical Assets',
        'Operational Assets',
        'ICT Assets',
        'Safety & Security Assets',
        'Transportation Assets',
        'Environmental Assets',
    ];

    // Satuan umum
    private const UNITS = [
        'unit', 'buah', 'set', 'pcs', 'lembar',
        'meter', 'kg', 'liter', 'roll', 'pasang',
    ];

    // Pilihan masa pakai untuk depresiasi garis lurus
    private const DEPRECIATION_YEARS_OPTIONS = [
        1 => '1 Tahun', 2 => '2 Tahun', 3 => '3 Tahun',
        4 => '4 Tahun', 5 => '5 Tahun', 8 => '8 Tahun',
        10 => '10 Tahun', 15 => '15 Tahun', 20 => '20 Tahun',
    ];

    // Pilihan interval PM
    private const PM_INTERVALS = [
        7   => 'Setiap 1 Minggu',
        14  => 'Setiap 2 Minggu',
        30  => 'Setiap 1 Bulan',
        60  => 'Setiap 2 Bulan',
        90  => 'Setiap 3 Bulan (Kuartal)',
        180 => 'Setiap 6 Bulan',
        365 => 'Setiap 1 Tahun',
    ];

    public function __construct()
    {
        $this->model    = new InventoryAssetModel();
        $this->logModel = new MaintenanceLogModel();
    }

    // ---------------------------------------------------------------
    // Helper: apakah user dibatasi per departemen?
    // Admin = lihat semua, user/technician = departemennya saja
    // ---------------------------------------------------------------
    private function getDeptScope(): ?int
    {
        $role = session()->get('role');
        if ($role === 'admin') {
            return null; // admin lihat semua
        }
        return session()->get('department_id') ?: null;
    }

    // ---------------------------------------------------------------
    // GET /admin/inventory
    // ---------------------------------------------------------------
    public function index()
    {
        $deptScope = $this->getDeptScope();

        $filters = [
            'search'           => $this->request->getGet('search'),
            'status'           => $this->request->getGet('status'),
            'condition'        => $this->request->getGet('condition'),
            'category'         => $this->request->getGet('category'),
            // Admin bisa filter bebas, user/technician dikunci ke dept sendiri
            'department_id'    => $deptScope ?? $this->request->getGet('department_id'),
            'location_id'      => $this->request->getGet('location_id'),
            'status_condition' => $this->request->getGet('status_condition'),
            'warranty_expiring'=> $this->request->getGet('warranty_expiring'),
        ];

        $page       = max(1, (int) $this->request->getGet('page'));
        $offset     = ($page - 1) * self::PER_PAGE;
        $total      = $this->model->countFiltered($filters);
        $assets     = $this->model->getList($filters, self::PER_PAGE, $offset);
        $totalPages = $total > 0 ? (int) ceil($total / self::PER_PAGE) : 1;

        return view('inventory/index', [
            'title'         => 'Inventory Aset',
            'assets'        => $assets,
            'filters'       => $filters,
            'stats'         => $this->model->getStats($deptScope),
            'departments'   => $this->model->getDepartmentsDropdown(),
            'locations'     => $this->model->getLocationsDropdown(),
            'categories'    => self::CATEGORIES,
            'page'          => $page,
            'total_pages'   => $totalPages,
            'total_records' => $total,
            'per_page'      => self::PER_PAGE,
            // Kirim ke view agar filter dept dilock untuk non-admin
            'dept_scope'    => $deptScope,
        ]);
    }

    // ---------------------------------------------------------------
    // GET /admin/inventory/new
    // ---------------------------------------------------------------
    public function create()
    {
        return view('inventory/form', [
            'title'                    => 'Tambah Aset',
            'asset'                    => null,
            'categories'               => self::CATEGORIES,
            'departments'              => $this->model->getDepartmentsDropdown(),
            'locations'                => $this->model->getLocationsDropdown(),
            'locations_with_dept'      => $this->model->getLocationsWithDept(),
            'vendors'                  => $this->model->getVendorsDropdown(),
            'units'                    => self::UNITS,
            'depreciation_years_opts'  => self::DEPRECIATION_YEARS_OPTIONS,
            'pm_intervals'             => self::PM_INTERVALS,
            'templates'                => (new \App\Models\AssetTemplateModel())->findAll(),
        ]);
    }

    // ---------------------------------------------------------------
    // POST /admin/inventory
    // ---------------------------------------------------------------
    public function store()
    {
        $rules = [
            'name'      => 'required|min_length[2]|max_length[150]',
            'category'  => 'required|max_length[50]',
            'condition' => 'required|in_list[baik,rusak_ringan,rusak_berat]',
            'status'    => 'required|max_length[50]',
            'quantity'  => 'permit_empty|integer|greater_than[0]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $category  = $this->request->getPost('category');
        $assetCode = trim($this->request->getPost('asset_code') ?? '');

        if (empty($assetCode)) {
            $assetCode = $this->model->generateCode($category);
        } elseif (! $this->model->isCodeUnique($assetCode)) {
            return redirect()->back()->withInput()
                ->with('errors', ['asset_code' => 'Kode aset sudah digunakan.']);
        }

        $photoName = $this->handlePhotoUpload();

        $insertId = $this->model->insert([
            'asset_code'          => $assetCode,
            'name'                => $this->request->getPost('name'),
            'type'                => $this->request->getPost('type'),
            'category'            => $category,
            'department_id'       => $this->request->getPost('department_id')      ?: null,
            'location_id'         => $this->request->getPost('location_id')        ?: null,
            'vendor_id'           => $this->request->getPost('vendor_id')          ?: null,
            'brand'               => $this->request->getPost('brand'),
            'model'               => $this->request->getPost('model'),
            'serial_number'       => $this->request->getPost('serial_number'),
            'purchase_date'       => $this->request->getPost('purchase_date')      ?: null,
            'purchase_price'      => $this->request->getPost('purchase_price')     ?: null,
            'warranty_expiry'     => $this->request->getPost('warranty_expiry')    ?: null,
            'status_condition'    => $this->request->getPost('status_condition')   ?: 'baru',
            'quantity'            => (int) ($this->request->getPost('quantity') ?: 1),
            'unit'                => $this->request->getPost('unit')               ?: 'unit',
            'depreciation_years'  => $this->request->getPost('depreciation_years') ?: null,
            'pm_interval_days'    => $this->request->getPost('pm_interval_days')   ?: null,
            'condition'           => $this->request->getPost('condition'),
            'status'              => $this->request->getPost('status') ?: 'Standby',
            'requires_calibration' => (int) $this->request->getPost('requires_calibration'),
            'last_calibration_date' => $this->request->getPost('last_calibration_date') ?: null,
            'next_calibration_date' => $this->request->getPost('next_calibration_date') ?: null,
            'calibration_certificate' => $this->request->getPost('calibration_certificate') ?: null,
            'calibration_vendor'  => $this->request->getPost('calibration_vendor') ?: null,
            'description'         => $this->request->getPost('description'),
            'photo'               => $photoName,
            'created_by'          => session()->get('user_id'),
        ]);

        if ($insertId) {
            $this->logModel->record(
                $insertId,
                'ditambah',
                'Aset baru ditambahkan dengan kode ' . $assetCode
            );

            helper('qrcode');
            qr_generate_asset($insertId, $assetCode);
        }

        return redirect()->to('/admin/inventory')
            ->with('success', 'Aset <strong>' . esc($assetCode) . '</strong> berhasil ditambahkan.');
    }

    // ---------------------------------------------------------------
    // GET /admin/inventory/{id}
    // ---------------------------------------------------------------
    public function show(int $id)
    {
        $asset = $this->model->getById($id);
        if (! $asset) {
            return redirect()->to('/admin/inventory')->with('error', 'Aset tidak ditemukan.');
        }

        // Guard: user/technician tidak bisa akses aset dept lain
        $deptScope = $this->getDeptScope();
        if ($deptScope !== null && (int)($asset['department_id'] ?? 0) !== $deptScope) {
            return redirect()->to('/admin/inventory')
                ->with('error', 'Anda tidak memiliki akses ke aset ini.');
        }

        // Hitung umur dan nilai buku
        $age       = InventoryAssetModel::calcAge($asset['purchase_date'] ?? null);
        $bookValue = InventoryAssetModel::calcBookValue(
            (float) ($asset['purchase_price'] ?? 0),
            (float) ($asset['depreciation_value'] ?? 0),
            $asset['purchase_date'] ?? null
        );

        // Dapatkan jadwal PM untuk aset ini
        $pmModel = new \App\Models\PreventiveMaintenanceModel();
        $pmSchedules = $this->db->table('pm_schedules ps')
            ->select('ps.*, u.name AS assigned_to_name')
            ->join('users u', 'u.id = ps.assigned_to', 'left')
            ->where('ps.asset_id', $id)
            ->where('ps.deleted_at', null)
            ->orderBy('ps.next_due', 'ASC')
            ->get()->getResultArray();

        return view('inventory/detail', [
            'title'        => 'Detail Aset — ' . $asset['name'],
            'asset'        => $asset,
            'logs'         => $this->logModel->getByAsset($id),
            'age'          => $age,
            'book_value'   => $bookValue,
            'pm_schedules' => $pmSchedules,
        ]);
    }

    // ---------------------------------------------------------------
    // GET /admin/inventory/{id}/edit
    // ---------------------------------------------------------------
    public function edit(int $id)
    {
        if (session()->get('role') !== 'admin') {
            return redirect()->to('/admin/inventory/' . $id)->with('error', 'Hanya administrator yang dapat mengubah data aset.');
        }

        $asset = $this->model->getById($id);
        if (! $asset) {
            return redirect()->to('/admin/inventory')->with('error', 'Aset tidak ditemukan.');
        }

        // Guard: user/technician tidak bisa edit aset dept lain
        $deptScope = $this->getDeptScope();
        if ($deptScope !== null && (int)($asset['department_id'] ?? 0) !== $deptScope) {
            return redirect()->to('/admin/inventory')
                ->with('error', 'Anda tidak memiliki akses untuk mengedit aset ini.');
        }

        return view('inventory/form', [
            'title'                   => 'Edit Aset',
            'asset'                   => $asset,
            'categories'              => self::CATEGORIES,
            'departments'             => $this->model->getDepartmentsDropdown(),
            'locations'               => $this->model->getLocationsDropdown(),
            'locations_with_dept'     => $this->model->getLocationsWithDept(),
            'vendors'                 => $this->model->getVendorsDropdown(),
            'units'                   => self::UNITS,
            'depreciation_years_opts' => self::DEPRECIATION_YEARS_OPTIONS,
            'pm_intervals'            => self::PM_INTERVALS,
            'templates'               => (new \App\Models\AssetTemplateModel())->findAll(),
        ]);
    }

    // ---------------------------------------------------------------
    // POST /admin/inventory/{id}/update
    // ---------------------------------------------------------------
    public function update(int $id)
    {
        if (session()->get('role') !== 'admin') {
            return redirect()->to('/admin/inventory/' . $id)->with('error', 'Hanya administrator yang dapat mengubah data aset.');
        }

        $asset = $this->model->getById($id);
        if (! $asset) {
            return redirect()->to('/admin/inventory')->with('error', 'Aset tidak ditemukan.');
        }

        $rules = [
            'name'      => 'required|min_length[2]|max_length[150]',
            'category'  => 'required|max_length[50]',
            'condition' => 'required|in_list[baik,rusak_ringan,rusak_berat]',
            'status'    => 'required|max_length[50]',
            'quantity'  => 'permit_empty|integer|greater_than[0]',
        ];

        $newCode = trim($this->request->getPost('asset_code') ?? '');
        if ($newCode && $newCode !== $asset['asset_code']) {
            if (! $this->model->isCodeUnique($newCode, $id)) {
                return redirect()->back()->withInput()
                    ->with('errors', ['asset_code' => 'Kode aset sudah digunakan.']);
            }
        }

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        // Foto: compress & replace jika ada upload baru
        $photoName = $asset['photo'];
        $newPhoto  = $this->handlePhotoUpload();
        if ($newPhoto) {
            if ($photoName && file_exists(FCPATH . 'uploads/assets/' . $photoName)) {
                unlink(FCPATH . 'uploads/assets/' . $photoName);
            }
            $photoName = $newPhoto;
        }

        $newData = [
            'asset_code'         => $newCode ?: $asset['asset_code'],
            'name'               => $this->request->getPost('name'),
            'type'               => $this->request->getPost('type'),
            'category'           => $this->request->getPost('category'),
            'department_id'      => $this->request->getPost('department_id')      ?: null,
            'location_id'        => $this->request->getPost('location_id')        ?: null,
            'vendor_id'          => $this->request->getPost('vendor_id')          ?: null,
            'brand'              => $this->request->getPost('brand'),
            'model'              => $this->request->getPost('model'),
            'serial_number'      => $this->request->getPost('serial_number'),
            'purchase_date'      => $this->request->getPost('purchase_date')      ?: null,
            'purchase_price'     => $this->request->getPost('purchase_price')     ?: null,
            'warranty_expiry'    => $this->request->getPost('warranty_expiry')    ?: null,
            'status_condition'   => $this->request->getPost('status_condition')   ?: 'baru',
            'quantity'           => (int) ($this->request->getPost('quantity') ?: 1),
            'unit'               => $this->request->getPost('unit')               ?: 'unit',
            'depreciation_years' => $this->request->getPost('depreciation_years') ?: null,
            'pm_interval_days'   => $this->request->getPost('pm_interval_days')   ?: null,
            'condition'          => $this->request->getPost('condition'),
            'status'             => $this->request->getPost('status'),
            'requires_calibration' => (int) $this->request->getPost('requires_calibration'),
            'last_calibration_date' => $this->request->getPost('last_calibration_date') ?: null,
            'next_calibration_date' => $this->request->getPost('next_calibration_date') ?: null,
            'calibration_certificate' => $this->request->getPost('calibration_certificate') ?: null,
            'calibration_vendor' => $this->request->getPost('calibration_vendor') ?: null,
            'description'        => $this->request->getPost('description'),
            'photo'              => $photoName,
        ];

        $this->model->update($id, $newData);
        $this->logModel->record($id, 'diubah', 'Data aset diperbarui.', $asset, $newData);

        return redirect()->to('/admin/inventory/' . $id)
            ->with('success', 'Aset berhasil diperbarui.');
    }

    // ---------------------------------------------------------------
    // POST /admin/inventory/{id}/delete
    // ---------------------------------------------------------------
    public function delete(int $id)
    {
        if (session()->get('role') !== 'admin') {
            return redirect()->to('/admin/inventory')->with('error', 'Hanya administrator yang dapat menghapus aset.');
        }

        $asset = $this->model->getById($id);
        if (! $asset) {
            return redirect()->to('/admin/inventory')->with('error', 'Aset tidak ditemukan.');
        }

        $this->model->delete($id);
        $this->logModel->record($id, 'dihapus', 'Aset dihapus: ' . $asset['asset_code']);

        helper('qrcode');
        qr_delete('qr_' . $asset['asset_code'] . '.png');
        qr_delete('qr_' . $asset['asset_code'] . '.svg');

        return redirect()->to('/admin/inventory')
            ->with('success', 'Aset <strong>' . esc($asset['name']) . '</strong> berhasil dihapus.');
    }

    // ---------------------------------------------------------------
    // PRIVATE — Photo Upload + Auto-Compress ke WebP
    // ---------------------------------------------------------------

    private function handlePhotoUpload(): ?string
    {
        $photo = $this->request->getFile('photo');

        if (! $photo || ! $photo->isValid() || $photo->hasMoved()) {
            return null;
        }

        $allowed = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
        if (! in_array($photo->getMimeType(), $allowed)) {
            return null;
        }

        if ($photo->getSizeByUnit('mb') > 5) {
            return null; // max 5MB sebelum compress
        }

        $uploadDir = FCPATH . 'uploads/assets/';
        if (! is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // Nama file dengan ekstensi .webp
        $baseName  = pathinfo($photo->getRandomName(), PATHINFO_FILENAME);
        $finalName = $baseName . '.webp';
        $tmpPath   = $photo->getTempName();

        // Coba compress ke WebP jika GD tersedia
        if ($this->compressToWebp($tmpPath, $photo->getMimeType(), $uploadDir . $finalName)) {
            return $finalName;
        }

        // Fallback: simpan file asli
        $originalName = $photo->getRandomName();
        $photo->move($uploadDir, $originalName);
        return $originalName;
    }

    /**
     * Compress gambar ke WebP menggunakan GD
     * Otomatis resize jika lebar > 1200px
     */
    private function compressToWebp(string $srcPath, string $mimeType, string $destPath): bool
    {
        if (! function_exists('imagewebp')) {
            return false;
        }

        $src = match ($mimeType) {
            'image/jpeg' => @imagecreatefromjpeg($srcPath),
            'image/png'  => @imagecreatefrompng($srcPath),
            'image/webp' => @imagecreatefromwebp($srcPath),
            'image/gif'  => @imagecreatefromgif($srcPath),
            default      => false,
        };

        if (! $src) {
            return false;
        }

        // Resize jika terlalu besar (max 1200px width)
        $origW = imagesx($src);
        $origH = imagesy($src);
        $maxW  = 1200;

        if ($origW > $maxW) {
            $ratio  = $maxW / $origW;
            $newW   = $maxW;
            $newH   = (int) round($origH * $ratio);
            $resized = imagecreatetruecolor($newW, $newH);

            // Pertahankan transparansi untuk PNG
            if ($mimeType === 'image/png') {
                imagealphablending($resized, false);
                imagesavealpha($resized, true);
            }

            imagecopyresampled($resized, $src, 0, 0, 0, 0, $newW, $newH, $origW, $origH);
            imagedestroy($src);
            $src = $resized;
        }

        $result = imagewebp($src, $destPath, 82); // kualitas 82%
        imagedestroy($src);

        return $result;
    }
}
