<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\CategoryModel;

class CategoryController extends BaseController
{
    protected CategoryModel $categoryModel;

    public function __construct()
    {
        $this->categoryModel = new CategoryModel();
    }

    // GET /admin/categories
    public function index()
    {
        return view('categories/index', [
            'title'      => 'Kategori Aset',
            'categories' => $this->categoryModel->withAssetCount(),
        ]);
    }

    // GET /admin/categories/new
    public function create()
    {
        return view('categories/create', ['title' => 'Tambah Kategori']);
    }

    // POST /admin/categories
    public function store()
    {
        if (! $this->validate([
            'name' => 'required|min_length[2]|max_length[100]',
            'code' => 'required|max_length[20]|is_unique[categories.code]',
        ])) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $this->categoryModel->insert([
            'name'        => strtoupper(trim($this->request->getPost('name'))),
            'code'        => strtoupper(trim($this->request->getPost('code'))),
            'description' => $this->request->getPost('description'),
        ]);

        return redirect()->to('/admin/categories')->with('success', 'Kategori berhasil ditambahkan.');
    }

    // GET /admin/categories/{id}/edit
    public function edit(int $id)
    {
        $category = $this->categoryModel->find($id);
        if (! $category) {
            return redirect()->to('/admin/categories')->with('error', 'Kategori tidak ditemukan.');
        }

        return view('categories/edit', [
            'title'    => 'Edit Kategori',
            'category' => $category,
        ]);
    }

    // POST /admin/categories/{id}/update
    public function update(int $id)
    {
        $category = $this->categoryModel->find($id);
        if (! $category) {
            return redirect()->to('/admin/categories')->with('error', 'Kategori tidak ditemukan.');
        }

        if (! $this->validate([
            'name' => 'required|min_length[2]|max_length[100]',
            'code' => "required|max_length[20]|is_unique[categories.code,id,{$id}]",
        ])) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $this->categoryModel->update($id, [
            'name'        => strtoupper(trim($this->request->getPost('name'))),
            'code'        => strtoupper(trim($this->request->getPost('code'))),
            'description' => $this->request->getPost('description'),
        ]);

        return redirect()->to('/admin/categories')->with('success', 'Kategori berhasil diperbarui.');
    }

    // POST /admin/categories/{id}/delete
    public function delete(int $id)
    {
        $category = $this->categoryModel->find($id);
        if (! $category) {
            return redirect()->to('/admin/categories')->with('error', 'Kategori tidak ditemukan.');
        }

        $this->categoryModel->delete($id);

        return redirect()->to('/admin/categories')->with('success', 'Kategori berhasil dihapus.');
    }
}
