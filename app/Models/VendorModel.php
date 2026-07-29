<?php

namespace App\Models;

use CodeIgniter\Database\BaseConnection;

class VendorModel
{
    protected BaseConnection $db;

    public const CATEGORIES = [
        'supplier' => 'Supplier / Penyedia Barang',
        'service'  => 'Service / Jasa Perbaikan',
        'both'     => 'Supplier & Service',
    ];

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    public function getAll(array $filters = []): array
    {
        $b = $this->db->table('vendors v')
            ->select('v.*, (SELECT COUNT(*) FROM assets a WHERE a.vendor_id = v.id AND a.deleted_at IS NULL) AS asset_count')
            ->where('v.deleted_at', null);

        if (!empty($filters['search'])) {
            $s = $filters['search'];
            $b->groupStart()
                ->like('v.name', $s)
                ->orLike('v.code', $s)
                ->orLike('v.contact', $s)
                ->orLike('v.phone', $s)
                ->groupEnd();
        }
        if (!empty($filters['category'])) {
            $b->where('v.category', $filters['category']);
        }
        if (isset($filters['is_active']) && $filters['is_active'] !== '') {
            $b->where('v.is_active', $filters['is_active']);
        }

        return $b->orderBy('v.name')->get()->getResultArray();
    }

    public function getById(int $id): ?array
    {
        $row = $this->db->table('vendors')
            ->where('id', $id)
            ->where('deleted_at', null)
            ->get()->getRowArray();
        return $row ?: null;
    }

    public function getDropdown(): array
    {
        $rows = $this->db->table('vendors')
            ->select('id, name, code')
            ->where('deleted_at', null)
            ->where('is_active', 1)
            ->orderBy('name')
            ->get()->getResultArray();

        $out = [];
        foreach ($rows as $r) {
            $out[$r['id']] = $r['name'] . ($r['code'] ? ' (' . $r['code'] . ')' : '');
        }
        return $out;
    }

    public function insert(array $data): int|false
    {
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');
        $this->db->table('vendors')->insert($data);
        $id = $this->db->insertID();
        return $id > 0 ? (int) $id : false;
    }

    public function update(int $id, array $data): bool
    {
        $data['updated_at'] = date('Y-m-d H:i:s');
        return $this->db->table('vendors')->where('id', $id)->update($data);
    }

    public function delete(int $id): bool
    {
        return $this->db->table('vendors')
            ->where('id', $id)
            ->update(['deleted_at' => date('Y-m-d H:i:s'), 'is_active' => 0]);
    }

    public function isCodeUnique(string $code, ?int $excludeId = null): bool
    {
        $b = $this->db->table('vendors')
            ->where('code', $code)
            ->where('deleted_at', null);
        if ($excludeId) {
            $b->where('id !=', $excludeId);
        }
        return $b->countAllResults() === 0;
    }
}
