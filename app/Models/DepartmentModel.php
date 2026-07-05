<?php

namespace App\Models;

use CodeIgniter\Database\BaseConnection;

class DepartmentModel
{
    protected BaseConnection $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    public function getAll(bool $activeOnly = false): array
    {
        $b = $this->db->table('departments')
            ->select('departments.*, COUNT(a.id) AS asset_count, COUNT(u.id) AS user_count')
            ->join('assets a', 'a.department_id = departments.id AND a.deleted_at IS NULL', 'left')
            ->join('users u',  'u.department_id = departments.id AND u.deleted_at IS NULL', 'left')
            ->where('departments.deleted_at', null)
            ->groupBy('departments.id');

        if ($activeOnly) { $b->where('departments.is_active', 1); }

        return $b->orderBy('departments.name')->get()->getResultArray();
    }

    public function getById(int $id): ?array
    {
        $row = $this->db->table('departments')
            ->where('id', $id)->where('deleted_at', null)
            ->get()->getRowArray();
        return $row ?: null;
    }

    public function getDropdown(): array
    {
        $rows = $this->db->table('departments')
            ->select('id, name')
            ->where('deleted_at', null)->where('is_active', 1)
            ->orderBy('name')->get()->getResultArray();
        $out = [];
        foreach ($rows as $r) { $out[$r['id']] = $r['name']; }
        return $out;
    }

    public function isCodeUnique(string $code, int $excludeId = 0): bool
    {
        $b = $this->db->table('departments')
            ->where('code', $code)->where('deleted_at', null);
        if ($excludeId) { $b->where('id !=', $excludeId); }
        return $b->countAllResults() === 0;
    }

    public function insert(array $data): int|false
    {
        $data['created_at'] = $data['updated_at'] = date('Y-m-d H:i:s');
        $this->db->table('departments')->insert($data);
        $id = $this->db->insertID();
        return $id > 0 ? $id : false;
    }

    public function update(int $id, array $data): bool
    {
        $data['updated_at'] = date('Y-m-d H:i:s');
        return $this->db->table('departments')->where('id', $id)->update($data);
    }

    public function delete(int $id): bool
    {
        // Cek apakah departemen masih dipakai
        $assetCount = $this->db->table('assets')
            ->where('department_id', $id)->where('deleted_at', null)->countAllResults();
        if ($assetCount > 0) { return false; }

        return $this->db->table('departments')->where('id', $id)
            ->update(['deleted_at' => date('Y-m-d H:i:s'), 'is_active' => 0]);
    }
}
