<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class RoleFilter implements FilterInterface
{
    /**
     * Gunakan di route seperti:
     *   $routes->group('admin/users', ['filter' => 'role:admin'], ...)
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        $userRole = session()->get('role');

        // Jika argumen role diberikan, cek kecocokan
        if (! empty($arguments) && ! in_array($userRole, $arguments, true)) {
            if ($request->isAJAX()) {
                return service('response')
                    ->setStatusCode(403)
                    ->setJSON(['message' => 'Akses ditolak.']);
            }

            // Redirect ke halaman yang sesuai role agar tidak loop
            $fallback = match($userRole) {
                'admin'      => '/admin/dashboard',
                'user'       => '/admin/work-orders',
                'technician' => '/admin/work-orders',
                'it'         => '/admin/work-orders',
                'atem'       => '/admin/work-orders',
                'pembelian'  => '/admin/procurement',
                default      => '/login',
            };

            return redirect()->to($fallback)->with('error', 'Anda tidak memiliki akses ke halaman tersebut.');
        }

        // Jika tidak ada argumen, pastikan minimal sudah login dan punya role
        if (empty($userRole)) {
            return redirect()->to('/login');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // tidak diperlukan
    }
}
