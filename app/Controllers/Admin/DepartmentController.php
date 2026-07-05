<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\DepartmentModel;

class DepartmentController extends BaseController
{
    protected DepartmentModel $model;

    public function __construct()
    {
        $this->model = new DepartmentModel();
    }

    public function index()
    {
        return view('departments/index', [
            'title'       => 'Daftar Departemen',
            'departments' => $this->model->getAll(),
        ]);
    }

    public function create()
    {
        return view('departments/form', [
            'title' => 'Tambah Departemen',
            'dept'  => null,
        ]);
    }

    public function store()
    {
        $rules = [
            'name' => 'required|min_length[2]|max_length[100]',
            'code' => 'permit_empty|max_length[20]',
        ];
        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $code = strtoupper(trim($this->request->getPost('code') ?? ''));
        if ($code && ! $this->model->isCodeUnique($code)) {
            return redirect()->back()->withInput()
                ->with('errors', ['code' => 'Kode departemen sudah digunakan.']);
        }

        $this->model->insert([
            'name'        => $this->request->getPost('name'),
            'code'        => $code ?: null,
            'manager'     => $this->request->getPost('manager'),
            'phone'       => $this->request->getPost('phone'),
            'description' => $this->request->getPost('description'),
            'is_active'   => 1,
        ]);

        return redirect()->to('/admin/departments')
            ->with('success', 'Departemen berhasil ditambahkan.');
    }

    public function edit(int $id)
    {
        $dept = $this->model->getById($id);
        if (! $dept) {
            return redirect()->to('/admin/departments')->with('error', 'Departemen tidak ditemukan.');
        }
        return view('departments/form', ['title' => 'Edit Departemen', 'dept' => $dept]);
    }

    public function update(int $id)
    {
        $dept = $this->model->getById($id);
        if (! $dept) {
            return redirect()->to('/admin/departments')->with('error', 'Departemen tidak ditemukan.');
        }

        $rules = ['name' => 'required|min_length[2]|max_length[100]'];
        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $code = strtoupper(trim($this->request->getPost('code') ?? ''));
        if ($code && ! $this->model->isCodeUnique($code, $id)) {
            return redirect()->back()->withInput()
                ->with('errors', ['code' => 'Kode departemen sudah digunakan.']);
        }

        $this->model->update($id, [
            'name'        => $this->request->getPost('name'),
            'code'        => $code ?: null,
            'manager'     => $this->request->getPost('manager'),
            'phone'       => $this->request->getPost('phone'),
            'description' => $this->request->getPost('description'),
            'is_active'   => (int) $this->request->getPost('is_active'),
        ]);

        return redirect()->to('/admin/departments')
            ->with('success', 'Departemen berhasil diperbarui.');
    }

    public function delete(int $id)
    {
        $dept = $this->model->getById($id);
        if (! $dept) {
            return redirect()->to('/admin/departments')->with('error', 'Departemen tidak ditemukan.');
        }

        if (! $this->model->delete($id)) {
            return redirect()->to('/admin/departments')
                ->with('error', 'Departemen tidak dapat dihapus karena masih memiliki aset terkait.');
        }

        return redirect()->to('/admin/departments')
            ->with('success', 'Departemen <strong>' . esc($dept['name']) . '</strong> berhasil dihapus.');
    }
}
