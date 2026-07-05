<?php

namespace App\Models;

use CodeIgniter\Database\BaseConnection;

class RoomTypeModel
{
    protected BaseConnection $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    public function getAll(): array
    {
        return $this->db->table('room_types rt')
            ->select('rt.*, COUNT(l.id) AS location_count')
            ->join('locations l', 'l.room_type_id = rt.id AND l.deleted_at IS NULL', 'left')
            ->where('rt.deleted_at', null)
            ->groupBy('rt.id')
            ->orderBy('rt.name')
            ->get()->getResultArray();
    }

    public function getById(int $id): ?array
    {
        $row = $this->db->table('room_types')
            ->where('id', $id)->where('deleted_at', null)
            ->get()->getRowArray();
        return $row ?: null;
    }

    public function getDropdown(): array
    {
        $rows = $this->db->table('room_types')
            ->select('id, name')
            ->where('deleted_at', null)->where('is_active', 1)
            ->orderBy('name')->get()->getResultArray();
        $out = [];
        foreach ($rows as $r) { $out[$r['id']] = $r['name']; }
        return $out;
    }

    public function insert(array $data): int|false
    {
        $data['created_at'] = $data['updated_at'] = date('Y-m-d H:i:s');
        $this->db->table('room_types')->insert($data);
        $id = $this->db->insertID();
        return $id > 0 ? $id : false;
    }

    public function update(int $id, array $data): bool
    {
        $data['updated_at'] = date('Y-m-d H:i:s');
        return $this->db->table('room_types')->where('id', $id)->update($data);
    }

    public function delete(int $id): bool
    {
        $used = $this->db->table('locations')
            ->where('room_type_id', $id)->where('deleted_at', null)->countAllResults();
        if ($used > 0) { return false; }
        return $this->db->table('room_types')->where('id', $id)
            ->update(['deleted_at' => date('Y-m-d H:i:s'), 'is_active' => 0]);
    }
}
