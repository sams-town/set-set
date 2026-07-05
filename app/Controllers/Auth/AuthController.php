<?php

namespace App\Controllers\Auth;

use App\Controllers\BaseController;
use App\Models\UserModel;

class AuthController extends BaseController
{
    protected UserModel $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    // ---------------------------------------------------------------
    // GET /login
    // ---------------------------------------------------------------
    public function login()
    {
        if (session()->get('isLoggedIn')) {
            return redirect()->to('/admin/dashboard');
        }

        return view('auth/login', ['title' => 'Login']);
    }

    // ---------------------------------------------------------------
    // POST /login
    // ---------------------------------------------------------------
    public function loginProcess()
    {
        $rules = [
            'email'    => 'required|valid_email',
            'password' => 'required|min_length[6]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $email    = $this->request->getPost('email');
        $password = $this->request->getPost('password');

        $user = $this->userModel->findByEmail($email);

        if (! $user || ! password_verify($password, $user['password'])) {
            return redirect()->back()->withInput()->with('error', 'Email atau password salah.');
        }

        if (! $user['is_active']) {
            return redirect()->back()->withInput()->with('error', 'Akun Anda tidak aktif. Hubungi administrator.');
        }

        // Ambil nama departemen untuk session (tampil di sidebar)
        $deptName = null;
        if (!empty($user['department_id'])) {
            $deptRow = \Config\Database::connect()
                ->table('departments')
                ->select('name')
                ->where('id', $user['department_id'])
                ->get()->getRowArray();
            $deptName = $deptRow['name'] ?? null;
        }

        // Set session
        session()->set([
            'isLoggedIn'      => true,
            'user_id'         => $user['id'],
            'user_name'       => $user['name'],
            'user_email'      => $user['email'],
            'role'            => $user['role'],
            'department_id'   => $user['department_id'] ?? null,
            'department_name' => $deptName,
        ]);

        return redirect()->to('/admin/dashboard')->with('success', 'Selamat datang, ' . $user['name'] . '!');
    }

    // ---------------------------------------------------------------
    // GET /logout
    // ---------------------------------------------------------------
    public function logout()
    {
        session()->destroy();
        return redirect()->to('/login')->with('success', 'Anda telah berhasil logout.');
    }
}
