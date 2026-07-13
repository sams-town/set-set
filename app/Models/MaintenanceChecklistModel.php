<?php

namespace App\Models;

use CodeIgniter\Model;

class MaintenanceChecklistModel extends Model
{
    protected $table          = 'maintenance_checklist_instances';
    protected $primaryKey     = 'id';
    protected $useAutoIncrement = true;
    protected $useSoftDeletes = true;
    protected $useTimestamps  = true;
    protected $allowedFields  = [
        'asset_id', 'checklist_template_id', 'work_order_id',
        'technician_id', 'checklist_date', 'notes',
        'technician_signature', 'supervisor_signature', 'user_signature'
    ];

    // Get checklists for an asset
    public function getChecklistsForAsset(int $assetId): array
    {
        return $this->db->table('maintenance_checklist_instances ci')
            ->select('ci.*, u.name as technician_name')
            ->join('users u', 'u.id = ci.technician_id', 'left')
            ->where('ci.asset_id', $assetId)
            ->where('ci.deleted_at', null)
            ->orderBy('ci.checklist_date', 'DESC')
            ->get()->getResultArray();
    }

    // Get checklist instance with answers
    public function getChecklistInstance(int $checklistId): ?array
    {
        $instance = $this->db->table('maintenance_checklist_instances ci')
            ->select('ci.*, a.name as asset_name, a.asset_code, u.name as technician_name')
            ->join('assets a', 'a.id = ci.asset_id', 'left')
            ->join('users u', 'u.id = ci.technician_id', 'left')
            ->where('ci.id', $checklistId)
            ->where('ci.deleted_at', null)
            ->get()->getRowArray();

        if (!$instance) {
            return null;
        }

        // Get answers
        $instance['answers'] = $this->db->table('maintenance_checklist_answers a')
            ->where('a.checklist_instance_id', $checklistId)
            ->orderBy('a.id', 'ASC')
            ->get()->getResultArray();

        return $instance;
    }

    // Get or create checklist template for asset category
    public function getTemplateForCategory(string $category): ?array
    {
        $template = $this->db->table('maintenance_checklist_templates t')
            ->where('t.asset_category', $category)
            ->where('t.deleted_at', null)
            ->get()->getRowArray();

        if (!$template) {
            return null;
        }

        $template['items'] = $this->db->table('maintenance_checklist_items i')
            ->where('i.checklist_template_id', $template['id'])
            ->orderBy('i.sort_order', 'ASC')
            ->get()->getResultArray();

        return $template;
    }

    // Create a new checklist instance from template
    public function createChecklistFromTemplate(int $assetId, int $templateId, int $technicianId = null): int
    {
        // Get template and items
        $template = $this->db->table('maintenance_checklist_templates t')
            ->where('t.id', $templateId)
            ->where('t.deleted_at', null)
            ->get()->getRowArray();

        $items = $this->db->table('maintenance_checklist_items i')
            ->where('i.checklist_template_id', $templateId)
            ->orderBy('i.sort_order', 'ASC')
            ->get()->getResultArray();

        // Create checklist instance
        $checklistData = [
            'asset_id'               => $assetId,
            'checklist_template_id'  => $templateId,
            'technician_id'          => $technicianId,
            'checklist_date'         => date('Y-m-d'),
        ];

        $this->db->table('maintenance_checklist_instances')->insert($checklistData);
        $checklistId = $this->db->insertID();

        // Create answer records for each item
        foreach ($items as $item) {
            $this->db->table('maintenance_checklist_answers')->insert([
                'checklist_instance_id' => $checklistId,
                'checklist_item_id'     => $item['id'],
                'item_text'             => $item['item_text'],
            ]);
        }

        return $checklistId;
    }

    // Save checklist answers
    public function saveChecklistAnswers(int $checklistId, array $answers): void
    {
        foreach ($answers as $itemId => $data) {
            $this->db->table('maintenance_checklist_answers')
                ->where('id', $itemId)
                ->where('checklist_instance_id', $checklistId)
                ->update([
                    'status' => $data['status'] ?? 'n/a',
                    'notes'  => $data['notes'] ?? null,
                ]);
        }
    }

    // Create a default template if needed
    public function createDefaultTemplate(string $category): int
    {
        $this->db->table('maintenance_checklist_templates')->insert([
            'name'           => "Checklist {$category}",
            'asset_category' => $category,
        ]);
        $templateId = $this->db->insertID();

        // Add default items
        $defaultItems = [
            'Body / Chasing',
            'Power Supply',
            'Pole Clamp',
            'Batere Pack',
            'Display',
            'Control Key Pad',
            'Self Test',
            'Program Menu',
            'System Setup',
            'Compatibility Infusion Set',
            'Pluger',
            'Door Oper & Close function',
            'Sensor - sensor safety',
            'Lain - lain',
        ];

        foreach ($defaultItems as $i => $item) {
            $this->db->table('maintenance_checklist_items')->insert([
                'checklist_template_id' => $templateId,
                'item_text'             => $item,
                'sort_order'            => $i + 1,
            ]);
        }

        return $templateId;
    }
}
