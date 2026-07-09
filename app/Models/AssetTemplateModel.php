<?php

namespace App\Models;

use CodeIgniter\Model;

class AssetTemplateModel extends Model
{
    protected $table            = 'asset_templates';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;

    protected $allowedFields = [
        'name',
        'category',
        'brand',
        'model',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    protected $validationRules = [
        'name'     => 'required|min_length[2]|max_length[150]|is_unique[asset_templates.name,id,{id}]',
        'category' => 'required|in_list[Building Assets,Utility Assets,Clinical Assets,Operational Assets,ICT Assets,Safety & Security Assets,Transportation Assets,Environmental Assets]',
    ];

    protected $validationMessages = [
        'name' => [
            'required'  => 'Nama template wajib diisi.',
            'is_unique' => 'Nama template sudah terdaftar.',
        ],
        'category' => [
            'required' => 'Kategori wajib dipilih.',
            'in_list'  => 'Kategori tidak valid.',
        ],
    ];

    protected $skipValidation = false;
}
