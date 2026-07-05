<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\LocationModel;
use App\Models\DepartmentModel;
use App\Models\RoomTypeModel;

class LocationController extends BaseController
{
    protected LocationModel   $model;
    protected DepartmentModel $deptModel;
    protected RoomTypeModel   $rtModel;

    public function __construct()
    {
        $this->model     = new LocationModel();
        $this->deptModel = new DepartmentModel();
        $this->rtModel   = new RoomTypeModel();
    }

    public function index()
    {
        $filters = [
            'search'        => $this->request->getGet('search'),
            'department_id' => $this->request->getGet('department_id'),
            'room_type_id'  => $this->request->getGet('room_type_id'),
        ];

        return view('locations/index', [
            'title'       => 'Manajemen Lokasi',
            'locations'   => $this->model->getAll($filters),
            'filters'     => $filters,
            'departments' => $this->deptModel->getDropdown(),
            'room_types'  => $this->rtModel->getDropdown(),
        ]);
    }

    public function create()
    {
        return view('locations/form', [
            'title'       => 'Tambah Lokasi',
            'loc'         => null,
            'departments' => $this->deptModel->getDropdown(),
            'room_types'  => $this->rtModel->getDropdown(),
        ]);
    }

    public function store()
    {
        if (! $this->validate(['name' => 'required|min_length[2]|max_length[150]'])) {
            return redirect()->back()->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $photo = $this->handlePhotoUpload();

        $this->model->insert([
            'name'         => $this->request->getPost('name'),
            'building'     => $this->request->getPost('building'),
            'floor'        => $this->request->getPost('floor'),
            'department_id'=> $this->request->getPost('department_id') ?: null,
            'room_type_id' => $this->request->getPost('room_type_id')  ?: null,
            'capacity'     => $this->request->getPost('capacity')      ?: null,
            'notes'        => $this->request->getPost('notes'),
            'photo'        => $photo,
        ]);

        return redirect()->to('/admin/locations')
            ->with('success', 'Lokasi berhasil ditambahkan.');
    }

    public function edit(int $id)
    {
        $loc = $this->model->getById($id);
        if (! $loc) {
            return redirect()->to('/admin/locations')->with('error', 'Lokasi tidak ditemukan.');
        }

        return view('locations/form', [
            'title'       => 'Edit Lokasi',
            'loc'         => $loc,
            'departments' => $this->deptModel->getDropdown(),
            'room_types'  => $this->rtModel->getDropdown(),
        ]);
    }

    public function update(int $id)
    {
        $loc = $this->model->getById($id);
        if (! $loc) {
            return redirect()->to('/admin/locations')->with('error', 'Lokasi tidak ditemukan.');
        }

        if (! $this->validate(['name' => 'required|min_length[2]|max_length[150]'])) {
            return redirect()->back()->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $photo = $this->handlePhotoUpload();
        if (! $photo) {
            $photo = $loc['photo']; // pertahankan foto lama
        }

        $this->model->update($id, [
            'name'         => $this->request->getPost('name'),
            'building'     => $this->request->getPost('building'),
            'floor'        => $this->request->getPost('floor'),
            'department_id'=> $this->request->getPost('department_id') ?: null,
            'room_type_id' => $this->request->getPost('room_type_id')  ?: null,
            'capacity'     => $this->request->getPost('capacity')      ?: null,
            'notes'        => $this->request->getPost('notes'),
            'photo'        => $photo,
        ]);

        return redirect()->to('/admin/locations')
            ->with('success', 'Lokasi berhasil diperbarui.');
    }

    public function delete(int $id)
    {
        $loc = $this->model->getById($id);
        if (! $loc) {
            return redirect()->to('/admin/locations')->with('error', 'Lokasi tidak ditemukan.');
        }

        if (! $this->model->delete($id)) {
            return redirect()->to('/admin/locations')
                ->with('error', 'Lokasi tidak dapat dihapus karena masih memiliki aset.');
        }

        // Hapus foto jika ada
        if ($loc['photo'] && file_exists(FCPATH . 'uploads/locations/' . $loc['photo'])) {
            unlink(FCPATH . 'uploads/locations/' . $loc['photo']);
        }

        return redirect()->to('/admin/locations')
            ->with('success', 'Lokasi <strong>' . esc($loc['name']) . '</strong> berhasil dihapus.');
    }

    // ── Upload foto lokasi (compress ke WebP) ────────────────────
    private function handlePhotoUpload(): ?string
    {
        $photo = $this->request->getFile('photo');
        if (! $photo || ! $photo->isValid() || $photo->hasMoved()) { return null; }
        if (! in_array($photo->getMimeType(), ['image/jpeg','image/png','image/webp','image/gif'])) { return null; }
        if ($photo->getSizeByUnit('mb') > 5) { return null; }

        $dir = FCPATH . 'uploads/locations/';
        if (! is_dir($dir)) { mkdir($dir, 0755, true); }

        $name = pathinfo($photo->getRandomName(), PATHINFO_FILENAME) . '.webp';
        $tmp  = $photo->getTempName();

        if (function_exists('imagewebp')) {
            $src = match ($photo->getMimeType()) {
                'image/jpeg' => @imagecreatefromjpeg($tmp),
                'image/png'  => @imagecreatefrompng($tmp),
                'image/webp' => @imagecreatefromwebp($tmp),
                default      => false,
            };
            if ($src) {
                $w = imagesx($src); $h = imagesy($src);
                if ($w > 1200) {
                    $nh = (int) round($h * 1200 / $w);
                    $r  = imagecreatetruecolor(1200, $nh);
                    imagecopyresampled($r, $src, 0, 0, 0, 0, 1200, $nh, $w, $h);
                    imagedestroy($src); $src = $r;
                }
                imagewebp($src, $dir . $name, 82);
                imagedestroy($src);
                return $name;
            }
        }

        $orig = $photo->getRandomName();
        $photo->move($dir, $orig);
        return $orig;
    }
}
