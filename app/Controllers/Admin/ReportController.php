<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\InventoryAssetModel;
use App\Models\BorrowModel;
use App\Models\MaintenanceLogModel;
use App\Models\DepartmentModel;

class ReportController extends BaseController
{
    protected InventoryAssetModel $assetModel;
    protected BorrowModel         $borrowModel;
    protected MaintenanceLogModel $logModel;
    protected DepartmentModel     $deptModel;

    // Kategori aset (sama dengan InventoryAssetController)
    private const CATEGORIES = [
        'Komputer & Laptop', 'Printer & Scanner', 'Jaringan & Telekomunikasi',
        'Perabot Kantor', 'Proyektor & AV', 'Kendaraan Dinas',
        'Elektronik & Listrik', 'Alat Medis & Kesehatan',
        'Alat Ukur & Laboratorium', 'Lainnya',
    ];

    public function __construct()
    {
        $this->assetModel  = new InventoryAssetModel();
        $this->borrowModel = new BorrowModel();
        $this->logModel    = new MaintenanceLogModel();
        $this->deptModel   = new DepartmentModel();
    }

    // GET /admin/reports
    public function index()
    {
        $type = $this->request->getGet('type') ?? 'assets';

        $data = [
            'title'       => 'Laporan',
            'type'        => $type,
            'categories'  => self::CATEGORIES,
            'departments' => $this->deptModel->getDropdown(),
        ];

        switch ($type) {
            case 'borrows':
                $data['records'] = $this->borrowModel->getWithRelations([
                    'status' => $this->request->getGet('status'),
                ]);
                break;

            case 'logs':
                $data['records'] = $this->logModel->getAllWithRelations(500);
                break;

            case 'assets':
            default:
                $data['records'] = $this->assetModel->getList([
                    'status'        => $this->request->getGet('status'),
                    'category'      => $this->request->getGet('category'),
                    'department_id' => $this->request->getGet('department_id'),
                ], 1000, 0);
                break;
        }

        return view('reports/index', $data);
    }

    // GET /admin/reports/print
    public function print()
    {
        $type = $this->request->getGet('type') ?? 'assets';

        $data = [
            'title'      => 'Cetak Laporan',
            'type'       => $type,
            'print_date' => date('d F Y H:i'),
            'categories' => self::CATEGORIES,
            'departments'=> $this->deptModel->getDropdown(),
        ];

        switch ($type) {
            case 'borrows':
                $data['records'] = $this->borrowModel->getWithRelations([
                    'status' => $this->request->getGet('status'),
                ]);
                break;

            case 'logs':
                $data['records'] = $this->logModel->getAllWithRelations(500);
                break;

            case 'assets':
            default:
                $data['records'] = $this->assetModel->getList([
                    'status'        => $this->request->getGet('status'),
                    'category'      => $this->request->getGet('category'),
                    'department_id' => $this->request->getGet('department_id'),
                ], 1000, 0);
                break;
        }

        return view('reports/print', $data);
    }
}
