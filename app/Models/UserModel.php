<?php

namespace App\Models;

use CodeIgniter\Database\BaseConnection;

class UserModel
{
    protected BaseConnection $db;

    // Role yang tersedia
    public const ROLES = [
        'admin'      => 'Administrator',
        'technician' => 'Teknisi',
        'it'         => 'IT',
        'atem'       => 'ATEM',
        'user'       => 'Staff / User',
    ];

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    public function getAll(array $filters = []): array
    {
        $b = $this->db->table('users u')
            ->select('u.*, d.name AS department_name')
            ->join('departments d', 'd.id = u.department_id', 'left')
            ->where('u.deleted_at', null);

        if (!empty($filters['search'])) {
            $s = $filters['search'];
            $b->groupStart()->like('u.name', $s)->orLike('u.email', $s)
              ->orLike('u.employee_id', $s)->groupEnd();
        }
        if (!empty($filters['role']))          { $b->where('u.role', $filters['role']); }
        if (!empty($filters['department_id'])) { $b->where('u.department_id', $filters['department_id']); }
        if (isset($filters['is_active']) && $filters['is_active'] !== '') {
            $b->where('u.is_active', $filters['is_active']);
        }

        return $b->orderBy('u.name')->get()->getResultArray();
    }

    public function countFiltered(array $filters = []): int
    {
        $b = $this->db->table('users u')->where('u.deleted_at', null);
        if (!empty($filters['search'])) {
            $s = $filters['search'];
            $b->groupStart()->like('u.name', $s)->orLike('u.email', $s)->groupEnd();
        }
        if (!empty($filters['role']))          { $b->where('u.role', $filters['role']); }
        if (!empty($filters['department_id'])) { $b->where('u.department_id', $filters['department_id']); }
        return (int) $b->countAllResults();
    }

    public function getById(int $id): ?array
    {
        $row = $this->db->table('users u')
            ->select('u.*, d.name AS department_name')
            ->join('departments d', 'd.id = u.department_id', 'left')
            ->where('u.id', $id)->where('u.deleted_at', null)
            ->get()->getRowArray();
        return $row ?: null;
    }

    public function findByEmail(string $email): ?array
    {
        $row = $this->db->table('users')
            ->where('email', $email)->where('deleted_at', null)
            ->get()->getRowArray();
        return $row ?: null;
    }

    public function getActiveUsers(): array
    {
        return $this->db->table('users')
            ->where('is_active', 1)->where('deleted_at', null)
            ->orderBy('name')->get()->getResultArray();
    }

    public function getDropdown(string $role = ''): array
    {
        $b = $this->db->table('users')
            ->select('id, name')
            ->where('is_active', 1)->where('deleted_at', null);
        if ($role) { $b->where('role', $role); }
        $rows = $b->orderBy('name')->get()->getResultArray();
        $out  = [];
        foreach ($rows as $r) { $out[$r['id']] = $r['name']; }
        return $out;
    }

    public function insert(array $data): int|false
    {
        if (!empty($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT);
        }
        $data['created_at'] = $data['updated_at'] = date('Y-m-d H:i:s');
        $this->db->table('users')->insert($data);
        $id = $this->db->insertID();
        return $id > 0 ? $id : false;
    }

    public function update(int $id, array $data): bool
    {
        if (!empty($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT);
        } else {
            unset($data['password']);
        }
        $data['updated_at'] = date('Y-m-d H:i:s');
        return $this->db->table('users')->where('id', $id)->update($data);
    }

    public function delete(int $id): bool
    {
        return $this->db->table('users')->where('id', $id)
            ->update(['deleted_at' => date('Y-m-d H:i:s'), 'is_active' => 0]);
    }

    public function isEmailUnique(string $email, int $excludeId = 0): bool
    {
        $b = $this->db->table('users')
            ->where('email', $email)->where('deleted_at', null);
        if ($excludeId) { $b->where('id !=', $excludeId); }
        return $b->countAllResults() === 0;
    }
}
