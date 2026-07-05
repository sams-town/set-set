<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'name'       => 'Administrator',
                'email'      => 'admin@siaset.com',
                'password'   => password_hash('admin123', PASSWORD_BCRYPT),
                'role'       => 'admin',
                'is_active'  => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name'       => 'User Biasa',
                'email'      => 'user@siaset.com',
                'password'   => password_hash('user123', PASSWORD_BCRYPT),
                'role'       => 'user',
                'is_active'  => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
        ];

        // Gunakan insertBatch agar efisien
        $this->db->table('users')->insertBatch($users);

        echo "UserSeeder: 2 user berhasil dibuat.\n";
        echo "  - admin@siaset.com  / admin123 (role: admin)\n";
        echo "  - user@siaset.com   / user123  (role: user)\n";
    }
}
