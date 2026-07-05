<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\RoomTypeModel;

class RoomTypeController extends BaseController
{
    protected RoomTypeModel $model;

    public function __construct()
    {
        $this->model = new RoomTypeModel();
    }

    public function index()
    {
        return view('room_types/index', [
            'title'      => 'Tipe Ruangan',
            'room_types' => $this->model->getAll(),
        ]);
    }

    public function create()
    {
        return view('room_types/form', ['title' => 'Tambah Tipe Ruangan', 'rt' => null]);
    }

    public function store()
    {
        if (! $this->validate(['name' => 'required|min_length[2]|max_length[100]'])) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }
        $this->model->insert([
            'name'        => $this->request->getPost('name'),
            'code'        => strtoupper(trim($this->request->getPost('code') ?? '')) ?: null,
            'description' => $this->request->getPost('description'),
            'is_active'   => 1,
        ]);
        return redirect()->to('/admin/room-types')->with('success', 'Tipe ruangan berhasil ditambahkan.');
    }

    public function edit(int $id)
    {
        $rt = $this->model->getById($id);
        if (! $rt) { return redirect()->to('/admin/room-types')->with('error', 'Data tidak ditemukan.'); }
        return view('room_types/form', ['title' => 'Edit Tipe Ruangan', 'rt' => $rt]);
    }

    public function update(int $id)
    {
        if (! $this->validate(['name' => 'required|min_length[2]|max_length[100]'])) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }
        $this->model->update($id, [
            'name'        => $this->request->getPost('name'),
            'code'        => strtoupper(trim($this->request->getPost('code') ?? '')) ?: null,
            'description' => $this->request->getPost('description'),
            'is_active'   => (int) $this->request->getPost('is_active'),
        ]);
        return redirect()->to('/admin/room-types')->with('success', 'Tipe ruangan berhasil diperbarui.');
    }

    public function delete(int $id)
    {
        $rt = $this->model->getById($id);
        if (! $rt) { return redirect()->to('/admin/room-types')->with('error', 'Data tidak ditemukan.'); }
        if (! $this->model->delete($id)) {
            return redirect()->to('/admin/room-types')
                ->with('error', 'Tipe ruangan tidak dapat dihapus karena masih digunakan oleh lokasi.');
        }
        return redirect()->to('/admin/room-types')
            ->with('success', 'Tipe ruangan <strong>' . esc($rt['name']) . '</strong> dihapus.');
    }
}
