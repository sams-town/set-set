<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\ProcurementRequestModel;
use App\Models\PurchaseOrderModel;
use App\Models\DepartmentModel;
use App\Models\InventoryAssetModel;
use App\Services\WhatsAppService;

/**
 * ProcurementController
 *
 * Alur: Request → Approval Atasan → Approval Direktur
 *       → RFQ → PO → Barang Datang → Asset Register
 *
 * Routes:
 *   GET  /admin/procurement                    → index (dashboard + list)
 *   GET  /admin/procurement/new                → create request
 *   POST /admin/procurement                    → store request
 *   GET  /admin/procurement/{id}               → show detail request
 *   GET  /admin/procurement/{id}/edit          → edit request
 *   POST /admin/procurement/{id}/update        → update request
 *   POST /admin/procurement/{id}/submit        → submit untuk approval
 *   POST /admin/procurement/{id}/approve       → approve (atasan/direktur)
 *   POST /admin/procurement/{id}/reject        → reject
 *
 *   GET  /admin/procurement/{id}/po/new        → buat PO dari request
 *   POST /admin/procurement/{id}/po            → store PO
 *   GET  /admin/procurement/po/{poId}          → detail PO
 *   POST /admin/procurement/po/{poId}/receive  → catat penerimaan barang
 *   POST /admin/procurement/po/{poId}/register → register ke inventory
 */
class ProcurementController extends BaseController
{
    protected ProcurementRequestModel $model;
    protected PurchaseOrderModel      $poModel;
    protected DepartmentModel         $deptModel;
    protected InventoryAssetModel     $assetModel;
    protected WhatsAppService         $wa;

    private const PER_PAGE = 20;

    private const CATEGORIES = [
        'Komputer & Laptop', 'Printer & Scanner', 'Jaringan & Telekomunikasi',
        'Perabot Kantor', 'Proyektor & AV', 'Kendaraan Dinas',
        'Elektronik & Listrik', 'Alat Medis & Kesehatan',
        'Alat Ukur & Laboratorium', 'Lainnya',
    ];

    public function __construct()
    {
        $this->model      = new ProcurementRequestModel();
        $this->poModel    = new PurchaseOrderModel();
        $this->deptModel  = new DepartmentModel();
        $this->assetModel = new InventoryAssetModel();
        $this->wa         = new WhatsAppService();
    }

    // ================================================================
    // GET /admin/procurement — Dashboard + List
    // ================================================================
    public function index()
    {
        $filters = [
            'search'        => $this->request->getGet('search'),
            'status'        => $this->request->getGet('status'),
            'urgency'       => $this->request->getGet('urgency'),
            'department_id' => $this->request->getGet('department_id'),
        ];

        $page       = max(1, (int) $this->request->getGet('page'));
        $offset     = ($page - 1) * self::PER_PAGE;
        $total      = $this->model->countFiltered($filters);
        $requests   = $this->model->getList($filters, self::PER_PAGE, $offset);
        $totalPages = $total > 0 ? (int) ceil($total / self::PER_PAGE) : 1;

        $dashStats  = $this->model->getDashboardStats();
        $topVendors = $this->model->getTopVendors();
        $leadTime   = $this->model->getLeadTimeByVendor();
        $trend      = $this->model->getTrend(6);

        $chartTrend = json_encode([
            'labels' => array_column($trend, 'bulan'),
            'total'  => array_column($trend, 'total_request'),
            'nilai'  => array_map(fn($r) => (float) $r['total_nilai'], $trend),
        ]);

        return view('procurement/index', [
            'title'        => 'Procurement',
            'requests'     => $requests,
            'filters'      => $filters,
            'page'         => $page,
            'total_pages'  => $totalPages,
            'total_records'=> $total,
            'per_page'     => self::PER_PAGE,
            'dash_stats'   => $dashStats,
            'top_vendors'  => $topVendors,
            'lead_time'    => $leadTime,
            'chart_trend'  => $chartTrend,
            'departments'  => $this->deptModel->getDropdown(),
            'status_flow'  => ProcurementRequestModel::STATUS_FLOW,
            'urgency_opts' => ProcurementRequestModel::URGENCY,
        ]);
    }

    // ================================================================
    // GET /admin/procurement/new
    // ================================================================
    public function create()
    {
        return view('procurement/request_form', [
            'title'        => 'Permintaan Aset Baru',
            'req'          => null,
            'departments'  => $this->deptModel->getDropdown(),
            'categories'   => self::CATEGORIES,
            'urgency_opts' => ProcurementRequestModel::URGENCY,
        ]);
    }

    // ================================================================
    // POST /admin/procurement
    // ================================================================
    public function store()
    {
        if (! $this->validate([
            'title'    => 'required|min_length[5]|max_length[200]',
            'quantity' => 'required|integer|greater_than[0]',
        ])) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $photo = $this->handlePhotoUpload('photo');

        $id = $this->model->insert([
            'request_code'    => $this->model->generateCode(),
            'title'           => $this->request->getPost('title'),
            'category'        => $this->request->getPost('category'),
            'description'     => $this->request->getPost('description'),
            'quantity'        => (int) $this->request->getPost('quantity'),
            'unit'            => $this->request->getPost('unit') ?: 'unit',
            'estimated_price' => $this->request->getPost('estimated_price') ?: null,
            'urgency'         => $this->request->getPost('urgency') ?: 'normal',
            'department_id'   => $this->request->getPost('department_id') ?: null,
            'requested_by'    => session()->get('user_id'),
            'target_date'     => $this->request->getPost('target_date') ?: null,
            'photo'           => $photo,
            'status'          => 'draft',
        ]);

        return redirect()->to('/admin/procurement/' . $id)
            ->with('success', 'Permintaan berhasil dibuat. Silakan submit untuk approval.');
    }

    // ================================================================
    // GET /admin/procurement/{id}
    // ================================================================
    public function show(int $id)
    {
        $req = $this->model->getById($id);
        if (! $req) {
            return redirect()->to('/admin/procurement')->with('error', 'Permintaan tidak ditemukan.');
        }

        // Ambil PO jika ada
        $poList = $this->poModel->getList(['request_id' => $id]);

        return view('procurement/request_detail', [
            'title'       => 'Detail Permintaan — ' . $req['request_code'],
            'req'         => $req,
            'po_list'     => $poList,
            'status_flow' => ProcurementRequestModel::STATUS_FLOW,
            'urgency_opts'=> ProcurementRequestModel::URGENCY,
            'current_user_id' => (int) session()->get('user_id'),
            'current_role'    => session()->get('role'),
        ]);
    }

    // ================================================================
    // GET /admin/procurement/{id}/edit
    // ================================================================
    public function edit(int $id)
    {
        $req = $this->model->getById($id);
        if (! $req || ! in_array($req['status'], ['draft', 'rejected'])) {
            return redirect()->to('/admin/procurement/' . $id)
                ->with('error', 'Hanya permintaan berstatus Draft atau Ditolak yang dapat diedit.');
        }

        return view('procurement/request_form', [
            'title'        => 'Edit Permintaan',
            'req'          => $req,
            'departments'  => $this->deptModel->getDropdown(),
            'categories'   => self::CATEGORIES,
            'urgency_opts' => ProcurementRequestModel::URGENCY,
        ]);
    }

    // ================================================================
    // POST /admin/procurement/{id}/update
    // ================================================================
    public function update(int $id)
    {
        $req = $this->model->getById($id);
        if (! $req) {
            return redirect()->to('/admin/procurement')->with('error', 'Tidak ditemukan.');
        }

        if (! $this->validate(['title' => 'required|min_length[5]', 'quantity' => 'required|integer|greater_than[0]'])) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $photo = $this->handlePhotoUpload('photo') ?? $req['photo'];

        $this->model->update($id, [
            'title'           => $this->request->getPost('title'),
            'category'        => $this->request->getPost('category'),
            'description'     => $this->request->getPost('description'),
            'quantity'        => (int) $this->request->getPost('quantity'),
            'unit'            => $this->request->getPost('unit') ?: 'unit',
            'estimated_price' => $this->request->getPost('estimated_price') ?: null,
            'urgency'         => $this->request->getPost('urgency') ?: 'normal',
            'department_id'   => $this->request->getPost('department_id') ?: null,
            'target_date'     => $this->request->getPost('target_date') ?: null,
            'photo'           => $photo,
            'status'          => 'draft',
        ]);

        return redirect()->to('/admin/procurement/' . $id)->with('success', 'Permintaan berhasil diperbarui.');
    }

    // ================================================================
    // POST /admin/procurement/{id}/submit — Submit ke Approval Atasan
    // ================================================================
    public function submit(int $id)
    {
        $req = $this->model->getById($id);
        if (! $req || ! in_array($req['status'], ['draft', 'rejected'])) {
            return redirect()->to('/admin/procurement/' . $id)
                ->with('error', 'Permintaan tidak dapat disubmit.');
        }

        $this->model->update($id, ['status' => 'pending_atasan']);

        // Notifikasi WA ke admin
        try {
            $this->wa->sendToAdmins(implode("\n", [
                "━━━━━━━━━━━━━━━━━━━━",
                "🛒 *PERMINTAAN ASET BARU*",
                "━━━━━━━━━━━━━━━━━━━━",
                "📌 Kode    : *{$req['request_code']}*",
                "📦 Item    : {$req['title']}",
                "🔢 Qty     : {$req['quantity']} {$req['unit']}",
                "👤 Pemohon : {$req['requested_by_name']}",
                "🏢 Dept    : " . ($req['department_name'] ?? '-'),
                "━━━━━━━━━━━━━━━━━━━━",
                "🕐 " . date('d/m/Y H:i') . " WIB",
                "_RS.Taman Harapan Baru — Procurement_",
            ]));
        } catch (\Throwable $e) { log_message('error', '[Procurement WA] ' . $e->getMessage()); }

        return redirect()->to('/admin/procurement/' . $id)
            ->with('success', 'Permintaan berhasil disubmit untuk approval atasan.');
    }

    // ================================================================
    // POST /admin/procurement/{id}/approve — Approve bertahap
    // ================================================================
    public function approve(int $id)
    {
        $req  = $this->model->getById($id);
        $role = session()->get('role');
        $uid  = (int) session()->get('user_id');
        $note = $this->request->getPost('note');

        if (! $req) {
            return redirect()->to('/admin/procurement')->with('error', 'Tidak ditemukan.');
        }

        if ($req['status'] === 'pending_atasan') {
            $this->model->update($id, [
                'status'          => 'pending_direktur',
                'approved_atasan' => $uid,
                'atasan_note'     => $note,
            ]);
            $msg = 'Disetujui oleh atasan. Menunggu approval Direktur.';

        } elseif ($req['status'] === 'pending_direktur') {
            $this->model->update($id, [
                'status'             => 'approved',
                'approved_direktur'  => $uid,
                'direktur_note'      => $note,
                'approved_at'        => date('Y-m-d H:i:s'),
            ]);
            $msg = 'Disetujui oleh Direktur. Permintaan dapat dilanjutkan ke RFQ.';

        } else {
            return redirect()->to('/admin/procurement/' . $id)->with('error', 'Status tidak valid untuk approval.');
        }

        return redirect()->to('/admin/procurement/' . $id)->with('success', $msg);
    }

    // ================================================================
    // POST /admin/procurement/{id}/reject
    // ================================================================
    public function reject(int $id)
    {
        $req = $this->model->getById($id);
        if (! $req) {
            return redirect()->to('/admin/procurement')->with('error', 'Tidak ditemukan.');
        }

        $reason = trim($this->request->getPost('rejection_reason') ?? '');
        if (empty($reason)) {
            return redirect()->back()->with('error', 'Alasan penolakan wajib diisi.');
        }

        $this->model->update($id, [
            'status'           => 'rejected',
            'rejection_reason' => $reason,
        ]);

        return redirect()->to('/admin/procurement/' . $id)
            ->with('success', 'Permintaan ditolak.');
    }

    // ================================================================
    // POST /admin/procurement/{id}/set-rfq — Tandai sedang RFQ
    // ================================================================
    public function setRfq(int $id)
    {
        $req = $this->model->getById($id);
        if (! $req || $req['status'] !== 'approved') {
            return redirect()->to('/admin/procurement/' . $id)->with('error', 'Status tidak valid.');
        }
        $this->model->update($id, ['status' => 'rfq']);
        return redirect()->to('/admin/procurement/' . $id)->with('success', 'Status diperbarui ke RFQ.');
    }

    // ================================================================
    // GET /admin/procurement/{id}/po/new — Form buat PO
    // ================================================================
    public function createPo(int $id)
    {
        $req = $this->model->getById($id);
        if (! $req || ! in_array($req['status'], ['approved', 'rfq'])) {
            return redirect()->to('/admin/procurement/' . $id)
                ->with('error', 'Permintaan belum disetujui.');
        }

        $vendors = $this->deptModel->getDropdown(); // reuse — ambil vendor dari model
        $db      = \Config\Database::connect();
        $vendorRows = $db->table('vendors')
            ->select('id, name')->where('is_active', 1)->where('deleted_at', null)
            ->orderBy('name')->get()->getResultArray();
        $vendorOpts = [];
        foreach ($vendorRows as $v) { $vendorOpts[$v['id']] = $v['name']; }

        return view('procurement/po_form', [
            'title'      => 'Buat Purchase Order',
            'req'        => $req,
            'po'         => null,
            'vendors'    => $vendorOpts,
            'categories' => self::CATEGORIES,
        ]);
    }

    // ================================================================
    // POST /admin/procurement/{id}/po — Store PO
    // ================================================================
    public function storePo(int $id)
    {
        $req = $this->model->getById($id);
        if (! $req) {
            return redirect()->to('/admin/procurement')->with('error', 'Tidak ditemukan.');
        }

        if (! $this->validate(['vendor_id' => 'required|integer'])) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        // Ambil items dari POST (array)
        $descriptions = $this->request->getPost('item_desc')    ?? [];
        $quantities   = $this->request->getPost('item_qty')     ?? [];
        $units        = $this->request->getPost('item_unit')    ?? [];
        $prices       = $this->request->getPost('item_price')   ?? [];
        $categories   = $this->request->getPost('item_category') ?? [];

        $items = [];
        foreach ($descriptions as $i => $desc) {
            if (empty(trim($desc))) { continue; }
            $qty   = max(1, (int) ($quantities[$i] ?? 1));
            $price = (float) ($prices[$i] ?? 0);
            $items[] = [
                'description' => $desc,
                'category'    => $categories[$i] ?? null,
                'quantity'    => $qty,
                'unit'        => $units[$i] ?? 'unit',
                'unit_price'  => $price,
            ];
        }

        if (empty($items)) {
            return redirect()->back()->withInput()
                ->with('error', 'Minimal 1 item harus diisi.');
        }

        // Hitung lead time
        $poDate       = $this->request->getPost('po_date') ?: date('Y-m-d');
        $expectedDate = $this->request->getPost('expected_date');
        $leadTime     = null;
        if ($expectedDate) {
            $leadTime = (int) ((strtotime($expectedDate) - strtotime($poDate)) / 86400);
        }

        $poId = $this->poModel->insert([
            'po_code'       => $this->poModel->generateCode(),
            'request_id'    => $id,
            'vendor_id'     => $this->request->getPost('vendor_id'),
            'status'        => 'sent',
            'po_date'       => $poDate,
            'expected_date' => $expectedDate ?: null,
            'tax'           => (float) ($this->request->getPost('tax') ?: 0),
            'shipping'      => (float) ($this->request->getPost('shipping') ?: 0),
            'notes'         => $this->request->getPost('notes'),
            'terms'         => $this->request->getPost('terms'),
            'created_by'    => session()->get('user_id'),
            'lead_time_days'=> $leadTime,
        ], $items);

        // Update request ke status po
        $this->model->update($id, ['status' => 'po']);

        return redirect()->to('/admin/procurement/po/' . $poId)
            ->with('success', 'Purchase Order berhasil dibuat.');
    }

    // ================================================================
    // GET /admin/procurement/po/{poId} — Detail PO
    // ================================================================
    public function showPo(int $poId)
    {
        $po = $this->poModel->getById($poId);
        if (! $po) {
            return redirect()->to('/admin/procurement')->with('error', 'PO tidak ditemukan.');
        }

        $items = $this->poModel->getItems($poId);
        $req   = $po['request_id'] ? $this->model->getById((int) $po['request_id']) : null;

        return view('procurement/po_detail', [
            'title'  => 'Detail PO — ' . $po['po_code'],
            'po'     => $po,
            'items'  => $items,
            'req'    => $req,
            'status' => PurchaseOrderModel::STATUS,
        ]);
    }

    // ================================================================
    // POST /admin/procurement/po/{poId}/receive — Terima barang
    // ================================================================
    public function receivePo(int $poId)
    {
        $po = $this->poModel->getById($poId);
        if (! $po) {
            return redirect()->to('/admin/procurement')->with('error', 'PO tidak ditemukan.');
        }

        $receivedQtys = $this->request->getPost('received_qty') ?? [];
        $receivedDate = $this->request->getPost('received_date') ?: date('Y-m-d');

        $this->poModel->receiveItems($poId, $receivedQtys, $receivedDate);

        // Update request ke received
        if ($po['request_id']) {
            $req = $this->model->getById((int) $po['request_id']);
            if ($req && $req['status'] === 'po') {
                $this->model->update((int) $po['request_id'], ['status' => 'received']);
            }
        }

        return redirect()->to('/admin/procurement/po/' . $poId)
            ->with('success', 'Penerimaan barang berhasil dicatat.');
    }

    // ================================================================
    // POST /admin/procurement/po/{poId}/register — Register ke Inventory
    // ================================================================
    public function registerAsset(int $poId)
    {
        $po    = $this->poModel->getById($poId);
        $items = $this->poModel->getItems($poId);

        if (! $po || $po['status'] !== 'completed') {
            return redirect()->to('/admin/procurement/po/' . $poId)
                ->with('error', 'PO belum selesai diterima.');
        }

        $registered = 0;
        foreach ($items as $item) {
            if ($item['asset_id']) { continue; } // sudah diregister

            // Auto-insert ke inventory
            $assetCode = $this->assetModel->generateCode($item['category'] ?? 'Lainnya');
            $assetId   = $this->assetModel->insert([
                'asset_code'     => $assetCode,
                'name'           => $item['description'],
                'category'       => $item['category'] ?? 'Lainnya',
                'quantity'       => $item['qty_received'] ?: $item['quantity'],
                'unit'           => $item['unit'],
                'purchase_price' => $item['unit_price'],
                'purchase_date'  => $po['received_date'] ?: date('Y-m-d'),
                'vendor_id'      => $po['vendor_id'],
                'condition'      => 'baik',
                'status'         => 'tersedia',
                'status_condition'=> 'baru',
                'created_by'     => session()->get('user_id'),
            ]);

            if ($assetId) {
                // Tandai item sudah diregister
                \Config\Database::connect()->table('po_items')
                    ->where('id', $item['id'])->update(['asset_id' => $assetId]);
                $registered++;
            }
        }

        // Update request ke registered
        if ($po['request_id']) {
            $this->model->update((int) $po['request_id'], ['status' => 'registered']);
        }

        return redirect()->to('/admin/procurement/po/' . $poId)
            ->with('success', "{$registered} aset berhasil diregistrasi ke Inventory.");
    }

    // ================================================================
    // PRIVATE — Photo Upload
    // ================================================================
    private function handlePhotoUpload(string $field): ?string
    {
        $file = $this->request->getFile($field);
        if (! $file || ! $file->isValid() || $file->hasMoved()) { return null; }
        if (! in_array($file->getMimeType(), ['image/jpeg','image/png','image/webp'])) { return null; }
        if ($file->getSizeByUnit('mb') > 5) { return null; }

        $dir = FCPATH . 'uploads/procurement/';
        if (! is_dir($dir)) { mkdir($dir, 0755, true); }

        $name = pathinfo($file->getRandomName(), PATHINFO_FILENAME) . '.webp';
        $tmp  = $file->getTempName();

        if (function_exists('imagewebp')) {
            $src = match ($file->getMimeType()) {
                'image/jpeg' => @imagecreatefromjpeg($tmp),
                'image/png'  => @imagecreatefrompng($tmp),
                'image/webp' => @imagecreatefromwebp($tmp),
                default      => false,
            };
            if ($src) {
                $w = imagesx($src); $h = imagesy($src);
                if ($w > 1200) {
                    $nh = (int)round($h * 1200 / $w);
                    $r  = imagecreatetruecolor(1200, $nh);
                    imagecopyresampled($r, $src, 0, 0, 0, 0, 1200, $nh, $w, $h);
                    imagedestroy($src); $src = $r;
                }
                imagewebp($src, $dir . $name, 82);
                imagedestroy($src);
                return $name;
            }
        }

        $orig = $file->getRandomName();
        $file->move($dir, $orig);
        return $orig;
    }
}
