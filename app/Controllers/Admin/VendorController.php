<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\VendorModel;

class VendorController extends BaseController
{
    protected VendorModel $model;

    public function __construct()
    {
        $this->model = new VendorModel();
    }

    // GET /admin/vendors
    public function index()
    {
        $filters = [
            'search'    => $this->request->getGet('search'),
            'category'  => $this->request->getGet('category'),
            'is_active' => $this->request->getGet('is_active') ?? '',
        ];

        $vendors = $this->model->getAll($filters);

        // Stats
        $total    = count($vendors);
        $active   = count(array_filter($vendors, fn($v) => $v['is_active'] == 1));
        $supplier = count(array_filter($vendors, fn($v) => in_array($v['category'], ['supplier','both'])));
        $service  = count(array_filter($vendors, fn($v) => in_array($v['category'], ['service','both'])));

        return view('vendors/index', [
            'title'      => 'Master Data Vendor',
            'vendors'    => $vendors,
            'filters'    => $filters,
            'categories' => VendorModel::CATEGORIES,
            'stats'      => compact('total','active','supplier','service'),
        ]);
    }

    // GET /admin/vendors/new
    public function create()
    {
        return view('vendors/form', [
            'title'      => 'Tambah Vendor',
            'vendor'     => null,
            'categories' => VendorModel::CATEGORIES,
        ]);
    }

    // POST /admin/vendors
    public function store()
    {
        $rules = [
            'name'     => 'required|min_length[2]|max_length[150]',
            'category' => 'required|max_length[100]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $code = strtoupper(trim($this->request->getPost('code') ?? ''));
        if ($code && ! $this->model->isCodeUnique($code)) {
            return redirect()->back()->withInput()
                ->with('errors', ['code' => 'Kode vendor sudah digunakan.']);
        }

        $this->model->insert([
            'name'      => $this->request->getPost('name'),
            'code'      => $code ?: null,
            'contact'   => $this->request->getPost('contact') ?: null,
            'phone'     => $this->request->getPost('phone') ?: null,
            'email'     => $this->request->getPost('email') ?: null,
            'address'   => $this->request->getPost('address') ?: null,
            'category'  => $this->request->getPost('category'),
            'notes'     => $this->request->getPost('notes') ?: null,
            'is_active' => 1,
        ]);

        return redirect()->to('/admin/vendors')
            ->with('success', 'Vendor <strong>' . esc($this->request->getPost('name')) . '</strong> berhasil ditambahkan.');
    }

    // GET /admin/vendors/{id}/edit
    public function edit(int $id)
    {
        $vendor = $this->model->getById($id);
        if (! $vendor) {
            return redirect()->to('/admin/vendors')->with('error', 'Vendor tidak ditemukan.');
        }

        return view('vendors/form', [
            'title'      => 'Edit Vendor',
            'vendor'     => $vendor,
            'categories' => VendorModel::CATEGORIES,
        ]);
    }

    // POST /admin/vendors/{id}/update
    public function update(int $id)
    {
        $vendor = $this->model->getById($id);
        if (! $vendor) {
            return redirect()->to('/admin/vendors')->with('error', 'Vendor tidak ditemukan.');
        }

        $rules = [
            'name'     => 'required|min_length[2]|max_length[150]',
            'category' => 'required|max_length[100]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $code = strtoupper(trim($this->request->getPost('code') ?? ''));
        if ($code && ! $this->model->isCodeUnique($code, $id)) {
            return redirect()->back()->withInput()
                ->with('errors', ['code' => 'Kode vendor sudah digunakan.']);
        }

        $this->model->update($id, [
            'name'      => $this->request->getPost('name'),
            'code'      => $code ?: null,
            'contact'   => $this->request->getPost('contact') ?: null,
            'phone'     => $this->request->getPost('phone') ?: null,
            'email'     => $this->request->getPost('email') ?: null,
            'address'   => $this->request->getPost('address') ?: null,
            'category'  => $this->request->getPost('category'),
            'notes'     => $this->request->getPost('notes') ?: null,
            'is_active' => (int) $this->request->getPost('is_active'),
        ]);

        return redirect()->to('/admin/vendors')
            ->with('success', 'Vendor berhasil diperbarui.');
    }

    // POST /admin/vendors/{id}/delete
    public function delete(int $id)
    {
        $vendor = $this->model->getById($id);
        if (! $vendor) {
            return redirect()->to('/admin/vendors')->with('error', 'Vendor tidak ditemukan.');
        }

        // Cek apakah vendor masih dipakai di aset
        $db = \Config\Database::connect();
        $usedCount = $db->table('assets')
            ->where('vendor_id', $id)
            ->where('deleted_at', null)
            ->countAllResults();

        if ($usedCount > 0) {
            return redirect()->to('/admin/vendors')
                ->with('error', 'Vendor tidak dapat dihapus karena masih digunakan oleh <strong>' . $usedCount . '</strong> aset.');
        }

        $this->model->delete($id);

        return redirect()->to('/admin/vendors')
            ->with('success', 'Vendor <strong>' . esc($vendor['name']) . '</strong> berhasil dihapus.');
    }
}
