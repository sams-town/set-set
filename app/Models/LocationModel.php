<?php

namespace App\Models;

use CodeIgniter\Database\BaseConnection;

class LocationModel
{
    protected BaseConnection $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    public function getAll(array $filters = []): array
    {
        $b = $this->db->table('locations l')
            ->select('l.*, d.name AS department_name, rt.name AS room_type_name,
                      COUNT(a.id) AS asset_count')
            ->join('departments d', 'd.id = l.department_id', 'left')
            ->join('room_types rt',  'rt.id = l.room_type_id', 'left')
            ->join('assets a',       'a.location_id = l.id AND a.deleted_at IS NULL', 'left')
            ->where('l.deleted_at', null)
            ->groupBy('l.id');

        if (!empty($filters['search'])) {
            $s = $filters['search'];
            $b->groupStart()->like('l.name', $s)->orLike('l.building', $s)
              ->orLike('d.name', $s)->groupEnd();
        }
        if (!empty($filters['department_id'])) { $b->where('l.department_id', $filters['department_id']); }
        if (!empty($filters['room_type_id']))  { $b->where('l.room_type_id',  $filters['room_type_id']);  }

        return $b->orderBy('l.name')->get()->getResultArray();
    }

    public function countFiltered(array $filters = []): int
    {
        $b = $this->db->table('locations l')
            ->join('departments d', 'd.id = l.department_id', 'left')
            ->where('l.deleted_at', null);
        if (!empty($filters['search'])) {
            $s = $filters['search'];
            $b->groupStart()->like('l.name', $s)->orLike('l.building', $s)->groupEnd();
        }
        if (!empty($filters['department_id'])) { $b->where('l.department_id', $filters['department_id']); }
        if (!empty($filters['room_type_id']))  { $b->where('l.room_type_id',  $filters['room_type_id']);  }
        return (int) $b->countAllResults();
    }

    public function getById(int $id): ?array
    {
        $row = $this->db->table('locations l')
            ->select('l.*, d.name AS department_name, rt.name AS room_type_name')
            ->join('departments d', 'd.id = l.department_id', 'left')
            ->join('room_types rt',  'rt.id = l.room_type_id', 'left')
            ->where('l.id', $id)->where('l.deleted_at', null)
            ->get()->getRowArray();
        return $row ?: null;
    }

    public function getForDropdown(): array
    {
        $rows = $this->db->table('locations l')
            ->select('l.id, l.name, l.building, d.name AS dept')
            ->join('departments d', 'd.id = l.department_id', 'left')
            ->where('l.deleted_at', null)
            ->orderBy('l.name')->get()->getResultArray();
        $out = [];
        foreach ($rows as $r) {
            $out[$r['id']] = $r['name'] . ($r['building'] ? ' — ' . $r['building'] : '');
        }
        return $out;
    }

    public function insert(array $data): int|false
    {
        $data['created_at'] = $data['updated_at'] = date('Y-m-d H:i:s');
        $this->db->table('locations')->insert($data);
        $id = $this->db->insertID();
        return $id > 0 ? $id : false;
    }

    public function update(int $id, array $data): bool
    {
        $data['updated_at'] = date('Y-m-d H:i:s');
        return $this->db->table('locations')->where('id', $id)->update($data);
    }

    public function delete(int $id): bool
    {
        $used = $this->db->table('assets')
            ->where('location_id', $id)->where('deleted_at', null)->countAllResults();
        if ($used > 0) { return false; }
        return $this->db->table('locations')->where('id', $id)
            ->update(['deleted_at' => date('Y-m-d H:i:s')]);
    }
}
