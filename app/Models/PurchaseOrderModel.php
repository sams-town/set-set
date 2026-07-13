<?php

namespace App\Models;

use CodeIgniter\Database\BaseConnection;

class PurchaseOrderModel
{
    protected BaseConnection $db;

    public const STATUS = [
        'draft'     => ['label' => 'Draft',     'color' => 'bg-gray-100 text-gray-600'],
        'sent'      => ['label' => 'Terkirim',  'color' => 'bg-blue-100 text-blue-700'],
        'confirmed' => ['label' => 'Dikonfirmasi','color'=> 'bg-indigo-100 text-indigo-700'],
        'partial'   => ['label' => 'Sebagian',  'color' => 'bg-yellow-100 text-yellow-700'],
        'completed' => ['label' => 'Selesai',   'color' => 'bg-green-100 text-green-700'],
        'cancelled' => ['label' => 'Dibatalkan','color' => 'bg-red-100 text-red-700'],
    ];

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    public function getList(array $filters = [], int $limit = 20, int $offset = 0): array
    {
        $b = $this->db->table('purchase_orders po')
            ->select('po.*, v.name AS vendor_name, u.name AS created_by_name,
                      pr.request_code, pr.title AS request_title')
            ->join('vendors v',                'v.id  = po.vendor_id',   'left')
            ->join('users u',                  'u.id  = po.created_by',  'left')
            ->join('procurement_requests pr',  'pr.id = po.request_id',  'left');

        if (!empty($filters['status']))    { $b->where('po.status', $filters['status']); }
        if (!empty($filters['vendor_id'])) { $b->where('po.vendor_id', $filters['vendor_id']); }

        return $b->orderBy('po.created_at', 'DESC')->limit($limit, $offset)->get()->getResultArray();
    }

    public function getById(int $id): ?array
    {
        $row = $this->db->table('purchase_orders po')
            ->select('po.*, v.name AS vendor_name, v.phone AS vendor_phone,
                      v.email AS vendor_email, v.address AS vendor_address,
                      u.name AS created_by_name,
                      pr.request_code, pr.title AS request_title')
            ->join('vendors v',               'v.id  = po.vendor_id',  'left')
            ->join('users u',                 'u.id  = po.created_by', 'left')
            ->join('procurement_requests pr', 'pr.id = po.request_id', 'left')
            ->where('po.id', $id)->get()->getRowArray();
        return $row ?: null;
    }

    public function getItems(int $poId): array
    {
        return $this->db->table('po_items')
            ->where('po_id', $poId)->get()->getResultArray();
    }

    public function insert(array $data, array $items): int|false
    {
        $data['created_at'] = $data['updated_at'] = date('Y-m-d H:i:s');

        // Hitung subtotal dari items
        $subtotal = 0;
        foreach ($items as $item) {
            $subtotal += ($item['quantity'] ?? 1) * ($item['unit_price'] ?? 0);
        }
        $data['subtotal'] = $subtotal;
        $data['total']    = $subtotal + ($data['tax'] ?? 0) + ($data['shipping'] ?? 0);

        $this->db->table('purchase_orders')->insert($data);
        $poId = $this->db->insertID();
        if (! $poId) { return false; }

        foreach ($items as $item) {
            $qty   = (int)   ($item['quantity']   ?? 1);
            $price = (float) ($item['unit_price']  ?? 0);
            $this->db->table('po_items')->insert([
                'po_id'       => $poId,
                'description' => $item['description'] ?? '',
                'category'    => $item['category']    ?? null,
                'quantity'    => $qty,
                'unit'        => $item['unit']        ?? 'unit',
                'unit_price'  => $price,
                'total_price' => $qty * $price,
                'notes'       => $item['notes']       ?? null,
            ]);
        }

        return (int) $poId;
    }

    public function update(int $id, array $data): bool
    {
        $data['updated_at'] = date('Y-m-d H:i:s');
        return $this->db->table('purchase_orders')->where('id', $id)->update($data);
    }

    /**
     * Catat penerimaan barang per item
     */
    public function receiveItems(int $poId, array $receivedQtys, string $receivedDate): bool
    {
        $items = $this->getItems($poId);
        $allComplete = true;

        foreach ($items as $item) {
            $itemId  = (int) $item['id'];
            $newQty  = (int) ($receivedQtys[$itemId] ?? 0);
            if ($newQty <= 0) { continue; }

            $totalReceived = (int) $item['qty_received'] + $newQty;
            $this->db->table('po_items')->where('id', $itemId)
                ->update(['qty_received' => $totalReceived]);

            if ($totalReceived < (int) $item['quantity']) { $allComplete = false; }
        }

        // Update status PO
        $newStatus = $allComplete ? 'completed' : 'partial';
        return $this->db->table('purchase_orders')->where('id', $poId)->update([
            'status'        => $newStatus,
            'received_date' => $receivedDate,
            'updated_at'    => date('Y-m-d H:i:s'),
        ]);
    }

    public function generateCode(): string
    {
        $prefix = 'PO-' . date('Ym') . '-';
        $last = $this->db->table('purchase_orders')
            ->select('po_code')->like('po_code', $prefix, 'after')
            ->orderBy('po_code', 'DESC')->limit(1)->get()->getRowArray();
        $seq = 1;
        if ($last) { $parts = explode('-', $last['po_code']); $seq = (int) end($parts) + 1; }
        return $prefix . str_pad($seq, 3, '0', STR_PAD_LEFT);
    }
}
