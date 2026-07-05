<?php

namespace App\Models;

use CodeIgniter\Model;

class AssetLogModel extends Model
{
    protected $table            = 'asset_logs';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;

    protected $allowedFields = [
        'asset_id',
        'user_id',
        'action',
        'description',
        'old_value',
        'new_value',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = false; // tidak perlu updated_at

    protected $skipValidation = false;

    // ---------------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------------

    /**
     * Catat aktivitas aset
     */
    public function record(int $assetId, string $action, string $description = '', array $old = [], array $new = []): bool
    {
        return $this->insert([
            'asset_id'    => $assetId,
            'user_id'     => session()->get('user_id'),
            'action'      => $action,
            'description' => $description,
            'old_value'   => !empty($old) ? json_encode($old) : null,
            'new_value'   => !empty($new) ? json_encode($new) : null,
        ]) !== false;
    }

    /**
     * Riwayat log untuk satu aset beserta nama user
     */
    public function getByAsset(int $assetId): array
    {
        return $this->db->table('asset_logs al')
            ->select('al.*, u.name AS user_name')
            ->join('users u', 'u.id = al.user_id', 'left')
            ->where('al.asset_id', $assetId)
            ->orderBy('al.created_at', 'DESC')
            ->get()
            ->getResultArray();
    }

    /**
     * Semua log (untuk laporan aktivitas)
     */
    public function getAllWithRelations(int $limit = 100): array
    {
        return $this->db->table('asset_logs al')
            ->select('al.*, a.name AS asset_name, a.asset_code, u.name AS user_name')
            ->join('assets a', 'a.id = al.asset_id', 'left')
            ->join('users u', 'u.id = al.user_id', 'left')
            ->orderBy('al.created_at', 'DESC')
            ->limit($limit)
            ->get()
            ->getResultArray();
    }
}
