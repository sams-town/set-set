<?php

namespace App\Models;

use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\Database\BaseConnection;

/**
 * InventoryAssetModel
 *
 * Modul 2 — Inventory Asset (Single Source of Truth)
 * Field lengkap sesuai 20 field konsep GA Dashboard.
 */
class InventoryAssetModel
{
    protected BaseConnection $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    // ---------------------------------------------------------------
    // READ
    // ---------------------------------------------------------------

    public function countFiltered(array $filters = []): int
    {
        $builder = $this->db->table('assets a')->where('a.deleted_at', null);
        $this->applyFilters($builder, $filters);
        return (int) $builder->countAllResults();
    }

    public function getList(array $filters = [], int $limit = 15, int $offset = 0): array
    {
        $builder = $this->db->table('assets a')
            ->select(
                'a.id, a.asset_code, a.name, a.type, a.category, a.brand, a.model,
                 a.serial_number, a.purchase_date, a.purchase_price,
                 a.warranty_expiry, a.condition, a.status, a.status_condition,
                 a.quantity, a.unit, a.depreciation_years, a.depreciation_value,
                 a.pm_interval_days, a.photo, a.created_at,
                 d.name  AS department_name,
                 l.name  AS location_name, l.building, l.floor,
                 v.name  AS vendor_name,
                 u.name  AS created_by_name'
            )
            ->join('departments d', 'd.id = a.department_id AND d.deleted_at IS NULL', 'left')
            ->join('locations l',   'l.id = a.location_id   AND l.deleted_at IS NULL', 'left')
            ->join('vendors v',     'v.id = a.vendor_id     AND v.deleted_at IS NULL', 'left')
            ->join('users u',       'u.id = a.created_by    AND u.deleted_at IS NULL', 'left')
            ->where('a.deleted_at', null);

        $this->applyFilters($builder, $filters);

        return $builder
            ->orderBy('a.created_at', 'DESC')
            ->limit($limit, $offset)
            ->get()
            ->getResultArray();
    }

    public function getById(int $id): array|null
    {
        $row = $this->db->table('assets a')
            ->select('a.*,
                 d.name  AS department_name,
                 l.name  AS location_name, l.building, l.floor,
                 v.name  AS vendor_name, v.phone AS vendor_phone, v.email AS vendor_email,
                 u.name  AS created_by_name')
            ->join('departments d', 'd.id = a.department_id', 'left')
            ->join('locations l',   'l.id = a.location_id',   'left')
            ->join('vendors v',     'v.id = a.vendor_id',     'left')
            ->join('users u',       'u.id = a.created_by',    'left')
            ->where('a.id', $id)
            ->where('a.deleted_at', null)
            ->get()
            ->getRowArray();

        return $row ?: null;
    }

    // ---------------------------------------------------------------
    // CREATE
    // ---------------------------------------------------------------

    public function insert(array $data): int|false
    {
        // Hitung depresiasi otomatis jika ada harga dan masa pakai
        $data = $this->calcDepreciation($data);

        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');

        $this->db->table('assets')->insert($data);
        $insertId = $this->db->insertID();

        return $insertId > 0 ? $insertId : false;
    }

    // ---------------------------------------------------------------
    // UPDATE
    // ---------------------------------------------------------------

    public function update(int $id, array $data): bool
    {
        $data = $this->calcDepreciation($data);
        $data['updated_at'] = date('Y-m-d H:i:s');

        return $this->db->table('assets')
            ->where('id', $id)
            ->update($data);
    }

    // ---------------------------------------------------------------
    // SOFT DELETE
    // ---------------------------------------------------------------

    public function delete(int $id): bool
    {
        return $this->db->table('assets')
            ->where('id', $id)
            ->update([
                'deleted_at' => date('Y-m-d H:i:s'),
                'status'     => 'dihapus',
            ]);
    }

    // ---------------------------------------------------------------
    // HELPERS — Depresiasi & Umur Aset
    // ---------------------------------------------------------------

    /**
     * Hitung depresiasi garis lurus otomatis
     * Nilai depresiasi per tahun = purchase_price / depreciation_years
     */
    private function calcDepreciation(array $data): array
    {
        $price = isset($data['purchase_price']) ? (float) $data['purchase_price'] : 0;
        $years = isset($data['depreciation_years']) ? (int) $data['depreciation_years'] : 0;

        if ($price > 0 && $years > 0) {
            $data['depreciation_value'] = round($price / $years, 2);
        } else {
            $data['depreciation_value'] = null;
        }

        return $data;
    }

    /**
     * Hitung umur aset dalam tahun dan bulan dari purchase_date
     * Return: ['years' => 2, 'months' => 3, 'label' => '2 Tahun 3 Bulan']
     */
    public static function calcAge(?string $purchaseDate): array
    {
        if (! $purchaseDate) {
            return ['years' => 0, 'months' => 0, 'label' => '-'];
        }

        $start = new \DateTime($purchaseDate);
        $now   = new \DateTime();
        $diff  = $start->diff($now);

        $label = '';
        if ($diff->y > 0) {
            $label .= $diff->y . ' Tahun ';
        }
        if ($diff->m > 0) {
            $label .= $diff->m . ' Bulan';
        }
        if ($diff->y === 0 && $diff->m === 0) {
            $label = $diff->d . ' Hari';
        }

        return [
            'years'  => $diff->y,
            'months' => $diff->m,
            'days'   => $diff->d,
            'label'  => trim($label),
        ];
    }

    /**
     * Hitung nilai buku saat ini (Book Value)
     * Book Value = purchase_price - (depreciation_value × umur dalam tahun)
     */
    public static function calcBookValue(
        float $purchasePrice,
        float $depreciationValue,
        ?string $purchaseDate
    ): float {
        if ($purchasePrice <= 0 || $depreciationValue <= 0 || ! $purchaseDate) {
            return $purchasePrice;
        }

        $age = self::calcAge($purchaseDate);
        $yearsElapsed = $age['years'] + ($age['months'] / 12);
        $bookValue    = $purchasePrice - ($depreciationValue * $yearsElapsed);

        return max(0, round($bookValue, 2));
    }

    // ---------------------------------------------------------------
    // CEK UNIK KODE ASET
    // ---------------------------------------------------------------

    public function isCodeUnique(string $code, int $excludeId = 0): bool
    {
        $builder = $this->db->table('assets')
            ->where('asset_code', $code)
            ->where('deleted_at', null);

        if ($excludeId > 0) {
            $builder->where('id !=', $excludeId);
        }

        return $builder->countAllResults() === 0;
    }

    // ---------------------------------------------------------------
    // GENERATE KODE OTOMATIS
    // ---------------------------------------------------------------

    public function generateCode(string $categoryPrefix): string
    {
        $prefix = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $categoryPrefix), 0, 4))
                  . '-' . date('Ym') . '-';

        $last = $this->db->table('assets')
            ->select('asset_code')
            ->like('asset_code', $prefix, 'after')
            ->orderBy('asset_code', 'DESC')
            ->limit(1)
            ->get()
            ->getRowArray();

        $seq = 1;
        if ($last) {
            $parts = explode('-', $last['asset_code']);
            $seq   = (int) end($parts) + 1;
        }

        return $prefix . str_pad($seq, 3, '0', STR_PAD_LEFT);
    }

    // ---------------------------------------------------------------
    // STATISTIK DASHBOARD
    // ---------------------------------------------------------------

    public function getStats(?int $deptId = null): array
    {
        $builder = $this->db->table('assets')
            ->select('status, COUNT(*) AS total')
            ->where('deleted_at', null);

        if ($deptId !== null) {
            $builder->where('department_id', $deptId);
        }

        $rows = $builder->groupBy('status')->get()->getResultArray();

        $stats = ['total' => 0, 'tersedia' => 0, 'dipinjam' => 0, 'dalam_perbaikan' => 0, 'dihapus' => 0];

        foreach ($rows as $row) {
            if (array_key_exists($row['status'], $stats)) {
                $stats[$row['status']] = (int) $row['total'];
            }
            $stats['total'] += (int) $row['total'];
        }

        $warBuilder = $this->db->table('assets')
            ->where('deleted_at', null)
            ->where('warranty_expiry >=', date('Y-m-d'))
            ->where('warranty_expiry <=', date('Y-m-d', strtotime('+30 days')));

        if ($deptId !== null) {
            $warBuilder->where('department_id', $deptId);
        }

        $stats['warranty_soon'] = (int) $warBuilder->countAllResults();

        return $stats;
    }

    // ---------------------------------------------------------------
    // DROPDOWN HELPERS
    // ---------------------------------------------------------------

    public function getDepartmentsDropdown(): array
    {
        $rows = $this->db->table('departments')
            ->select('id, name')
            ->where('deleted_at', null)
            ->where('is_active', 1)
            ->orderBy('name', 'ASC')
            ->get()->getResultArray();

        $result = [];
        foreach ($rows as $r) {
            $result[$r['id']] = $r['name'];
        }
        return $result;
    }

    public function getLocationsDropdown(): array
    {
        $rows = $this->db->table('locations l')
            ->select('l.id, l.name, l.building, d.name AS dept_name')
            ->join('departments d', 'd.id = l.department_id', 'left')
            ->where('l.deleted_at', null)
            ->orderBy('l.name', 'ASC')
            ->get()->getResultArray();

        $result = [];
        foreach ($rows as $r) {
            $label = $r['name'];
            if ($r['building']) {
                $label .= ' — ' . $r['building'];
            }
            $result[$r['id']] = $label;
        }
        return $result;
    }

    public function getVendorsDropdown(): array
    {
        $rows = $this->db->table('vendors')
            ->select('id, name')
            ->where('deleted_at', null)
            ->where('is_active', 1)
            ->orderBy('name', 'ASC')
            ->get()->getResultArray();

        $result = [];
        foreach ($rows as $r) {
            $result[$r['id']] = $r['name'];
        }
        return $result;
    }

    // ---------------------------------------------------------------
    // PRIVATE FILTER HELPER
    // ---------------------------------------------------------------

    private function applyFilters(BaseBuilder $builder, array $filters): void
    {
        if (! empty($filters['search'])) {
            $s = $filters['search'];
            $builder->groupStart()
                ->like('a.name', $s)
                ->orLike('a.asset_code', $s)
                ->orLike('a.brand', $s)
                ->orLike('a.serial_number', $s)
                ->orLike('a.type', $s)
                ->groupEnd();
        }

        if (! empty($filters['status'])) {
            $builder->where('a.status', $filters['status']);
        }

        if (! empty($filters['condition'])) {
            $builder->where('a.condition', $filters['condition']);
        }

        if (! empty($filters['category'])) {
            $builder->where('a.category', $filters['category']);
        }

        if (! empty($filters['department_id'])) {
            $builder->where('a.department_id', $filters['department_id']);
        }

        if (! empty($filters['location_id'])) {
            $builder->where('a.location_id', $filters['location_id']);
        }

        if (! empty($filters['status_condition'])) {
            $builder->where('a.status_condition', $filters['status_condition']);
        }

        if (! empty($filters['warranty_expiring'])) {
            $builder->where('a.warranty_expiry >=', date('Y-m-d'))
                    ->where('a.warranty_expiry <=', date('Y-m-d', strtotime('+30 days')));
        }
    }
}
