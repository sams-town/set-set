<?php

namespace App\Models;

use CodeIgniter\Model;

class CategoryModel extends Model
{
    protected $table            = 'categories';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;

    protected $allowedFields = [
        'name',
        'code',
        'description',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    protected $validationRules = [
        'name' => 'required|min_length[2]|max_length[100]',
        'code' => 'required|max_length[20]|is_unique[categories.code,id,{id}]',
    ];

    protected $validationMessages = [
        'name' => ['required' => 'Nama kategori wajib diisi.'],
        'code' => [
            'required'  => 'Kode kategori wajib diisi.',
            'is_unique' => 'Kode kategori sudah digunakan.',
        ],
    ];

    protected $skipValidation = false;

    // ---------------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------------

    /** Ambil semua kategori sebagai pasangan id => name untuk dropdown */
    public function getForDropdown(): array
    {
        $rows   = $this->findAll();
        $result = [];
        foreach ($rows as $row) {
            $result[$row['id']] = $row['name'] . ' (' . $row['code'] . ')';
        }

        return $result;
    }

    /** Hitung jumlah aset per kategori */
    public function withAssetCount(): array
    {
        return $this->db->table('categories c')
            ->select('c.*, COUNT(a.id) AS asset_count')
            ->join('assets a', 'a.category_id = c.id AND a.deleted_at IS NULL', 'left')
            ->where('c.deleted_at', null)
            ->groupBy('c.id')
            ->orderBy('c.name', 'ASC')
            ->get()
            ->getResultArray();
    }
}
