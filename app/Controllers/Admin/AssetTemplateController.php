<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\AssetTemplateModel;

class AssetTemplateController extends BaseController
{
    protected AssetTemplateModel $templateModel;

    public function __construct()
    {
        $this->templateModel = new AssetTemplateModel();
    }

    // GET /admin/asset-templates
    public function index()
    {
        return view('asset_templates/index', [
            'title'     => 'Template Aset',
            'templates' => $this->templateModel->findAll(),
        ]);
    }

    // GET /admin/asset-templates/new
    public function create()
    {
        return view('asset_templates/form', [
            'title'    => 'Tambah Template Aset',
            'template' => null,
        ]);
    }

    // POST /admin/asset-templates
    public function store()
    {
        $data = [
            'name'     => trim($this->request->getPost('name') ?? ''),
            'category' => trim($this->request->getPost('category') ?? ''),
            'brand'    => trim($this->request->getPost('brand') ?? ''),
            'model'    => trim($this->request->getPost('model') ?? ''),
        ];

        if (! $this->templateModel->insert($data)) {
            return redirect()->back()->withInput()->with('errors', $this->templateModel->errors());
        }

        return redirect()->to('/admin/asset-templates')->with('success', 'Template aset berhasil ditambahkan.');
    }

    // GET /admin/asset-templates/{id}/edit
    public function edit(int $id)
    {
        $template = $this->templateModel->find($id);
        if (! $template) {
            return redirect()->to('/admin/asset-templates')->with('error', 'Template aset tidak ditemukan.');
        }

        return view('asset_templates/form', [
            'title'    => 'Edit Template Aset',
            'template' => $template,
        ]);
    }

    // POST /admin/asset-templates/{id}/update
    public function update(int $id)
    {
        $template = $this->templateModel->find($id);
        if (! $template) {
            return redirect()->to('/admin/asset-templates')->with('error', 'Template aset tidak ditemukan.');
        }

        $data = [
            'name'     => trim($this->request->getPost('name') ?? ''),
            'category' => trim($this->request->getPost('category') ?? ''),
            'brand'    => trim($this->request->getPost('brand') ?? ''),
            'model'    => trim($this->request->getPost('model') ?? ''),
        ];

        if (! $this->templateModel->update($id, $data)) {
            return redirect()->back()->withInput()->with('errors', $this->templateModel->errors());
        }

        return redirect()->to('/admin/asset-templates')->with('success', 'Template aset berhasil diperbarui.');
    }

    // POST /admin/asset-templates/{id}/delete
    public function delete(int $id)
    {
        $template = $this->templateModel->find($id);
        if (! $template) {
            return redirect()->to('/admin/asset-templates')->with('error', 'Template aset tidak ditemukan.');
        }

        $this->templateModel->delete($id);

        return redirect()->to('/admin/asset-templates')->with('success', 'Template aset berhasil dihapus.');
    }
}
