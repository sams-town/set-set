<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\UserModel;
use App\Models\DepartmentModel;

class UserController extends BaseController
{
    protected UserModel       $model;
    protected DepartmentModel $deptModel;

    public function __construct()
    {
        $this->model     = new UserModel();
        $this->deptModel = new DepartmentModel();
    }

    // ================================================================
    // GET /admin/users
    // ================================================================
    public function index()
    {
        $filters = [
            'search'        => $this->request->getGet('search'),
            'role'          => $this->request->getGet('role'),
            'department_id' => $this->request->getGet('department_id'),
            'is_active'     => $this->request->getGet('is_active') ?? '',
        ];

        $users = $this->model->getAll($filters);

        // Stats ringkasan per role
        $stats = [];
        foreach (UserModel::ROLES as $key => $label) {
            $stats[$key] = count(array_filter($users, fn($u) => $u['role'] === $key));
        }

        return view('users/index', [
            'title'       => 'Manajemen Staff',
            'users'       => $users,
            'filters'     => $filters,
            'stats'       => $stats,
            'roles'       => UserModel::ROLES,
            'departments' => $this->deptModel->getDropdown(),
        ]);
    }

    // ================================================================
    // GET /admin/users/new
    // ================================================================
    public function create()
    {
        return view('users/form', [
            'title'       => 'Tambah Staff',
            'user'        => null,
            'roles'       => UserModel::ROLES,
            'departments' => $this->deptModel->getDropdown(),
        ]);
    }

    // ================================================================
    // POST /admin/users
    // ================================================================
    public function store()
    {
        $rules = [
            'name'     => 'required|min_length[2]|max_length[100]',
            'email'    => 'required|valid_email',
            'password' => 'required|min_length[6]',
            'role'     => 'required|in_list[admin,technician,user]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $email = strtolower(trim($this->request->getPost('email')));
        if (! $this->model->isEmailUnique($email)) {
            return redirect()->back()->withInput()
                ->with('errors', ['email' => 'Email sudah terdaftar.']);
        }

        $avatar = $this->handleAvatarUpload();

        $this->model->insert([
            'name'          => $this->request->getPost('name'),
            'email'         => $email,
            'password'      => $this->request->getPost('password'),
            'role'          => $this->request->getPost('role'),
            'phone'         => $this->request->getPost('phone'),
            'department_id' => $this->request->getPost('department_id') ?: null,
            'employee_id'   => $this->request->getPost('employee_id'),
            'position'      => $this->request->getPost('position'),
            'notes'         => $this->request->getPost('notes'),
            'avatar'        => $avatar,
            'is_active'     => 1,
        ]);

        return redirect()->to('/admin/users')
            ->with('success', 'Staff <strong>' . esc($this->request->getPost('name')) . '</strong> berhasil ditambahkan.');
    }

    // ================================================================
    // GET /admin/users/{id}/edit
    // ================================================================
    public function edit(int $id)
    {
        $user = $this->model->getById($id);
        if (! $user) {
            return redirect()->to('/admin/users')->with('error', 'Staff tidak ditemukan.');
        }

        return view('users/form', [
            'title'       => 'Edit Staff',
            'user'        => $user,
            'roles'       => UserModel::ROLES,
            'departments' => $this->deptModel->getDropdown(),
        ]);
    }

    // ================================================================
    // POST /admin/users/{id}/update
    // ================================================================
    public function update(int $id)
    {
        $user = $this->model->getById($id);
        if (! $user) {
            return redirect()->to('/admin/users')->with('error', 'Staff tidak ditemukan.');
        }

        $rules = [
            'name'  => 'required|min_length[2]|max_length[100]',
            'email' => 'required|valid_email',
            'role'  => 'required|in_list[admin,technician,user]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $email = strtolower(trim($this->request->getPost('email')));
        if (! $this->model->isEmailUnique($email, $id)) {
            return redirect()->back()->withInput()
                ->with('errors', ['email' => 'Email sudah digunakan akun lain.']);
        }

        $avatar   = $this->handleAvatarUpload();
        $newAvatar = $avatar ?? $user['avatar'];

        // Hapus avatar lama jika diganti
        if ($avatar && $user['avatar'] && file_exists(FCPATH . 'uploads/avatars/' . $user['avatar'])) {
            unlink(FCPATH . 'uploads/avatars/' . $user['avatar']);
        }

        $data = [
            'name'          => $this->request->getPost('name'),
            'email'         => $email,
            'role'          => $this->request->getPost('role'),
            'phone'         => $this->request->getPost('phone'),
            'department_id' => $this->request->getPost('department_id') ?: null,
            'employee_id'   => $this->request->getPost('employee_id'),
            'position'      => $this->request->getPost('position'),
            'notes'         => $this->request->getPost('notes'),
            'avatar'        => $newAvatar,
            'is_active'     => (int) $this->request->getPost('is_active'),
        ];

        // Update password hanya jika diisi
        $pw = $this->request->getPost('password');
        if (! empty($pw)) {
            if (strlen($pw) < 6) {
                return redirect()->back()->withInput()
                    ->with('errors', ['password' => 'Password minimal 6 karakter.']);
            }
            $data['password'] = $pw;
        }

        $this->model->update($id, $data);

        return redirect()->to('/admin/users')
            ->with('success', 'Data staff berhasil diperbarui.');
    }

    // ================================================================
    // POST /admin/users/{id}/delete
    // ================================================================
    public function delete(int $id)
    {
        // Tidak boleh hapus diri sendiri
        if ($id === (int) session()->get('user_id')) {
            return redirect()->to('/admin/users')
                ->with('error', 'Tidak dapat menghapus akun yang sedang login.');
        }

        $user = $this->model->getById($id);
        if (! $user) {
            return redirect()->to('/admin/users')->with('error', 'Staff tidak ditemukan.');
        }

        $this->model->delete($id);

        return redirect()->to('/admin/users')
            ->with('success', 'Staff <strong>' . esc($user['name']) . '</strong> berhasil dihapus.');
    }

    // ================================================================
    // PRIVATE — Avatar Upload (compress ke WebP, max 400px)
    // ================================================================
    private function handleAvatarUpload(): ?string
    {
        $file = $this->request->getFile('avatar');
        if (! $file || ! $file->isValid() || $file->hasMoved()) { return null; }
        if (! in_array($file->getMimeType(), ['image/jpeg','image/png','image/webp','image/gif'])) { return null; }
        if ($file->getSizeByUnit('mb') > 2) { return null; }

        $dir = FCPATH . 'uploads/avatars/';
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
                $max = 400;
                if ($w > $max || $h > $max) {
                    $ratio = min($max / $w, $max / $h);
                    $nw = (int) round($w * $ratio); $nh = (int) round($h * $ratio);
                    $r = imagecreatetruecolor($nw, $nh);
                    imagecopyresampled($r, $src, 0, 0, 0, 0, $nw, $nh, $w, $h);
                    imagedestroy($src); $src = $r;
                }
                imagewebp($src, $dir . $name, 85);
                imagedestroy($src);
                return $name;
            }
        }

        $orig = $file->getRandomName();
        $file->move($dir, $orig);
        return $orig;
    }
}
