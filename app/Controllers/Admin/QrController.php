<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\InventoryAssetModel;

/**
 * QrController
 *
 * Menyajikan QR Code aset dalam berbagai format:
 *   GET /admin/qr/{id}          → tampil PNG langsung di browser
 *   GET /admin/qr/{id}/svg      → tampil SVG
 *   GET /admin/qr/{id}/download → download PNG
 *   GET /admin/qr/{id}/label    → halaman label siap cetak (Tailwind)
 */
class QrController extends BaseController
{
    protected InventoryAssetModel $assetModel;

    public function __construct()
    {
        helper('qrcode');
        $this->assetModel = new InventoryAssetModel();
    }

    // ---------------------------------------------------------------
    // Tampilkan QR sebagai PNG langsung (embed di <img src="...">)
    // ---------------------------------------------------------------
    public function png(int $id)
    {
        $asset = $this->assetModel->getById($id);
        if (! $asset) {
            return $this->response->setStatusCode(404)->setBody('Asset not found');
        }

        $content = base_url('qr/' . $asset['asset_code']);
        qr_output_png($content, 7);
        exit; // stop CI4 response pipeline
    }

    // ---------------------------------------------------------------
    // Tampilkan QR sebagai SVG
    // ---------------------------------------------------------------
    public function svg(int $id)
    {
        $asset = $this->assetModel->getById($id);
        if (! $asset) {
            return $this->response->setStatusCode(404)->setBody('Asset not found');
        }

        $filename = qr_generate_svg($id, $asset['asset_code']);
        if (! $filename) {
            return $this->response->setStatusCode(500)->setBody('QR generation failed');
        }

        $path = FCPATH . 'uploads/qrcodes/' . $filename;
        return $this->response
            ->setHeader('Content-Type', 'image/svg+xml')
            ->setHeader('Cache-Control', 'public, max-age=86400')
            ->setBody(file_get_contents($path));
    }

    // ---------------------------------------------------------------
    // Download QR PNG
    // ---------------------------------------------------------------
    public function download(int $id)
    {
        $asset = $this->assetModel->getById($id);
        if (! $asset) {
            return redirect()->back()->with('error', 'Aset tidak ditemukan.');
        }

        $filename = qr_generate_asset($id, $asset['asset_code']);
        if (! $filename) {
            return redirect()->back()->with('error', 'Gagal membuat QR Code.');
        }

        $path = FCPATH . 'uploads/qrcodes/' . $filename;

        return $this->response
            ->setHeader('Content-Type', 'image/png')
            ->setHeader('Content-Disposition', 'attachment; filename="qr_' . $asset['asset_code'] . '.png"')
            ->setHeader('Content-Length', (string) filesize($path))
            ->setBody(file_get_contents($path));
    }

    // ---------------------------------------------------------------
    // Halaman label cetak — satu aset
    // ---------------------------------------------------------------
    public function label(int $id)
    {
        $asset = $this->assetModel->getById($id);
        if (! $asset) {
            return redirect()->to('/admin/inventory')->with('error', 'Aset tidak ditemukan.');
        }

        // Generate base64 agar tidak perlu akses file
        $content  = base_url('qr/' . $asset['asset_code']);
        $qrBase64 = qr_base64($content, 6);

        return view('inventory/qr_label', [
            'title'    => 'Label QR — ' . $asset['asset_code'],
            'asset'    => $asset,
            'qr_b64'   => $qrBase64,
        ]);
    }

    // ---------------------------------------------------------------
    // Halaman label cetak massal — array id dari query string
    // GET /admin/qr/labels?ids=1,2,3
    // ---------------------------------------------------------------
    public function labels()
    {
        $rawIds = $this->request->getGet('ids') ?? '';
        $ids    = array_filter(
            array_map('intval', explode(',', $rawIds)),
            fn($v) => $v > 0
        );

        if (empty($ids)) {
            return redirect()->to('/admin/inventory')->with('error', 'Tidak ada aset yang dipilih.');
        }

        $assets = [];
        foreach (array_slice($ids, 0, 50) as $assetId) { // max 50
            $a = $this->assetModel->getById($assetId);
            if ($a) {
                $a['qr_b64'] = qr_base64(base_url('admin/inventory/' . $assetId), 5);
                $assets[]    = $a;
            }
        }

        return view('inventory/qr_labels', [
            'title'  => 'Cetak Label QR (' . count($assets) . ' aset)',
            'assets' => $assets,
        ]);
    }
}
