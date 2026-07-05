<?php

namespace App\Models;

use CodeIgniter\Model;

class BorrowModel extends Model
{
    protected $table            = 'borrows';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;

    protected $allowedFields = [
        'borrow_code',
        'asset_id',
        'user_id',
        'borrow_date',
        'return_date_plan',
        'return_date_actual',
        'status',
        'purpose',
        'notes',
        'approved_by',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules = [
        'asset_id'    => 'required|integer',
        'user_id'     => 'required|integer',
        'borrow_date' => 'required|valid_date',
    ];

    protected $validationMessages = [
        'asset_id'    => ['required' => 'Aset wajib dipilih.'],
        'borrow_date' => ['required' => 'Tanggal pinjam wajib diisi.'],
    ];

    protected $skipValidation = false;

    // ---------------------------------------------------------------
    // Query Helpers
    // ---------------------------------------------------------------

    /**
     * Daftar peminjaman beserta nama aset dan peminjam
     * Mendukung filter department_id via join ke assets
     */
    public function getWithRelations(array $filters = []): array
    {
        $builder = $this->db->table('borrows b')
            ->select('b.*, a.name AS asset_name, a.asset_code, a.department_id,
                      u.name AS user_name, u.email AS user_email,
                      ap.name AS approver_name,
                      d.name AS department_name')
            ->join('assets a',      'a.id  = b.asset_id',   'left')
            ->join('users u',       'u.id  = b.user_id',    'left')
            ->join('users ap',      'ap.id = b.approved_by','left')
            ->join('departments d', 'd.id  = a.department_id', 'left');

        if (!empty($filters['status'])) {
            $builder->where('b.status', $filters['status']);
        }
        if (!empty($filters['user_id'])) {
            $builder->where('b.user_id', $filters['user_id']);
        }
        // Scope departemen — untuk user/technician hanya lihat aset dept sendiri
        if (!empty($filters['department_id'])) {
            $builder->where('a.department_id', $filters['department_id']);
        }
        if (!empty($filters['search'])) {
            $builder->groupStart()
                ->like('a.name', $filters['search'])
                ->orLike('a.asset_code', $filters['search'])
                ->orLike('b.borrow_code', $filters['search'])
                ->orLike('u.name', $filters['search'])
                ->groupEnd();
        }

        return $builder->orderBy('b.created_at', 'DESC')->get()->getResultArray();
    }

    /**
     * Detail satu peminjaman
     */
    public function getDetailById(int $id): array|null
    {
        $result = $this->db->table('borrows b')
            ->select('b.*, a.name AS asset_name, a.asset_code, a.photo AS asset_photo,
                      u.name AS user_name, u.email AS user_email,
                      ap.name AS approver_name')
            ->join('assets a', 'a.id = b.asset_id', 'left')
            ->join('users u', 'u.id = b.user_id', 'left')
            ->join('users ap', 'ap.id = b.approved_by', 'left')
            ->where('b.id', $id)
            ->get()
            ->getRowArray();

        return $result ?: null;
    }

    /**
     * Generate kode peminjaman: BRW-YYYYMMDD-NNN
     */
    public function generateCode(): string
    {
        $prefix = 'BRW-' . date('Ymd') . '-';
        $last   = $this->db->table('borrows')
            ->like('borrow_code', $prefix, 'after')
            ->orderBy('borrow_code', 'DESC')
            ->limit(1)
            ->get()
            ->getRowArray();

        $number = 1;
        if ($last) {
            $parts  = explode('-', $last['borrow_code']);
            $number = (int) end($parts) + 1;
        }

        return $prefix . str_pad($number, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Peminjaman yang sudah lewat tanggal rencana kembali tapi belum dikembalikan
     */
    public function getOverdue(?int $deptId = null): array
    {
        $builder = $this->db->table('borrows b')
            ->select('b.*, a.name AS asset_name, a.asset_code, u.name AS user_name')
            ->join('assets a', 'a.id = b.asset_id', 'left')
            ->join('users u',  'u.id = b.user_id',  'left')
            ->where('b.status', 'dipinjam')
            ->where('b.return_date_plan <', date('Y-m-d'));

        if ($deptId !== null) {
            $builder->where('a.department_id', $deptId);
        }

        return $builder->orderBy('b.return_date_plan', 'ASC')->get()->getResultArray();
    }
}
